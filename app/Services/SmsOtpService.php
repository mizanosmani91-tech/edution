<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SmsOtpService
 *
 * রেজিস্ট্রেশনের সময় মোবাইল নম্বর যাচাইয়ের জন্য OTP পাঠানো ও ভেরিফাই করার
 * সার্ভিস — Onecodesoft Bulk SMS (bulksms.ocs-api.top) প্ল্যাটফর্মের API key
 * ব্যবহার করে (config/services.php -> 'bulksms', .env-এ BULKSMS_API_KEY)।
 *
 * এটা প্ল্যাটফর্ম-লেভেল OTP (রেজিস্ট্রেশন যাচাই) — প্রতিষ্ঠানের নিজস্ব
 * SMS গেটওয়ে সেটিংস (NotificationGatewaySettings, per-tenant) থেকে আলাদা।
 */
class SmsOtpService
{
    protected const TTL_MINUTES = 5;
    protected const RESEND_COOLDOWN_SECONDS = 60;
    protected const VERIFIED_TTL_MINUTES = 30;

    /**
     * বাংলাদেশি নম্বরকে আন্তর্জাতিক ফরম্যাটে (৮৮০...) স্বাভাবিক করে,
     * যেহেতু SMS API-তে country code সহ নম্বর দিতে হয়।
     */
    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '880')) {
            return $digits;
        }

        if (str_starts_with($digits, '0')) {
            return '880' . substr($digits, 1);
        }

        return '880' . $digits;
    }

    public function canResend(string $phone): bool
    {
        return ! Cache::has('reg_otp_cooldown:' . $this->normalizePhone($phone));
    }

    public function send(string $phone): void
    {
        $normalized = $this->normalizePhone($phone);
        $code = (string) random_int(100000, 999999);

        Cache::put('reg_otp:' . $normalized, $code, now()->addMinutes(self::TTL_MINUTES));
        Cache::put('reg_otp_cooldown:' . $normalized, true, now()->addSeconds(self::RESEND_COOLDOWN_SECONDS));

        // ⚠️ Unicode (বাংলা) SMS-এ ৭০ ক্যারেক্টারের বেশি হলেই ২ পার্টে ভেঙে
        // যায় (ডাবল খরচ) — তাই বার্তাটা ইচ্ছাকৃতভাবে ছোট রাখা হয়েছে,
        // যাতে ১ পার্টেই (৭০ ক্যারেক্টারের নিচে) পাঠানো যায়।
        $message = "EDUTION যাচাই কোড: {$code}, মেয়াদ " . self::TTL_MINUTES . " মিনিট। শেয়ার করবেন না।";

        $this->sendMessage($normalized, $message);
    }

    /**
     * ⚠️ সাধারণ SMS পাঠানোর জেনেরিক মেথড — শুধু OTP না, প্রতিষ্ঠান অনুমোদনের
     * পর সাময়িক পাসওয়ার্ড পাঠাতেও এটা ব্যবহার হয় (SuperadminDashboard দেখুন)।
     * কারণ: superadmin প্যানেলে দেখানো পাসওয়ার্ড রিলোড/ব্রাউজার বন্ধ হলে
     * হারিয়ে যেতে পারে, কিন্তু SMS হিসেবে প্রতিষ্ঠানের ফোনে একবার পৌঁছে
     * গেলে সেটা স্থায়ীভাবে থেকে যায় — তাই approve করার সময়ই স্বয়ংক্রিয়ভাবে SMS যায়।
     */
    public function sendMessage(string $phone, string $message): void
    {
        $normalized = $this->normalizePhone($phone);

        try {
            Http::timeout(10)->get(config('services.bulksms.endpoint'), [
                'api_key' => config('services.bulksms.api_key'),
                'type' => 'text',
                'number' => $normalized,
                'senderid' => config('services.bulksms.sender_id'),
                'message' => $message,
            ]);
        } catch (\Throwable $e) {
            // ⚠️ SMS গেটওয়ে ডাউন থাকলেও মূল কাজ (approve/OTP) যেন ভেঙে না পড়ে —
            // শুধু লগ করা হলো, superadmin স্ক্রিনে দেখানো পাসওয়ার্ড দিয়ে
            // ম্যানুয়ালি জানানো যাবে fallback হিসেবে।
            Log::warning('SMS পাঠাতে ব্যর্থ: ' . $e->getMessage());
        }
    }

    public function verify(string $phone, string $code): bool
    {
        $normalized = $this->normalizePhone($phone);
        $stored = Cache::get('reg_otp:' . $normalized);

        if ($stored !== null && hash_equals((string) $stored, $code)) {
            Cache::forget('reg_otp:' . $normalized);
            Cache::put('reg_otp_verified:' . $normalized, true, now()->addMinutes(self::VERIFIED_TTL_MINUTES));

            return true;
        }

        return false;
    }

    public function isVerified(string $phone): bool
    {
        return (bool) Cache::get('reg_otp_verified:' . $this->normalizePhone($phone));
    }

    public function clearVerified(string $phone): void
    {
        Cache::forget('reg_otp_verified:' . $this->normalizePhone($phone));
    }
}
