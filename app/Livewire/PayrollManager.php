<?php

namespace App\Livewire;

use App\Models\PayrollRecord;
use App\Models\Teacher;
use Livewire\Component;

class PayrollManager extends Component
{
    public int $month;
    public int $year;

    public bool $showAdjustModal = false;
    public ?string $adjustingId = null;
    public string $otherAllowance = '0';
    public string $deductions = '0';
    public string $deductionReason = '';

    public function mount(): void
    {
        $this->month = (int) now()->month;
        $this->year = (int) now()->year;
    }

    public function generatePayroll(): void
    {
        $teachers = Teacher::where('status', 'active')->get();

        foreach ($teachers as $t) {
            if (PayrollRecord::where('teacher_id', $t->id)->where('month', $this->month)->where('year', $this->year)->exists()) {
                continue;
            }

            $base = (float) ($t->base_salary ?? 0);
            $rent = (float) ($t->house_rent ?? 0);
            $medical = (float) ($t->medical_allowance ?? 0);

            PayrollRecord::create([
                'teacher_id' => $t->id,
                'month' => $this->month,
                'year' => $this->year,
                'base_salary' => $base,
                'house_rent' => $rent,
                'medical_allowance' => $medical,
                'other_allowance' => 0,
                'deductions' => 0,
                'net_pay' => $base + $rent + $medical,
                'status' => 'pending',
            ]);
        }
    }

    public function openAdjust(string $id): void
    {
        $record = PayrollRecord::findOrFail($id);
        $this->adjustingId = $id;
        $this->otherAllowance = (string) $record->other_allowance;
        $this->deductions = (string) $record->deductions;
        $this->deductionReason = $record->deduction_reason ?? '';
        $this->showAdjustModal = true;
    }

    public function saveAdjust(): void
    {
        $this->validate([
            'otherAllowance' => 'required|numeric|min:0',
            'deductions' => 'required|numeric|min:0',
        ]);

        $record = PayrollRecord::findOrFail($this->adjustingId);
        $netPay = $record->base_salary + $record->house_rent + $record->medical_allowance
            + (float) $this->otherAllowance - (float) $this->deductions;

        $record->update([
            'other_allowance' => $this->otherAllowance,
            'deductions' => $this->deductions,
            'deduction_reason' => $this->deductionReason ?: null,
            'net_pay' => max(0, $netPay),
        ]);

        $this->showAdjustModal = false;
    }

    public function markPaid(string $id): void
    {
        PayrollRecord::findOrFail($id)->update([
            'status' => 'paid',
            'paid_date' => now()->toDateString(),
            'paid_by' => auth()->id(),
        ]);
    }

    public function render()
    {
        $records = PayrollRecord::with('teacher')
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->get()
            ->sortBy(fn ($r) => $r->teacher->name ?? '');

        return view('livewire.payroll-manager', [
            'records' => $records,
            'totalNet' => $records->sum('net_pay'),
            'paidCount' => $records->where('status', 'paid')->count(),
            'pendingCount' => $records->where('status', 'pending')->count(),
        ])->layout('components.layouts.app', ['title' => 'পে-রোল / বেতন ব্যবস্থাপনা']);
    }
}
