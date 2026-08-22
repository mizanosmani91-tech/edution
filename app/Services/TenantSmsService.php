<?php

namespace App\Services;

use App\Models\IntegrationSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * TenantSmsService — প্রতিষ্ঠানের নিজস্ব SMS গেটওয়ে ক্রেডেনশিয়াল
 * (NotificationGatewaySettings পেজে সেভ করা) ব্যবহার করে অভিভাবক/স্টাফের
 * মোবাইলে সরাসরি SMS পাঠায়।
 *
 * এটা SmsOtpService থেকে আলাদা — SmsOtpService প্ল্যাটফর্ম-লেভেল
 * (রেজিস্ট্রেশন OTP, বিলিং এলার্ট ইত্যাদি, EDUTION এর নিজস্ব bulksms
 * অ্যাকাউন্ট দিয়ে পাঠায়)। এটা প্রতি-প্রতিষ্ঠান (tenant) নিজস্ব SMS
 * অ্যাকাউন্ট থেকে অভিভাবকদের কাছে পাঠায় — তাই প্রতিষ্ঠান SMS চালু না
 * করলে বা ক্রেডেনশিয়াল না দিলে কিছুই পাঠানো হয় না (চুপচাপ স্কিপ)।
 *
 * ⚠️ bulksmsbd.net এর পাবলিক API স্ট্রাকচার অনুযায়ী লেখা হয়েছে, কিন্তু
 * বাস্তব API key ছাড়া টেস্ট করা যায়নি। প্রতিষ্ঠান নিজের bulksmsbd.net
 * অ্যাকাউন্টের API key/Sender ID বসানোর পর একটা টেস্ট SMS পাঠিয়ে
 * যাচাই করে নেওয়া জরুরি।
 */
class TenantSmsService
{
    /**
     * @return bool পাঠানো হয়েছে কিনা (চালু না থাকলে/ব্যর্থ হলে false)
     */
    public function send(string $institutionId, string $phone, string $message): bool
    {
        $settings = IntegrationSetting::find($institutionId);

        if (! $settings || ! $settings->sms_enabled || ! $settings->sms_api_key) {
            return false;
        }

        $normalized = $this->normalizePhone($phone);

        try {
            $response = match ($settings->sms_provider) {
                'bulksmsbd' => Http::timeout(10)->get('http://bulksmsbd.net/api/smsapi', [
                    'api_key' => $settings->sms_api_key,
                    'type' => 'text',
                    'number' => $normalized,
                    'senderid' => $settings->sms_sender_id ?: '8809617611031',
                    'message' => $message,
                ]),
                default => null,
            };

            if (! $response) {
                Log::warning("অজানা SMS provider: {$settings->sms_provider} (institution: {$institutionId})");

                return false;
            }

            if (! $response->successful()) {
                Log::warning("প্রতিষ্ঠান SMS পাঠাতে ব্যর্থ (institution: {$institutionId}): ".$response->body());

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning("প্রতিষ্ঠান SMS পাঠাতে এক্সেপশন (institution: {$institutionId}): ".$e->getMessage());

            return false;
        }
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '880')) {
            return $digits;
        }

        if (str_starts_with($digits, '0')) {
            return '880'.substr($digits, 1);
        }

        return '880'.$digits;
    }
}
