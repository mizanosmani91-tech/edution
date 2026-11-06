<?php

namespace App\Livewire;

use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * আমার প্রোফাইল — সব রোলের জন্য (অ্যাডমিন, শিক্ষক, অভিভাবক, শিক্ষার্থী)।
 * নিজের নাম, ফোন, ইমেইল ও পাসওয়ার্ড বদলানো যায়। ইমেইল লগইনের পরিচয়, তাই
 * ইমেইল বদলাতে বা পাসওয়ার্ড বদলাতে বর্তমান পাসওয়ার্ড লাগে।
 */
class MyProfile extends Component
{
    public string $name = '';
    public string $phone = '';
    public string $email = '';

    public string $email_password = '';      // ইমেইল বদলানোর জন্য
    public string $current_password = '';    // পাসওয়ার্ড বদলানোর জন্য
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public bool $profileSaved = false;
    public bool $passwordSaved = false;

    public function mount(): void
    {
        $user = auth()->user();

        $this->name = (string) $user->name;
        $this->phone = (string) $user->phone;
        $this->email = (string) $user->email;
    }

    private function throttleKey(string $action): string
    {
        return 'my-profile:' . $action . ':' . auth()->id();
    }

    /**
     * বর্তমান পাসওয়ার্ড মেলে কিনা। ভুল হলে ত্রুটি বসিয়ে false দেয়; ৫ বার ভুলে ৫ মিনিট
     * আটকে দেয় — অনুমান করে চেষ্টা ঠেকাতে। (exception নয়, bool: Livewire-এর
     * ত্রুটি-ব্যাগে বার্তা নিশ্চিতভাবে থাকে।)
     */
    private function checkCurrentPassword(string $value, string $field): bool
    {
        $key = $this->throttleKey('password');

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError($field, 'অনেকবার ভুল হয়েছে। কিছুক্ষণ পরে আবার চেষ্টা করুন।');

            return false;
        }

        if (! Hash::check($value, auth()->user()->password)) {
            RateLimiter::hit($key, 300);
            $this->addError($field, 'বর্তমান পাসওয়ার্ড সঠিক নয়।');

            return false;
        }

        RateLimiter::clear($key);

        return true;
    }

    public function saveProfile(): void
    {
        $this->profileSaved = false;
        $user = auth()->user();

        $this->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            // ইমেইল শুধু এই প্রতিষ্ঠানের মধ্যে অনন্য হতে হয় (institution_id + email)
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')
                    ->where('institution_id', $user->institution_id)
                    ->ignore($user->id),
            ],
        ], [], [
            'name' => 'নাম',
            'phone' => 'ফোন',
            'email' => 'ইমেইল',
        ]);

        $emailChanged = strcasecmp($this->email, (string) $user->email) !== 0;

        if ($emailChanged) {
            $this->validate(['email_password' => 'required'], [], ['email_password' => 'বর্তমান পাসওয়ার্ড']);

            if (! $this->checkCurrentPassword($this->email_password, 'email_password')) {
                return;
            }
        }

        $user->update([
            'name' => $this->name,
            'phone' => $this->phone ?: null,
            'email' => $this->email,
        ]);

        // শিক্ষকের নিজের রেকর্ডেও নাম/ফোন মিলিয়ে রাখা (শিক্ষক তালিকা সেখান থেকেই দেখায়)
        if ($user->role === 'teacher' && $user->teacher_id) {
            Teacher::whereKey($user->teacher_id)->update([
                'name' => $this->name,
                'phone' => $this->phone ?: null,
            ]);
        }

        $this->email_password = '';
        $this->profileSaved = true;
        $this->dispatch('toast', message: 'প্রোফাইল সংরক্ষণ করা হয়েছে');
    }

    public function changePassword(): void
    {
        $this->passwordSaved = false;

        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed|different:current_password',
        ], [], [
            'current_password' => 'বর্তমান পাসওয়ার্ড',
            'new_password' => 'নতুন পাসওয়ার্ড',
        ]);

        if (! $this->checkCurrentPassword($this->current_password, 'current_password')) {
            return;
        }

        auth()->user()->forceFill([
            'password' => $this->new_password, // User-এর 'hashed' cast নিজেই হ্যাশ করে
            'must_change_password' => false,
        ])->save();

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->passwordSaved = true;
        $this->dispatch('toast', message: 'পাসওয়ার্ড বদলানো হয়েছে');
    }

    public function render()
    {
        return view('livewire.my-profile', [
            'user' => auth()->user(),
        ])->layout('components.layouts.app', ['title' => 'আমার প্রোফাইল']);
    }
}
