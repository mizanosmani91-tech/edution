<?php

namespace App\Services;

use App\Models\IntegrationSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * bKash Tokenized Checkout (Merchant Checkout API v1.2.0-beta) ইন্টিগ্রেশন।
 *
 * ফ্লো: grantToken() → createPayment() (guardian কে bKash এর পেজে পাঠানো হয়) →
 * bKash থেকে callback এ ফেরত এলে executePayment() দিয়ে কনফার্ম করা হয়।
 *
 * ⚠️ এটা bKash এর পাবলিক ডকুমেন্টেড API স্ট্রাকচার অনুযায়ী লেখা, কিন্তু বাস্তব
 * sandbox/production ক্রেডেনশিয়াল ছাড়া কল করে টেস্ট করা সম্ভব হয়নি। প্রতিষ্ঠান
 * পেমেন্ট-গেটওয়ে সেটিংসে আসল app_key/app_secret/username/password বসানোর পর
 * প্রথম একটা টেস্ট পেমেন্ট sandbox মোডে চালিয়ে যাচাই করে নেওয়া জরুরি।
 */
class BkashPaymentService
{
    private const SANDBOX_BASE = 'https://tokenized.sandbox.bka.sh/v1.2.0-beta';

    private const LIVE_BASE = 'https://tokenized.pay.bka.sh/v1.2.0-beta';

    private IntegrationSetting $settings;

    public function __construct(string $institutionId)
    {
        $settings = IntegrationSetting::find($institutionId);

        if (! $settings || ! $settings->bkash_enabled) {
            throw new RuntimeException('এই প্রতিষ্ঠানের জন্য bKash পেমেন্ট চালু নেই।');
        }

        $this->settings = $settings;
    }

    private function baseUrl(): string
    {
        return $this->settings->bkash_sandbox ? self::SANDBOX_BASE : self::LIVE_BASE;
    }

    /**
     * bKash token 1 ঘণ্টা মেয়াদি — বারবার grant না করে ক্যাশ থেকে রিফ্রেশ ব্যবহার করা হয়
     */
    private function grantToken(): string
    {
        $cacheKey = 'bkash_token_'.$this->settings->institution_id;

        return Cache::remember($cacheKey, now()->addMinutes(50), function () {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'username' => $this->settings->bkash_username,
                'password' => $this->settings->bkash_password,
            ])->post($this->baseUrl().'/tokenized/checkout/token/grant', [
                'app_key' => $this->settings->bkash_api_key,
                'app_secret' => $this->settings->bkash_api_secret,
            ]);

            if (! $response->successful() || ! $response->json('id_token')) {
                throw new RuntimeException('bKash token নেওয়া যায়নি: '.$response->body());
            }

            return $response->json('id_token');
        });
    }

    private function headers(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => $this->grantToken(),
            'X-App-Key' => $this->settings->bkash_api_key,
        ];
    }

    /**
     * পেমেন্ট শুরু — সফল হলে bKash এর checkout URL ফেরত দেয়, guardian কে সেখানে
     * পাঠিয়ে দিতে হবে
     */
    public function createPayment(float $amount, string $feeCollectionId, string $callbackUrl): array
    {
        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl().'/tokenized/checkout/create', [
                'mode' => '0011', // tokenized checkout
                'payerReference' => $feeCollectionId,
                'callbackURL' => $callbackUrl,
                'amount' => number_format($amount, 2, '.', ''),
                'currency' => 'BDT',
                'intent' => 'sale',
                'merchantInvoiceNumber' => 'EDU-'.strtoupper(substr($feeCollectionId, 0, 8)).'-'.now()->format('YmdHis'),
            ]);

        if (! $response->successful() || $response->json('statusCode') !== '0000') {
            throw new RuntimeException('bKash পেমেন্ট শুরু করা যায়নি: '.$response->body());
        }

        return $response->json();
    }

    /**
     * bKash callback থেকে ফেরত আসা paymentID দিয়ে পেমেন্ট চূড়ান্তভাবে কনফার্ম করা হয়
     */
    public function executePayment(string $paymentId): array
    {
        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl().'/tokenized/checkout/execute', [
                'paymentID' => $paymentId,
            ]);

        return $response->json() ?? [];
    }

    public function queryPayment(string $paymentId): array
    {
        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl().'/tokenized/checkout/payment/status', [
                'paymentID' => $paymentId,
            ]);

        return $response->json() ?? [];
    }
}
