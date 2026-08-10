<?php

namespace App\Livewire;

use App\Models\InstitutionSetting;
use Livewire\Component;

class InstitutionSettingsForm extends Component
{
    public bool $hasDepartments = false;
    public bool $consecutivePeriodBlocking = true;
    public string $themePrimaryColor = '#2563eb';
    public string $themeAccentColor = '#16a34a';
    public bool $saved = false;

    public function mount(): void
    {
        abort_unless(auth()->user()->role === 'admin' || auth()->user()->isSuperAdmin(), 403);

        $settings = auth()->user()->institution->settings;
        $this->hasDepartments = (bool) ($settings->has_departments ?? false);
        $this->consecutivePeriodBlocking = (bool) ($settings->consecutive_period_blocking ?? true);
        $this->themePrimaryColor = $settings->theme_primary_color ?? '#2563eb';
        $this->themeAccentColor = $settings->theme_accent_color ?? '#16a34a';
    }

    public function save(): void
    {
        InstitutionSetting::updateOrCreate(
            ['institution_id' => auth()->user()->institution_id],
            [
                'has_departments' => $this->hasDepartments,
                'consecutive_period_blocking' => $this->consecutivePeriodBlocking,
                'theme_primary_color' => $this->themePrimaryColor,
                'theme_accent_color' => $this->themeAccentColor,
            ]
        );

        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.institution-settings-form')->layout('components.layouts.app');
    }
}
