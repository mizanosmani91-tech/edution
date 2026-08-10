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

    public function render()
    {
        $fees = FeeCollection::with('student')
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->monthFilter, fn ($q) => $q->where('due_month', $this->monthFilter))
            ->latest('due_month')
            ->paginate(15);

        return view('livewire.fee-collection-list', ['fees' => $fees])->layout('components.layouts.app');
    }
}
