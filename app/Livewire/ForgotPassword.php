<?php

namespace App\Livewire;

use App\Models\Institution;
use App\Models\Teacher;
use App\Models\User;
use App\Services\SmsOtpService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

/**
 * ForgotPassword
 *
 * নিজে থেকে পাসওয়ার্ড রিসেট করার সেলফ-সার্ভিস ফ্লো।
 *
 * ⚠️ চ্যানেল অগ্রাধিকার: ইমেইল প্রধান (ফ্রি, প্রতিটা ইউজারের ইমেইল
 * এমনিতেই লগইনের জন্য বাধ্যতামূলক), SMS শুধু ব্যাকআপ হিসেবে — কারণ প্রতিটা
 * SMS-এ খরচ হয় (Onecodesoft), আর সবাই যদি SMS দিয়েই রিসেট করে তাহলে খরচ
 * অনিয়ন্ত্রিতভাবে বাড়বে। তাই কোড আগে ইমেইলে যায়, SMS অপশন তখনই দেখানো হয়
 * যখন role-এর সাথে ফোন নম্বর যুক্ত আছে (admin → institution phone,
 * teacher → নিজের phone) এবং ইউজার নিজে থেকে "SMS-এ পাঠান" বাটনে ক্লিক করে।
 *
 * কোড ইউজার-আইডি দিয়ে cache-এ রাখা হয় (channel নির্বিশেষে একই কোড কাজ করে),
 * যাতে ইমেইলে না পেলে SMS ব্যাকআপেও একই কোড দিয়ে ভেরিফাই করা যায়।
 */
class ForgotPassword extends Component
{
    public string $step = 'email'; // email -> otp -> done
    public string $email = '';
    public string $code = '';
    public string $password = '';
    public string $password_confirmation = '';

    public ?string $resolvedUserId = null;
    public ?string $resolvedEmail = null;
    public ?string $resolvedPhone = null;
    public bool $smsAvailable = false;
    public bool $smsSent = false;
    public string $formError = '';
    public string $infoMessage = '';

    protected const TTL_MINUTES = 5;
    protected const COOLDOWN_SECONDS = 60;

    protected function resolveInstitution(): ?Institution
    {
        return Institution::resolveFromSubdomain(request()->getHost());
    }

    public function sendCode(): void
    {
        $this->formError = '';
        $this->validate(['email' => ['required', 'email']]);

        $institution = $this->resolveInstitution();

        if (! $institution) {
            $this->formError = 'নিজের প্রতিষ্ঠানের subdomain (যেমন yourschool.edution.xyz) থেকে এই পেজে আসুন।';
            return;
        }

        $user = User::where('institution_id', $institution->id)
            ->where('email', $this->email)
            ->first();

        if (! $user) {
            $this->formError = 'এই ইমেইলে কোনো অ্যাকাউন্ট পাওয়া যায়নি।';
            return;
        }

        if (! $this->canResend($user->id)) {
            $this->formError = 'একটু আগেই কোড পাঠানো হয়েছে, ১ মিনিট পর আবার চেষ্টা করুন।';
            return;
        }

        $phone = match ($user->role) {
            'admin' => $institution->phone,
            'teacher' => Teacher::find($user->teacher_id)?->phone,
            default => null,
        };

        $code = $this->issueCode($user->id);
        $this->sendEmailCode($user->email, $code);

        $this->resolvedUserId = $user->id;
        $this->resolvedEmail = $this->maskEmail($user->email);
        $this->resolvedPhone = $phone;
        $this->smsAvailable = (bool) $phone;
        $this->smsSent = false;
        $this->step = 'otp';
    }

    public function sendSmsBackup(SmsOtpService $sms): void
    {
        $this->formError = '';

        if (! $this->resolvedUserId) {
            $this->step = 'email';
            return;
        }

        if (! $this->resolvedPhone) {
            $this->formError = 'এই অ্যাকাউন্টের সাথে কোনো ফোন নম্বর যুক্ত নেই।';
            return;
        }

        if (! $this->canResend($this->resolvedUserId)) {
            $this->formError = 'একটু আগেই কোড পাঠানো হয়েছে, ১ মিনিট পর আবার চেষ্টা করুন।';
            return;
        }

        // ⚠️ নতুন কোড না বানিয়ে আগেরটাই আবার পাঠানো হচ্ছে (একই cache key) —
        // যাতে ইমেইলে আসা কোড আর SMS-এর কোড আলাদা হয়ে ইউজার বিভ্রান্ত না হয়।
        $code = Cache::get($this->codeKey($this->resolvedUserId));

        if (! $code) {
            $code = $this->issueCode($this->resolvedUserId);
        } else {
            Cache::put($this->cooldownKey($this->resolvedUserId), true, now()->addSeconds(self::COOLDOWN_SECONDS));
        }

        $sms->sendMessage($this->resolvedPhone, "EDUTION পাসওয়ার্ড রিসেট কোড: {$code}, মেয়াদ " . self::TTL_MINUTES . " মিনিট। শেয়ার করবেন না।");

        $this->smsSent = true;
        $this->infoMessage = 'ফোনে SMS পাঠানো হয়েছে।';
    }

    public function resetPassword(): void
    {
        $this->formError = '';
        $this->validate([
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! $this->resolvedUserId) {
            $this->formError = 'সেশনের মেয়াদ শেষ হয়ে গেছে, আবার শুরু থেকে চেষ্টা করুন।';
            $this->step = 'email';
            return;
        }

        $attemptsKey = 'pwreset_attempts:' . $this->resolvedUserId;
        $attempts = (int) Cache::get($attemptsKey, 0);

        if ($attempts >= 5) {
            $this->formError = 'অনেকবার ভুল চেষ্টা হয়েছে, ১০ মিনিট পর আবার শুরু থেকে চেষ্টা করুন।';
            $this->step = 'email';
            return;
        }

        $stored = Cache::get($this->codeKey($this->resolvedUserId));

        if ($stored === null || ! hash_equals((string) $stored, $this->code)) {
            Cache::put($attemptsKey, $attempts + 1, now()->addMinutes(10));
            $this->formError = 'কোডটি সঠিক না অথবা মেয়াদ শেষ হয়ে গেছে।';
            return;
        }

        Cache::forget($this->codeKey($this->resolvedUserId));
        Cache::forget($attemptsKey);

        $user = User::find($this->resolvedUserId);

        if (! $user) {
            $this->formError = 'অ্যাকাউন্ট পাওয়া যায়নি।';
            $this->step = 'email';
            return;
        }

        $user->update([
            'password' => Hash::make($this->password),
            'must_change_password' => false,
        ]);

        $this->step = 'done';
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
            // ⚠️ ইমেইল গেটওয়ে না থাকলেও/ব্যর্থ হলেও ফ্লো ভেঙে না পড়ে —
            // ইউজার তখন "SMS-এ পাঠান" ব্যাকআপ বাটন ব্যবহার করতে পারবে।
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

    public function render()
    {
        return view('livewire.forgot-password', [
            'institution' => $this->resolveInstitution(),
        ])->layout('components.layouts.blank');
    }
}
