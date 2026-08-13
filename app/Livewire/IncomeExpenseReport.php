<?php

namespace App\Livewire;

use App\Models\Expense;
use App\Models\FeeCollection;
use Illuminate\Support\Carbon;
use Livewire\Component;

class IncomeExpenseReport extends Component
{
    public string $from;
    public string $to;

    public function mount(): void
    {
        $this->from = Carbon::now()->startOfMonth()->toDateString();
        $this->to = Carbon::now()->toDateString();
    }

    public function render()
    {
        $income = FeeCollection::whereBetween('paid_at', [$this->from, Carbon::parse($this->to)->endOfDay()])
            ->where('amount_paid', '>', 0)
            ->get();

        $expenses = Expense::whereBetween('date', [$this->from, $this->to])->get();

        $totalIncome = $income->sum('amount_paid');
        $totalExpense = $expenses->sum('amount');

        $incomeByType = $income->groupBy('fee_type')->map(fn ($g) => $g->sum('amount_paid'));
        $expenseByCategory = $expenses->groupBy('category')->map(fn ($g) => $g->sum('amount'));

        return view('livewire.income-expense-report', [
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'net' => $totalIncome - $totalExpense,
            'incomeByType' => $incomeByType,
            'expenseByCategory' => $expenseByCategory,
        ])->layout('components.layouts.app', ['title' => 'আয়-ব্যয় রিপোর্ট']);
    }
}
