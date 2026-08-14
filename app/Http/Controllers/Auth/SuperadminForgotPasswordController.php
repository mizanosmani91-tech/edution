<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SmsOtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * SuperadminForgotPasswordController
 *
 * প্যানেল.edution.xyz-এর জন্য আলাদা forgot-password ফ্লো — superadmin কোনো
 * institution-এর সাথে যুক্ত না, তাই নিজের users.phone কলাম ব্যবহার হয়
 * (User::invitePhone, SuperadminDashboard::inviteSuperadmin দেখুন)। শুধু
 * SMS OTP — ইমেইল এখানে ব্যবহার হয়নি কারণ superadmin সংখ্যায় খুবই কম এবং
 * নিরাপত্তার দিক থেকে দ্বিতীয় ফ্যাক্টর (ফোন) বেশি বিশ্বাসযোগ্য।
 */
class SuperadminForgotPasswordController extends Controller
{
    protected const TTL_MINUTES = 5;
    protected const COOLDOWN_SECONDS = 60;

    public function create()
    {
        return view('auth.superadmin-forgot-password');
    }

    public function sendCode(Request $request, SmsOtpService $sms): JsonResponse
    {
        $validator = Validator::make($request->all(), ['email' => ['required', 'email']]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $user = User::where('role', 'superadmin')
            ->whereNull('institution_id')
            ->where('email', $request->string('email'))
            ->first();

        if (! $user || ! $user->phone) {
            return response()->json(['message' => 'এই ইমেইলে কোনো সুপার এডমিন অ্যাকাউন্ট বা ফোন নম্বর পাওয়া যায়নি।'], 422);
        }

        if (! $this->canResend($user->id)) {
            return response()->json(['message' => 'একটু আগেই কোড পাঠানো হয়েছে, ১ মিনিট পর আবার চেষ্টা করুন।'], 429);
        }

        $code = $this->issueCode($user->id);
        $sms->sendMessage($user->phone, "EDUTION: Password reset code: {$code} (valid {$this->ttl()} min)");

        return response()->json([
            'message' => 'ফোনে কোড পাঠানো হয়েছে।',
            'userId' => $user->id,
            'maskedPhone' => substr($user->phone, 0, 4) . str_repeat('*', max(strlen($user->phone) - 7, 0)) . substr($user->phone, -3),
        ]);
    }

    public function reset(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'userId' => ['required', 'uuid'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $userId = $request->string('userId')->toString();
        $attemptsKey = 'sa_pwreset_attempts:' . $userId;
        $attempts = (int) Cache::get($attemptsKey, 0);

        if ($attempts >= 5) {
            return response()->json(['message' => 'অনেকবার ভুল চেষ্টা হয়েছে, ১০ মিনিট পর আবার শুরু থেকে চেষ্টা করুন।'], 429);
        }

        $stored = Cache::get($this->codeKey($userId));

        if ($stored === null || ! hash_equals((string) $stored, (string) $request->string('code'))) {
            Cache::put($attemptsKey, $attempts + 1, now()->addMinutes(10));

            return response()->json(['message' => 'কোডটি সঠিক না অথবা মেয়াদ শেষ হয়ে গেছে।'], 422);
        }

        Cache::forget($this->codeKey($userId));
        Cache::forget($attemptsKey);

        $user = User::where('role', 'superadmin')->find($userId);

        if (! $user) {
            return response()->json(['message' => 'অ্যাকাউন্ট পাওয়া যায়নি।'], 422);
        }

        $user->update([
            'password' => Hash::make($request->string('password')),
            'must_change_password' => false,
        ]);

        return response()->json(['message' => 'পাসওয়ার্ড পরিবর্তন হয়েছে।']);
    }

    protected function ttl(): int
    {
        return self::TTL_MINUTES;
    }

    protected function issueCode(string $userId): string
    {
        $code = (string) random_int(100000, 999999);
        Cache::put($this->codeKey($userId), $code, now()->addMinutes(self::TTL_MINUTES));
        Cache::put($this->cooldownKey($userId), true, now()->addSeconds(self::COOLDOWN_SECONDS));

        return $code;
    }

    protected function canResend(string $userId): bool
    {
        return ! Cache::has($this->cooldownKey($userId));
    }

    protected function codeKey(string $userId): string
    {
        return 'sa_pwreset_otp:user:' . $userId;
    }

    protected function cooldownKey(string $userId): string
    {
        return 'sa_pwreset_cooldown:user:' . $userId;
    }
}
