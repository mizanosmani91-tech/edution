<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\Teacher;
use App\Models\User;
use App\Services\SmsOtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

/**
 * ForgotPasswordController
 *
 * ⚠️ ইচ্ছাকৃতভাবে Livewire component না বানিয়ে সাধারণ controller + fetch()
 * ব্যবহার করা হয়েছে — কারণ Livewire-এর সব action `/livewire/update` নামের
 * একটাই shared route দিয়ে যায় (AppServiceProvider দেখুন), যেটাতে
 * বাধ্যতামূলক 'auth' middleware বসানো আছে (ড্যাশবোর্ডের ভেতরের বাকি সব
 * Livewire কম্পোনেন্টের নিরাপত্তার জন্য)। ফলে লগইন-ই না করা কোনো ইউজার
 * (এই forgot-password ফ্লো ঠিক এই অবস্থায়ই থাকে) Livewire কম্পোনেন্ট
 * ব্যবহার করলে প্রতিটা ক্লিকেই "auth" middleware তাকে /login-এ রিডাইরেক্ট
 * করে দিত। এই একই কারণে রেজিস্ট্রেশন OTP-ও (OtpController) Livewire না
 * হয়ে প্লেইন controller — এই ফাইলটা সেই একই প্যাটার্ন অনুসরণ করছে।
 */
class ForgotPasswordController extends Controller
{
    protected const TTL_MINUTES = 5;
    protected const COOLDOWN_SECONDS = 60;

    public function create(Request $request)
    {
        $institution = Institution::resolveFromSubdomain($request->getHost());

        return view('auth.forgot-password', ['institution' => $institution]);
    }

    public function sendCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $institution = Institution::resolveFromSubdomain($request->getHost());

        if (! $institution) {
            return response()->json(['message' => 'নিজের প্রতিষ্ঠানের subdomain থেকে এই পেজে আসুন।'], 422);
        }

        $user = User::where('institution_id', $institution->id)
            ->where('email', $request->string('email'))
            ->first();

        if (! $user) {
            return response()->json(['message' => 'এই ইমেইলে কোনো অ্যাকাউন্ট পাওয়া যায়নি।'], 422);
        }

        if (! $this->canResend($user->id)) {
            return response()->json(['message' => 'একটু আগেই কোড পাঠানো হয়েছে, ১ মিনিট পর আবার চেষ্টা করুন।'], 429);
        }

        $phone = match ($user->role) {
            'admin' => $institution->phone,
            'teacher' => Teacher::find($user->teacher_id)?->phone,
            default => null,
        };

        $code = $this->issueCode($user->id);
        $this->sendEmailCode($user->email, $code);

        return response()->json([
            'message' => 'ইমেইলে কোড পাঠানো হয়েছে।',
            'userId' => $user->id,
            'maskedEmail' => $this->maskEmail($user->email),
            'smsAvailable' => (bool) $phone,
        ]);
    }

    public function sendSmsBackup(Request $request, SmsOtpService $sms): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'userId' => ['required', 'uuid'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'অবৈধ অনুরোধ।'], 422);
        }

        $user = User::find($request->string('userId'));

        if (! $user) {
            return response()->json(['message' => 'অ্যাকাউন্ট পাওয়া যায়নি।'], 422);
        }

        $institution = Institution::find($user->institution_id);

        $phone = match ($user->role) {
            'admin' => $institution?->phone,
            'teacher' => Teacher::find($user->teacher_id)?->phone,
            default => null,
        };

        if (! $phone) {
            return response()->json(['message' => 'এই অ্যাকাউন্টের সাথে কোনো ফোন নম্বর যুক্ত নেই।'], 422);
        }

        if (! $this->canResend($user->id)) {
            return response()->json(['message' => 'একটু আগেই কোড পাঠানো হয়েছে, ১ মিনিট পর আবার চেষ্টা করুন।'], 429);
        }

        // ⚠️ নতুন কোড না বানিয়ে আগেরটাই আবার পাঠানো হচ্ছে (একই cache key) —
        // যাতে ইমেইলে আসা কোড আর SMS-এর কোড আলাদা হয়ে ইউজার বিভ্রান্ত না হয়।
        $code = Cache::get($this->codeKey($user->id));

        if (! $code) {
            $code = $this->issueCode($user->id);
        } else {
            Cache::put($this->cooldownKey($user->id), true, now()->addSeconds(self::COOLDOWN_SECONDS));
        }

        $sms->sendMessage($phone, "EDUTION পাসওয়ার্ড রিসেট কোড: {$code}, মেয়াদ " . self::TTL_MINUTES . " মিনিট। শেয়ার করবেন না।");

        return response()->json(['message' => 'ফোনে SMS পাঠানো হয়েছে।']);
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
        $attemptsKey = 'pwreset_attempts:' . $userId;
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

        $user = User::find($userId);

        if (! $user) {
            return response()->json(['message' => 'অ্যাকাউন্ট পাওয়া যায়নি।'], 422);
        }

        $user->update([
            'password' => Hash::make($request->string('password')),
            'must_change_password' => false,
        ]);

        return response()->json(['message' => 'পাসওয়ার্ড পরিবর্তন হয়েছে।']);
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
        return 'pwreset_otp:user:' . $userId;
    }

    protected function cooldownKey(string $userId): string
    {
        return 'pwreset_cooldown:user:' . $userId;
    }

    protected function sendEmailCode(string $email, string $code): void
    {
        $message = "আপনার EDUTION পাসওয়ার্ড রিসেট কোড: {$code}\n\nএই কোডের মেয়াদ " . self::TTL_MINUTES . " মিনিট। কোডটি কারো সাথে শেয়ার করবেন না।\n\nআপনি যদি এই অনুরোধ না করে থাকেন, এই ইমেইল উপেক্ষা করুন।";

        try {
            Mail::raw($message, function ($mail) use ($email) {
                $mail->to($email)->subject('EDUTION পাসওয়ার্ড রিসেট কোড');
            });
        } catch (\Throwable $e) {
            Log::warning('পাসওয়ার্ড রিসেট ইমেইল পাঠাতে ব্যর্থ: ' . $e->getMessage());
        }
    }

    protected function maskEmail(string $email): string
    {
        [$name, $domain] = explode('@', $email) + [null, null];

        if (! $name || ! $domain) {
            return $email;
        }

        $visible = min(2, strlen($name));

        return substr($name, 0, $visible) . str_repeat('*', max(strlen($name) - $visible, 1)) . '@' . $domain;
    }
}
