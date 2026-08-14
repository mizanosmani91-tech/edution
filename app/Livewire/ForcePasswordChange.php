<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

/**
 * ForcePasswordChange
 *
 * সাময়িক পাসওয়ার্ড দিয়ে (রেজিস্ট্রেশন অনুমোদনের পর SMS-এ পাওয়া কোড, বা
 * superadmin-এর reset করা পাসওয়ার্ড) প্রথমবার লগইন করলে এই পেজ বাধ্যতামূলক
 * দেখানো হয় (EnsurePasswordChanged middleware দেখুন) — নতুন পাসওয়ার্ড সেট
 * না করা পর্যন্ত অন্য কোনো পেজে যাওয়া যায় না।
 */
class ForcePasswordChange extends Component
{
    public string $password = '';
    public string $password_confirmation = '';

    public function save(): void
    {
        $this->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($this->password),
            'must_change_password' => false,
        ]);

        if ($user->isSuperAdmin()) {
            $this->redirectRoute('superadmin.institutions', navigate: false);
            return;
        }

        $this->redirectRoute('dashboard', navigate: false);
    }

    public function render()
    {
        $isSuperAdmin = Auth::user()?->isSuperAdmin();

        return view(
            $isSuperAdmin
                ? 'livewire.superadmin-force-password-change'
                : 'livewire.force-password-change'
        )->layout('components.layouts.blank');
    }
}
