<?php

namespace App\Livewire;

use App\Models\IntegrationSetting;
use Livewire\Component;

class NotificationGatewaySettings extends Component
{
    public bool $smsEnabled = false;
    public string $smsProvider = 'bulksmsbd';
    public string $smsApiKey = '';
    public string $smsSenderId = '';

    public bool $emailEnabled = false;
    public string $emailSmtpHost = '';
    public string $emailSmtpPort = '587';
    public string $emailSmtpUsername = '';
    public string $emailSmtpPassword = '';
    public string $emailSmtpEncryption = 'tls';
    public string $emailFromAddress = '';
    public string $emailFromName = '';

    public ?string $savedMessage = null;

    public function mount(): void
    {
        $settings = IntegrationSetting::find(app('tenant.institution_id'));

        $this->smsEnabled = $settings?->sms_enabled ?? false;
        $this->smsProvider = $settings?->sms_provider ?? 'bulksmsbd';
        $this->smsApiKey = $settings?->sms_api_key ?? '';
        $this->smsSenderId = $settings?->sms_sender_id ?? '';

        $this->emailEnabled = $settings?->email_enabled ?? false;
        $this->emailSmtpHost = $settings?->email_smtp_host ?? '';
        $this->emailSmtpPort = $settings?->email_smtp_port ?? '587';
        $this->emailSmtpUsername = $settings?->email_smtp_username ?? '';
        $this->emailSmtpPassword = $settings?->email_smtp_password ?? '';
        $this->emailSmtpEncryption = $settings?->email_smtp_encryption ?? 'tls';
        $this->emailFromAddress = $settings?->email_from_address ?? '';
        $this->emailFromName = $settings?->email_from_name ?? '';
    }

    public function save(): void
    {
        // ⚠️ শুধু credential সংরক্ষণ — SMS/Email আসলেই পাঠানো এখনো ইমপ্লিমেন্ট
        // করা হয়নি, কারণ real SMS gateway account / SMTP credential দরকার।
        IntegrationSetting::updateOrCreate(
            ['institution_id' => app('tenant.institution_id')],
            [
                'sms_enabled' => $this->smsEnabled,
                'sms_provider' => $this->smsProvider,
                'sms_api_key' => $this->smsApiKey ?: null,
                'sms_sender_id' => $this->smsSenderId ?: null,
                'email_enabled' => $this->emailEnabled,
                'email_smtp_host' => $this->emailSmtpHost ?: null,
                'email_smtp_port' => $this->emailSmtpPort ?: null,
                'email_smtp_username' => $this->emailSmtpUsername ?: null,
                'email_smtp_password' => $this->emailSmtpPassword ?: null,
                'email_smtp_encryption' => $this->emailSmtpEncryption ?: null,
                'email_from_address' => $this->emailFromAddress ?: null,
                'email_from_name' => $this->emailFromName ?: null,
            ]
        );

        $this->savedMessage = 'সেটিংস সংরক্ষণ করা হয়েছে।';
    }

    public function render()
    {
        return view('livewire.notification-gateway-settings')
            ->layout('components.layouts.app', ['title' => 'SMS / Email গেটওয়ে সেটিংস']);
    }
}
