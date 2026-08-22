<?php

namespace App\Livewire;

use App\Models\Budget;
use App\Models\Expense;
use Illuminate\Support\Carbon;
use Livewire\Component;

/**
 * BudgetManager — নির্বাচিত মাসের জন্য প্রতিটা খরচ-ক্যাটাগরির বাজেট বেঁধে
 * দেওয়া হয়, আর ExpenseTracker এ ইতিমধ্যে যা খরচ হয়ে গেছে তার সাথে তুলনা
 * করে over/under দেখানো হয়। নতুন ক্যাটাগরিও এখান থেকে যোগ করা যায় (আগে
 * কখনো খরচ না হলেও বাজেট বেঁধে রাখা যায়)।
 */
class BudgetManager extends Component
{
    public string $periodMonth;

    /** @var array<string,string> category => planned amount (string, editable input) */
    public array $planned = [];

    public string $newCategory = '';

    public bool $saved = false;

    public function mount(): void
    {
        $this->periodMonth = now()->format('Y-m');
        $this->loadPlanned();
    }

    public function updatedPeriodMonth(): void
    {
        $this->loadPlanned();
    }

    private function loadPlanned(): void
    {
        $categories = $this->allCategories();

        $existing = Budget::where('period_month', $this->periodMonth)->pluck('planned_amount', 'category');

        $this->planned = [];
        foreach ($categories as $cat) {
            $this->planned[$cat] = $existing->has($cat) ? (string) $existing->get($cat) : '';
        }

        $this->saved = false;
    }

    private function allCategories()
    {
        $fromExpenses = Expense::whereNotNull('category')->distinct()->pluck('category');
        $fromBudgets = Budget::whereNotNull('category')->distinct()->pluck('category');

        return $fromExpenses->merge($fromBudgets)->unique()->sort()->values();
    }

    public function addCategory(): void
    {
        $cat = trim($this->newCategory);

        if ($cat === '' || isset($this->planned[$cat])) {
            $this->newCategory = '';

            return;
        }

        $this->planned[$cat] = '';
        $this->newCategory = '';
    }

    public function save(): void
    {
        foreach ($this->planned as $category => $amount) {
            if ($amount === '' || $amount === null) {
                continue;
            }

            Budget::updateOrCreate(
                ['category' => $category, 'period_month' => $this->periodMonth],
                ['planned_amount' => (float) $amount]
            );
        }

        $this->saved = true;
        $this->dispatch('toast', message: 'বাজেট সংরক্ষণ করা হয়েছে।');
    }

    public function render()
    {
        $start = Carbon::createFromFormat('Y-m', $this->periodMonth)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $spent = Expense::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->whereNotNull('category')
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        $rows = [];
        $totalPlanned = 0;
        $totalSpent = 0;

        foreach ($this->planned as $category => $plannedAmount) {
            $plannedVal = $plannedAmount !== '' ? (float) $plannedAmount : 0;
            $spentVal = (float) ($spent->get($category) ?? 0);
            $totalPlanned += $plannedVal;
            $totalSpent += $spentVal;

            $rows[] = [
                'category' => $category,
                'planned' => $plannedVal,
                'spent' => $spentVal,
                'percent' => $plannedVal > 0 ? min(200, round(($spentVal / $plannedVal) * 100)) : ($spentVal > 0 ? 100 : 0),
                'over' => $plannedVal > 0 && $spentVal > $plannedVal,
            ];
        }

        return view('livewire.budget-manager', [
            'rows' => $rows,
            'totalPlanned' => $totalPlanned,
            'totalSpent' => $totalSpent,
        ])->layout('components.layouts.app', ['title' => 'বাজেট পরিকল্পনা']);
    }
}
