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

    public bool $nagadEnabled = false;
    public string $nagadMerchantNumber = '';
    public string $nagadApiKey = '';

    public ?string $savedMessage = null;

    public function mount(): void
    {
        $settings = IntegrationSetting::find(app('tenant.institution_id'));

        $this->bkashEnabled = $settings?->bkash_enabled ?? false;
        $this->bkashMerchantNumber = $settings?->bkash_merchant_number ?? '';
        $this->bkashApiKey = $settings?->bkash_api_key ?? '';
        $this->bkashApiSecret = $settings?->bkash_api_secret ?? '';

        $this->nagadEnabled = $settings?->nagad_enabled ?? false;
        $this->nagadMerchantNumber = $settings?->nagad_merchant_number ?? '';
        $this->nagadApiKey = $settings?->nagad_api_key ?? '';
    }

    public function save(): void
    {
        // ⚠️ এখানে শুধু credential সংরক্ষণ করা হচ্ছে — আসল bKash/Nagad পেমেন্ট
        // API-তে ভেরিফাই করা হয়নি, কারণ সেটার জন্য প্রকৃত sandbox/production
        // অ্যাক্সেস দরকার। *_enabled অন থাকলেও, লাইভ চার্জ ফিচার এখনো চালু হয়নি।
        IntegrationSetting::updateOrCreate(
            ['institution_id' => app('tenant.institution_id')],
            [
                'bkash_enabled' => $this->bkashEnabled,
                'bkash_merchant_number' => $this->bkashMerchantNumber ?: null,
                'bkash_api_key' => $this->bkashApiKey ?: null,
                'bkash_api_secret' => $this->bkashApiSecret ?: null,
                'nagad_enabled' => $this->nagadEnabled,
                'nagad_merchant_number' => $this->nagadMerchantNumber ?: null,
                'nagad_api_key' => $this->nagadApiKey ?: null,
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
