<?php

namespace App\Livewire;

use App\Models\Institution;
use App\Models\Teacher;
use App\Models\User;
use App\Services\SmsOtpService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

/**
 * ForgotPassword
 *
 * নিজে থেকে পাসওয়ার্ড রিসেট করার সেলফ-সার্ভিস ফ্লো — ইমেইলের বদলে SMS
 * ব্যবহার হয়েছে কারণ প্ল্যাটফর্মে এখনো নির্ভরযোগ্য ইমেইল গেটওয়ে সেট করা
 * নেই (MAIL_MAILER=log), কিন্তু SMS গেটওয়ে (Onecodesoft) ইতিমধ্যে কাজ করছে
 * (রেজিস্ট্রেশন OTP, সাময়িক পাসওয়ার্ড পাঠানোর জন্য ব্যবহৃত হচ্ছে)।
 *
 * ফোন নম্বর role অনুযায়ী ভিন্ন জায়গা থেকে আসে:
 *   - admin   → প্রতিষ্ঠানের রেজিস্টার করা ফোন নম্বর (Institution::phone)
 *   - teacher → নিজের প্রোফাইলের ফোন নম্বর (Teacher::phone)
 *   - guardian/student → এখনো আলাদা ফোন নম্বর ইউজারের সাথে যুক্ত নেই, তাই
 *     সেলফ-সার্ভিস রিসেট সম্ভব না — এডমিনের সাথে যোগাযোগ করতে বলা হয়।
 */
class ForgotPassword extends Component
{
    public string $step = 'email'; // email -> otp -> done
    public string $email = '';
    public string $code = '';
    public string $password = '';
    public string $password_confirmation = '';

    public ?string $maskedPhone = null;
    public ?string $resolvedUserId = null;
    public ?string $resolvedPhone = null;
    public string $formError = '';

    protected function resolveInstitution(): ?Institution
    {
        return Institution::resolveFromSubdomain(request()->getHost());
    }

    public function sendCode(SmsOtpService $sms): void
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

        $phone = match ($user->role) {
            'admin' => $institution->phone,
            'teacher' => Teacher::find($user->teacher_id)?->phone,
            default => null,
        };

        if (! $phone) {
            $this->formError = 'এই অ্যাকাউন্টের সাথে কোনো ফোন নম্বর যুক্ত নেই — দয়া করে প্রতিষ্ঠানের এডমিনের সাথে যোগাযোগ করে পাসওয়ার্ড রিসেট করিয়ে নিন।';
            return;
        }

        if (! $sms->canResendReset($phone)) {
            $this->formError = 'একটু আগেই কোড পাঠানো হয়েছে, ১ মিনিট পর আবার চেষ্টা করুন।';
            return;
        }

        $sms->sendResetCode($phone);

        $this->resolvedUserId = $user->id;
        $this->resolvedPhone = $phone;
        $this->maskedPhone = $this->mask($phone);
        $this->step = 'otp';
    }

    public function resend(SmsOtpService $sms): void
    {
        $this->formError = '';

        if (! $this->resolvedPhone) {
            $this->step = 'email';
            return;
        }

        if (! $sms->canResendReset($this->resolvedPhone)) {
            $this->formError = 'একটু আগেই কোড পাঠানো হয়েছে, ১ মিনিট পর আবার চেষ্টা করুন।';
            return;
        }

        $sms->sendResetCode($this->resolvedPhone);
    }

    public function resetPassword(SmsOtpService $sms): void
    {
        $this->formError = '';
        $this->validate([
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! $this->resolvedPhone || ! $this->resolvedUserId) {
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

        if (! $sms->verifyResetCode($this->resolvedPhone, $this->code)) {
            Cache::put($attemptsKey, $attempts + 1, now()->addMinutes(10));
            $this->formError = 'কোডটি সঠিক না অথবা মেয়াদ শেষ হয়ে গেছে।';
            return;
        }

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

    protected function mask(string $phone): string
    {
        return substr($phone, 0, 5) . str_repeat('*', max(strlen($phone) - 8, 0)) . substr($phone, -3);
    }

    public function render()
    {
        return view('livewire.forgot-password', [
            'institution' => $this->resolveInstitution(),
        ]);
    }
}
