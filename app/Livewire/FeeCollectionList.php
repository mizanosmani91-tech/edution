<?php

namespace App\Livewire;

use App\Models\FeeCollection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class FeeCollectionList extends Component
{
    use WithPagination;

    public string $statusFilter = 'due'; // due / partial / paid / overdue / '' (সব)
    public string $monthFilter = ''; // 'YYYY-MM', খালি মানে সব মাস

    // দ্রুত পেমেন্ট এন্ট্রির জন্য (মোবাইলে collector প্রায়ই এখানেই কাজ করে)
    public ?string $payingId = null;

    #[Validate('required|numeric|min:0')]
    public string $payAmount = '';

    #[Validate('required|in:bkash,nagad,bank_transfer,cash')]
    public string $payMethod = 'cash';

    public function openPayModal(string $feeId): void
    {
        $this->payingId = $feeId;
        $this->payAmount = '';
    }

    public function recordPayment(): void
    {
        $this->validate();

        // ⚠️ route model binding না, ম্যানুয়াল find — global scope এখানেও
        // প্রযোজ্য, তাই অন্য institution এর id দিলেও কিছু পাওয়া যাবে না
        $fee = FeeCollection::findOrFail($this->payingId);

        DB::transaction(function () use ($fee) {
            $newPaid = $fee->amount_paid + (float) $this->payAmount;
            $fee->update([
                'amount_paid' => $newPaid,
                'payment_method' => $this->payMethod,
                'paid_at' => now(),
                'status' => $newPaid >= $fee->amount_due ? 'paid' : 'partial',
            ]);
        });

        $this->payingId = null;
        $this->payAmount = '';
    }

    /**
     * অভিভাবক পোর্টাল থেকে "আমি পেমেন্ট করেছি" দাবি জমা দিলে সেটা এখানে
     * এডমিন যাচাই করে কনফার্ম/বাতিল করতে পারবে। কনফার্ম করলেই তখন আসল
     * amount_paid/status আপডেট হয় — গার্ডিয়ান নিজে সরাসরি বদলাতে পারে না।
     */
    public function confirmGuardianClaim(string $feeId): void
    {
        $fee = FeeCollection::findOrFail($feeId);

        if ($fee->guardian_claim_status !== 'pending') {
            return;
        }

        DB::transaction(function () use ($fee) {
            $newPaid = $fee->amount_paid + (float) $fee->guardian_claimed_amount;
            $fee->update([
                'amount_paid' => $newPaid,
                'payment_method' => $fee->guardian_claimed_method,
                'transaction_ref' => $fee->guardian_claimed_ref,
                'paid_at' => now(),
                'status' => $newPaid >= $fee->amount_due ? 'paid' : 'partial',
                'collected_by' => auth()->id(),
                'guardian_claim_status' => 'confirmed',
            ]);
        });

        $this->dispatch('toast', message: 'অভিভাবকের পেমেন্ট দাবি নিশ্চিত করে হিসেবে যোগ করা হয়েছে।');
    }

    public function rejectGuardianClaim(string $feeId): void
    {
        $fee = FeeCollection::findOrFail($feeId);

        $fee->update([
            'guardian_claim_status' => 'rejected',
        ]);

        $this->dispatch('toast', message: 'দাবিটি প্রত্যাখ্যান করা হয়েছে।');
    }

    public function render()
    {
        $fees = FeeCollection::with('student')
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->monthFilter, fn ($q) => $q->where('due_month', $this->monthFilter))
            ->latest('due_month')
            ->paginate(15);

        $pendingClaimsCount = FeeCollection::where('guardian_claim_status', 'pending')->count();

        return view('livewire.fee-collection-list', ['fees' => $fees, 'pendingClaimsCount' => $pendingClaimsCount])->layout('components.layouts.app');
    }
}
