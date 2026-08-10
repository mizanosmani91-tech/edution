<?php

namespace App\Livewire;

use App\Models\Institution;
use App\Models\InstitutionPayment;
use Livewire\Component;

class SuperadminInstitutionsList extends Component
{
    public ?string $reviewingPaymentId = null;

    public function approvePayment(string $paymentId): void
    {
        $payment = InstitutionPayment::findOrFail($paymentId);

        $payment->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        // পেমেন্ট approve হলে institution active করে দেওয়া, trial শেষ হয়ে
        // থাকলেও যেন suspend না থাকে
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
        $institutions = Institution::withCount([
                'students',
            ])
            ->with(['users' => fn ($q) => $q->where('role', 'admin')->limit(1)])
            ->orderByDesc('created_at')
            ->get();

        $pendingPayments = InstitutionPayment::with('institution')
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('livewire.superadmin-institutions-list', [
            'institutions' => $institutions,
            'pendingPayments' => $pendingPayments,
        ])->layout('components.layouts.superadmin');
    }
}
