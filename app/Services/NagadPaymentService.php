<?php

namespace App\Services;

use App\Models\IntegrationSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Nagad Payment Gateway (Checkout API) ইন্টিগ্রেশন।
 *
 * ⚠️ bKash এর মতো token/username-password ভিত্তিক না — Nagad এর API RSA
 * কী-পেয়ার ভিত্তিক নিরাপত্তা ব্যবহার করে:
 *   - প্রতিটা রিকোয়েস্টের sensitiveData Nagad এর "PG Public Key" দিয়ে এনক্রিপ্ট করা হয়
 *   - প্রতিটা রিকোয়েস্টে "Merchant Private Key" দিয়ে একটা signature বসানো হয়
 *   - Nagad থেকে ফেরত আসা sensitiveData আমাদের Merchant Private Key দিয়ে ডিক্রিপ্ট করা হয়
 * এই দুইটা কী প্রতিষ্ঠান নিজে Nagad Merchant Portal থেকে "Key Generate" করে
 * পেমেন্ট-গেটওয়ে সেটিংসে বসাবে।
 *
 * ফ্লো: initiate() দুই ধাপে কাজ করে (Nagad API এর নিয়ম অনুযায়ী) —
 *   ধাপ ১ (check-out/initialize) → paymentReferenceId + challenge পাওয়া যায়
 *   ধাপ ২ (check-out/complete)   → চূড়ান্ত checkout URL পাওয়া যায়, guardian কে সেখানে পাঠানো হয়
 * এরপর Nagad callback এ ফেরত পাঠায়, verify() দিয়ে সেটা যাচাই করে নিশ্চিত হওয়া হয়।
 *
 * ⚠️ এটা Nagad এর পাবলিক ডকুমেন্টেড API স্ট্রাকচার (ও ওপেন-সোর্স রেফারেন্স
 * ইমপ্লিমেন্টেশন) অনুযায়ী লেখা, কিন্তু বাস্তব sandbox/production merchant ID ও
 * কী-পেয়ার ছাড়া কল করে টেস্ট করা সম্ভব হয়নি। প্রথম টেস্ট পেমেন্ট অবশ্যই sandbox
 * মোডে চালিয়ে যাচাই করে নেওয়া জরুরি।
 */
class NagadPaymentService
{
    private const SANDBOX_BASE = 'http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0/api/dfs/';

    private const LIVE_BASE = 'https://api.mynagad.com/api/dfs/';

    private IntegrationSetting $settings;

    public function __construct(string $institutionId)
    {
        $settings = IntegrationSetting::find($institutionId);

        if (! $settings || ! $settings->nagad_enabled) {
            throw new RuntimeException('এই প্রতিষ্ঠানের জন্য Nagad পেমেন্ট চালু নেই।');
        }

        if (! $settings->nagad_merchant_id || ! $settings->nagad_merchant_private_key || ! $settings->nagad_pg_public_key) {
            throw new RuntimeException('Nagad মার্চেন্ট আইডি বা কী-পেয়ার সেটিংসে দেওয়া নেই।');
        }

        $this->settings = $settings;
    }

    private function baseUrl(): string
    {
        return $this->settings->nagad_sandbox ? self::SANDBOX_BASE : self::LIVE_BASE;
    }

    private function encryptWithPgPublicKey(string $plainJson): string
    {
        $publicKey = "-----BEGIN PUBLIC KEY-----\n".trim($this->settings->nagad_pg_public_key)."\n-----END PUBLIC KEY-----";
        $keyResource = openssl_pkey_get_public($publicKey);

        if (! $keyResource || ! openssl_public_encrypt($plainJson, $encrypted, $keyResource)) {
            throw new RuntimeException('Nagad PG Public Key দিয়ে এনক্রিপ্ট করা যায়নি — কী ভুল হতে পারে।');
        }

        return base64_encode($encrypted);
    }

    private function signWithMerchantPrivateKey(string $plainJson): string
    {
        $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n".trim($this->settings->nagad_merchant_private_key)."\n-----END RSA PRIVATE KEY-----";
        $keyResource = openssl_pkey_get_private($privateKey);

        if (! $keyResource || ! openssl_sign($plainJson, $signature, $keyResource, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Nagad Merchant Private Key দিয়ে সাইন করা যায়নি — কী ভুল হতে পারে।');
        }

        return base64_encode($signature);
    }

    private function decryptWithMerchantPrivateKey(string $base64Cipher): string
    {
        $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n".trim($this->settings->nagad_merchant_private_key)."\n-----END RSA PRIVATE KEY-----";
        $keyResource = openssl_pkey_get_private($privateKey);

        if (! $keyResource || ! openssl_private_decrypt(base64_decode($base64Cipher), $plain, $keyResource)) {
            throw new RuntimeException('Nagad থেকে আসা ডেটা ডিক্রিপ্ট করা যায়নি।');
        }

        return $plain;
    }

    private function headers(string $clientIp): array
    {
        return [
            'Content-Type' => 'application/json',
            'X-KM-Api-Version' => 'v-0.2.0',
            'X-KM-IP-V4' => $clientIp,
            'X-KM-Client-Type' => 'PC_WEB',
        ];
    }

    /**
     * পেমেন্ট শুরু — দুই ধাপ শেষে Nagad এর checkout URL ফেরত দেয়, guardian কে
     * সেখানে পাঠিয়ে দিতে হবে।
     */
    public function initiate(float $amount, string $feeCollectionId, string $callbackUrl, string $clientIp): array
    {
        $merchantId = $this->settings->nagad_merchant_id;
        $orderId = 'EDU'.strtoupper(Str::random(6)).now()->format('His');

        // ধাপ ১: initialize — চ্যালেঞ্জ ও paymentReferenceId নেওয়া
        $challenge = Str::random(40);
        $initSensitive = [
            'merchantId' => $merchantId,
            'datetime' => now()->format('YmdHis'),
            'orderId' => $orderId,
            'challenge' => $challenge,
        ];

        $initResponse = Http::withHeaders($this->headers($clientIp))
            ->post($this->baseUrl()."check-out/initialize/{$merchantId}/{$orderId}", [
                'dateTime' => now()->format('YmdHis'),
                'sensitiveData' => $this->encryptWithPgPublicKey(json_encode($initSensitive)),
                'signature' => $this->signWithMerchantPrivateKey(json_encode($initSensitive)),
            ]);

        $initData = $initResponse->json();

        if (! is_array($initData) || empty($initData['sensitiveData']) || empty($initData['signature'])) {
            throw new RuntimeException('Nagad initialize ব্যর্থ হয়েছে: '.($initResponse->body() ?: 'অজানা কারণ'));
        }

        $decrypted = json_decode($this->decryptWithMerchantPrivateKey($initData['sensitiveData']), true);

        if (empty($decrypted['paymentReferenceId'])) {
            throw new RuntimeException('Nagad থেকে paymentReferenceId পাওয়া যায়নি।');
        }

        $paymentReferenceId = $decrypted['paymentReferenceId'];
        $returnedChallenge = $decrypted['challenge'] ?? $challenge;

        // ধাপ ২: complete — অ্যামাউন্ট কনফার্ম করে চূড়ান্ত checkout URL নেওয়া
        $orderSensitive = [
            'merchantId' => $merchantId,
            'orderId' => $orderId,
            'currencyCode' => '050', // BDT এর ISO 4217 numeric কোড, Nagad API এই ফরম্যাট আশা করে
            'amount' => number_format($amount, 2, '.', ''),
            'challenge' => $returnedChallenge,
        ];

        $completeResponse = Http::withHeaders($this->headers($clientIp))
            ->post($this->baseUrl()."check-out/complete/{$paymentReferenceId}", [
                'sensitiveData' => $this->encryptWithPgPublicKey(json_encode($orderSensitive)),
                'signature' => $this->signWithMerchantPrivateKey(json_encode($orderSensitive)),
                'merchantCallbackURL' => $callbackUrl,
            ]);

        $completeData = $completeResponse->json();

        if (! is_array($completeData) || ($completeData['status'] ?? null) !== 'Success' || empty($completeData['callBackUrl'])) {
            throw new RuntimeException('Nagad পেমেন্ট শুরু করা যায়নি: '.($completeResponse->body() ?: 'অজানা কারণ'));
        }

        return [
            'checkoutUrl' => $completeData['callBackUrl'],
            'orderId' => $orderId,
            'paymentReferenceId' => $paymentReferenceId,
        ];
    }

    /**
     * Nagad callback থেকে ফেরত আসা payment_ref_id দিয়ে সরাসরি Nagad সার্ভারকে
     * জিজ্ঞেস করে আসল অবস্থা নিশ্চিত করা হয় — শুধু query string বিশ্বাস করা হয় না।
     */
    public function verify(string $paymentReferenceId): array
    {
        $response = Http::get($this->baseUrl().'verify/payment/'.$paymentReferenceId);

        return $response->json() ?? [];
    }
}
