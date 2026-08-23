<?php

namespace App\Livewire;

use App\Models\FeeCollection;
use App\Models\FeeInstallmentPlan;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * FeeInstallmentPlans
 *
 * বড় অঙ্কের ফি (ভর্তি ফি, পরীক্ষার ফি ইত্যাদি) কয়েক কিস্তিতে ভাগ করার
 * ব্যবস্থা — এটা প্রতিযোগী কোনো স্কুল ম্যানেজমেন্ট সফটওয়্যারে দেখা যায়নি।
 * প্ল্যান তৈরি করলেই সমান-ভাগে ভাগ হওয়া কিস্তিগুলো আলাদা fee_collections
 * সারি হিসেবে তৈরি হয়ে যায়, তাই বকেয়া তালিকা/অভিভাবক পোর্টাল/SMS
 * রিমাইন্ডার — সব বিদ্যমান ফিচারই স্বয়ংক্রিয়ভাবে কিস্তি সাপোর্ট করবে।
 */
class FeeInstallmentPlans extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public string $search = '';
    public ?string $selectedStudentId = null;

    public string $feeType = 'ভর্তি ফি';
    public string $totalAmount = '';
    public int $installmentsCount = 3;
    public string $startMonth = '';
    public string $note = '';

    public function mount(): void
    {
        $this->startMonth = now()->format('Y-m');
    }

    public function openModal(): void
    {
        $this->reset(['selectedStudentId', 'totalAmount', 'note', 'search']);
        $this->feeType = 'ভর্তি ফি';
        $this->installmentsCount = 3;
        $this->startMonth = now()->format('Y-m');
        $this->showModal = true;
    }

    public function selectStudent(string $id): void
    {
        $this->selectedStudentId = $id;
        $this->search = '';
    }

    public function getStudentsProperty()
    {
        if (! $this->search) {
            return collect();
        }

        return Student::where('status', 'active')
            ->where(function ($q) {
                $q->where('name', 'ilike', '%'.$this->search.'%')
                    ->orWhere('student_id_no', 'ilike', '%'.$this->search.'%');
            })
            ->orderBy('name')
            ->limit(15)
            ->get();
    }

    public function getSelectedStudentProperty()
    {
        return $this->selectedStudentId ? Student::find($this->selectedStudentId) : null;
    }

    public function createPlan(): void
    {
        $this->validate([
            'selectedStudentId' => ['required'],
            'feeType' => ['required', 'string', 'max:255'],
            'totalAmount' => ['required', 'numeric', 'min:1'],
            'installmentsCount' => ['required', 'integer', 'min:2', 'max:12'],
            'startMonth' => ['required', 'date_format:Y-m'],
        ]);

        DB::transaction(function () {
            $plan = FeeInstallmentPlan::create([
                'student_id' => $this->selectedStudentId,
                'fee_type' => $this->feeType,
                'total_amount' => $this->totalAmount,
                'installments_count' => $this->installmentsCount,
                'start_month' => $this->startMonth,
                'note' => $this->note ?: null,
                'created_by' => auth()->id(),
            ]);

            $total = (float) $this->totalAmount;
            $count = $this->installmentsCount;
            $baseShare = floor(($total / $count) * 100) / 100; // ভগ্নাংশ এড়াতে
            $remainder = round($total - ($baseShare * $count), 2);

            $cursor = \Carbon\Carbon::createFromFormat('Y-m', $this->startMonth);

            for ($i = 1; $i <= $count; $i++) {
                $share = $baseShare;
                if ($i === $count) {
                    // শেষ কিস্তিতে পূর্ণাঙ্গ যোগফল মেলাতে বাকি অংশ যোগ করা হয়
                    $share = round($baseShare + $remainder, 2);
                }

                FeeCollection::create([
                    'student_id' => $this->selectedStudentId,
                    'fee_type' => $this->feeType.' (কিস্তি '.$i.'/'.$count.')',
                    'amount_due' => $share,
                    'amount_paid' => 0,
                    'due_month' => $cursor->format('Y-m'),
                    'status' => 'due',
                    'installment_plan_id' => $plan->id,
                    'installment_number' => $i,
                ]);

                $cursor->addMonth();
            }
        });

        $this->showModal = false;
        $this->dispatch('toast', message: 'কিস্তি প্ল্যান তৈরি হয়েছে — '.$this->installmentsCount.'টি কিস্তি বকেয়া তালিকায় যোগ হয়েছে।');
    }

    public function render()
    {
        $plans = FeeInstallmentPlan::with(['student', 'installments'])
            ->latest()
            ->paginate(15);

        return view('livewire.fee-installment-plans', ['plans' => $plans])->layout('components.layouts.app', ['title' => 'ফি কিস্তি প্ল্যান']);
    }
}
