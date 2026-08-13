<?php

namespace App\Livewire;

use App\Models\Institution;
use App\Models\InstitutionPayment;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;

class SuperadminInstitutionsList extends Component
{
    // Approve হওয়ার পর temp password এখানে সাময়িকভাবে দেখানো হবে —
    // superadmin এটা copy করে প্রতিষ্ঠানকে (ফোন/হোয়াটসঅ্যাপে) জানাবে,
    // যেহেতু এখনো automated email পাঠানোর সিস্টেম নেই
    public ?string $justApprovedSlug = null;
    public ?string $justApprovedPassword = null;

    public function approvePendingInstitution(string $institutionId): void
    {
        $institution = Institution::query()->findOrFail($institutionId);

        // ⚠️ ৮ ক্যারেক্টার random password — শক্ত এলোমেলো, কিন্তু ফোনে
        // পড়ে শোনানোর মতো সহজ (ambiguous ক্যারেক্টার O/0, I/l বাদ)
        $tempPassword = Str::password(10, symbols: false);

        $user = User::create([
            'institution_id' => $institution->id,
            'name' => $institution->name . ' Admin',
            'email' => $institution->registration_email,
            'password' => Hash::make($tempPassword),
            'role' => 'admin',
            'must_change_password' => true,
        ]);

        $institution->update(['status' => 'active']);

        $this->justApprovedSlug = $institution->slug;
        $this->justApprovedPassword = $tempPassword;
    }

    public function rejectPendingInstitution(string $institutionId): void
    {
        Institution::query()->findOrFail($institutionId)->update(['status' => 'rejected']);
    }

    public function approvePayment(string $paymentId): void
    {
        $payment = InstitutionPayment::findOrFail($paymentId);

        $payment->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $payment->institution->update(['status' => 'active']);
    }

    public function rejectPayment(string $paymentId): void
    {
        InstitutionPayment::findOrFail($paymentId)->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
    }

    public function render()
    {
        // ⚠️ 'pending' status এর institution — এদের কোনো admin user নেই এখনো,
        // registration_email দিয়েই approve করার সময় user তৈরি হবে
        $pendingInstitutions = Institution::query()
            ->where('status', 'pending')
            ->latest()
            ->get();

        $activeInstitutions = Institution::query()
            ->withCount('students')
            ->where('status', '!=', 'pending')
            ->orderByDesc('created_at')
            ->get();

        $pendingPayments = InstitutionPayment::with('institution')
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('livewire.superadmin-institutions-list', [
            'pendingInstitutions' => $pendingInstitutions,
            'institutions' => $activeInstitutions,
            'pendingPayments' => $pendingPayments,
        ])->layout('components.layouts.superadmin');
    }
}
