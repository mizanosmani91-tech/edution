<?php

namespace App\Livewire;

use App\Models\InstitutionSetting;
use Livewire\Component;

class InstitutionSettingsForm extends Component
{
    // প্রতিষ্ঠান প্রোফাইল
    public string $institutionName = '';
    public string $institutionPhone = '';
    public string $institutionAddress = '';

    // ফিচার টগল
    public bool $hasDepartments = false;
    public bool $consecutivePeriodBlocking = true;

    // ব্র্যান্ডিং
    public string $themePrimaryColor = '#5C1A2B';
    public string $themeAccentColor = '#C9A227';

    public bool $saved = false;

    public function mount(): void
    {
        abort_unless(auth()->user()->role === 'admin' || auth()->user()->isSuperAdmin(), 403);

        $institution = auth()->user()->institution;
        $settings = $institution->settings;

        $this->institutionName = $institution->name;
        $this->institutionPhone = $institution->phone ?? '';
        $this->institutionAddress = $institution->address ?? '';

        $this->hasDepartments = (bool) ($settings->has_departments ?? false);
        $this->consecutivePeriodBlocking = (bool) ($settings->consecutive_period_blocking ?? true);
        $this->themePrimaryColor = $settings->theme_primary_color ?? '#5C1A2B';
        $this->themeAccentColor = $settings->theme_accent_color ?? '#C9A227';
    }

    public function save(): void
    {
        $this->validate([
            'institutionName' => 'required|string|max:255',
            'institutionPhone' => 'nullable|string|max:20',
        ]);

        auth()->user()->institution->update([
            'name' => $this->institutionName,
            'phone' => $this->institutionPhone ?: null,
            'address' => $this->institutionAddress ?: null,
        ]);

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
        return view('livewire.institution-settings-form')
            ->layout('components.layouts.app', ['title' => 'সেটিংস']);
    }
}
