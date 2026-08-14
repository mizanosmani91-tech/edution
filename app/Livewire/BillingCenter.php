<?php

namespace App\Livewire;

use App\Models\InstitutionPayment;
use App\Models\WalletTransaction;
use App\Services\BillingService;
use Livewire\Component;

/**
 * BillingCenter — tenant admin এর জন্য নিজের প্রতিষ্ঠানের বিলিং স্ট্যাটাস
 * দেখা ও ম্যানুয়াল পেমেন্ট/টপ-আপ সাবমিট করার পেজ। সাবমিট করা মাত্র টাকা
 * যোগ/অ্যাক্টিভ হয় না — superadmin অনুমোদন করলেই (SuperadminDashboard::
 * approvePayment) কার্যকর হয়, ঠিক আগের ধাপে guardian fee-claim এর মতোই
 * দুই-ধাপ ভেরিফিকেশন প্যাটার্ন।
 */
class BillingCenter extends Component
{
    public string $payAmount = '';
    public string $payMethod = 'bkash';
    public string $payRef = '';
    public bool $showPayModal = false;
    public string $payPurpose = 'subscription'; // subscription | wallet_topup

    public function mount(): void
    {
        abort_unless(auth()->user()->role === 'admin', 403, 'শুধু এডমিন এই পেজ দেখতে পারবেন');
    }

    public function openPayModal(string $purpose): void
    {
        $this->payPurpose = $purpose;
        $this->payAmount = '';
        $this->payRef = '';
        $this->showPayModal = true;
    }

    public function closePayModal(): void
    {
        $this->showPayModal = false;
    }

    public function submitPayment(): void
    {
        $this->validate([
            'payAmount' => ['required', 'numeric', 'min:1'],
            'payMethod' => ['required', 'string'],
            'payRef' => ['required', 'string', 'max:100'],
        ]);

        $institution = auth()->user()->institution;

        InstitutionPayment::create([
            'institution_id' => $institution->id,
            'amount' => $this->payAmount,
            'method' => $this->payMethod,
            'transaction_ref' => $this->payRef,
            'for_month' => now()->format('Y-m'),
            'purpose' => $this->payPurpose,
            'status' => 'pending',
            'submitted_by' => auth()->id(),
        ]);

        $this->showPayModal = false;
        $this->dispatch('toast', message: 'পেমেন্ট সাবমিট করা হয়েছে, এডমিন যাচাই করে অনুমোদন করবে');
    }

    public function render()
    {
        $institution = auth()->user()->institution;
        $billing = app(BillingService::class);

        return view('livewire.billing-center', [
            'institution' => $institution,
            'activeStudentCount' => $billing->activeStudentCount($institution),
            'postpaidDue' => $billing->postpaidDueAmount($institution),
            'prepaidMonthlyCost' => $billing->prepaidMonthlyCost($institution),
            'payments' => InstitutionPayment::where('institution_id', $institution->id)->latest()->limit(20)->get(),
            'walletTransactions' => $institution->isPrepaid()
                ? WalletTransaction::where('institution_id', $institution->id)->latest()->limit(20)->get()
                : collect(),
            'tiers' => BillingService::POSTPAID_TIERS,
        ])->layout('components.layouts.app', ['title' => 'বিলিং']);
    }
}
