<?php

namespace App\Livewire;

use App\Models\IntegrationSetting;
use Livewire\Component;

class PaymentGatewaySettings extends Component
{
    public bool $bkashEnabled = false;
    public string $bkashMerchantNumber = '';
    public string $bkashApiKey = '';
    public string $bkashApiSecret = '';
    public string $bkashUsername = '';
    public string $bkashPassword = '';
    public bool $bkashSandbox = true;

    public bool $nagadEnabled = false;
    public string $nagadMerchantNumber = '';
    public string $nagadApiKey = '';
    public string $nagadMerchantId = '';
    public string $nagadMerchantPrivateKey = '';
    public string $nagadPgPublicKey = '';
    public bool $nagadSandbox = true;

    public ?string $savedMessage = null;

    public function mount(): void
    {
        $settings = IntegrationSetting::find(app('tenant.institution_id'));

        $this->bkashEnabled = $settings?->bkash_enabled ?? false;
        $this->bkashMerchantNumber = $settings?->bkash_merchant_number ?? '';
        $this->bkashApiKey = $settings?->bkash_api_key ?? '';
        $this->bkashApiSecret = $settings?->bkash_api_secret ?? '';
        $this->bkashUsername = $settings?->bkash_username ?? '';
        $this->bkashPassword = $settings?->bkash_password ?? '';
        $this->bkashSandbox = $settings?->bkash_sandbox ?? true;

        $this->nagadEnabled = $settings?->nagad_enabled ?? false;
        $this->nagadMerchantNumber = $settings?->nagad_merchant_number ?? '';
        $this->nagadApiKey = $settings?->nagad_api_key ?? '';
        $this->nagadMerchantId = $settings?->nagad_merchant_id ?? '';
        $this->nagadMerchantPrivateKey = $settings?->nagad_merchant_private_key ?? '';
        $this->nagadPgPublicKey = $settings?->nagad_pg_public_key ?? '';
        $this->nagadSandbox = $settings?->nagad_sandbox ?? true;
    }

    public function save(): void
    {
        // ⚠️ bKash Tokenized Checkout ও Nagad Checkout — দুইটা ইন্টিগ্রেশনই কোড-সম্পূর্ণ
        // (OnlinePaymentController, BkashPaymentService, NagadPaymentService) — কিন্তু
        // বাস্তব sandbox/production ক্রেডেনশিয়াল ছাড়া এখনো লাইভ টেস্ট করা হয়নি।
        // প্রথমে Sandbox মোডে টেস্ট পেমেন্ট করে যাচাই করে নেওয়া জরুরি।
        IntegrationSetting::updateOrCreate(
            ['institution_id' => app('tenant.institution_id')],
            [
                'bkash_enabled' => $this->bkashEnabled,
                'bkash_merchant_number' => $this->bkashMerchantNumber ?: null,
                'bkash_api_key' => $this->bkashApiKey ?: null,
                'bkash_api_secret' => $this->bkashApiSecret ?: null,
                'bkash_username' => $this->bkashUsername ?: null,
                'bkash_password' => $this->bkashPassword ?: null,
                'bkash_sandbox' => $this->bkashSandbox,
                'nagad_enabled' => $this->nagadEnabled,
                'nagad_merchant_number' => $this->nagadMerchantNumber ?: null,
                'nagad_api_key' => $this->nagadApiKey ?: null,
                'nagad_merchant_id' => $this->nagadMerchantId ?: null,
                'nagad_merchant_private_key' => $this->nagadMerchantPrivateKey ?: null,
                'nagad_pg_public_key' => $this->nagadPgPublicKey ?: null,
                'nagad_sandbox' => $this->nagadSandbox,
            ]
        );

        $this->savedMessage = 'সেটিংস সংরক্ষণ করা হয়েছে।';
    }

    public function render()
    {
        return view('livewire.payment-gateway-settings')
            ->layout('components.layouts.app', ['title' => 'অনলাইন পেমেন্ট গেটওয়ে']);
    }
}
