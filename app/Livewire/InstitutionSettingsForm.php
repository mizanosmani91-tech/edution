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
    public bool $consecutivePeriodBlocking = true;

    // ব্র্যান্ডিং
    public string $themePrimaryColor = '#6C5CE7';
    public string $themeAccentColor = '#F59E0B';

    // চেক-ইন/চেক-আউট geofence
    public ?string $institutionLatitude = null;
    public ?string $institutionLongitude = null;
    public int $geofenceRadius = 150;

    public bool $saved = false;

    public function mount(): void
    {
        abort_unless(auth()->user()->role === 'admin' || auth()->user()->isSuperAdmin(), 403);

        $institution = auth()->user()->institution;
        $settings = $institution->settings;

        $this->institutionName = $institution->name;
        $this->institutionPhone = $institution->phone ?? '';
        $this->institutionAddress = $institution->address ?? '';

        $this->consecutivePeriodBlocking = (bool) ($settings->consecutive_period_blocking ?? true);
        $this->themePrimaryColor = $settings->theme_primary_color ?? '#6C5CE7';
        $this->themeAccentColor = $settings->theme_accent_color ?? '#F59E0B';

        $this->institutionLatitude = $institution->latitude !== null ? (string) $institution->latitude : null;
        $this->institutionLongitude = $institution->longitude !== null ? (string) $institution->longitude : null;
        $this->geofenceRadius = $institution->geofence_radius_meters ?? 150;
    }

    public function setCurrentLocation(string $lat, string $lng): void
    {
        $this->institutionLatitude = $lat;
        $this->institutionLongitude = $lng;
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
            'latitude' => $this->institutionLatitude !== null && $this->institutionLatitude !== '' ? $this->institutionLatitude : null,
            'longitude' => $this->institutionLongitude !== null && $this->institutionLongitude !== '' ? $this->institutionLongitude : null,
            'geofence_radius_meters' => $this->geofenceRadius ?: 150,
        ]);

        InstitutionSetting::updateOrCreate(
            ['institution_id' => auth()->user()->institution_id],
            [
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
