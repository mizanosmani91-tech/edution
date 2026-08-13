<?php

namespace App\Livewire;

use App\Models\Expense;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class ExpenseTracker extends Component
{
    use WithPagination;

    public bool $showModal = false;

    #[Validate('required|string|max:60')]
    public string $category = '';

    #[Validate('required|numeric|min:0')]
    public string $amount = '';

    #[Validate('required|date')]
    public string $date = '';

    public string $description = '';

    public function openModal(): void
    {
        $this->reset(['category', 'amount', 'description']);
        $this->date = now()->toDateString();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        Expense::create([
            'category' => $this->category,
            'amount' => $this->amount,
            'date' => $this->date,
            'description' => $this->description ?: null,
            'recorded_by' => auth()->id(),
        ]);

        $this->showModal = false;
    }

    public function delete(string $id): void
    {
        Expense::findOrFail($id)->delete();
    }

    public function render()
    {
        $expenses = Expense::latest('date')->paginate(20);
        $thisMonthTotal = Expense::whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount');

        return view('livewire.expense-tracker', [
            'expenses' => $expenses,
            'thisMonthTotal' => $thisMonthTotal,
        ])->layout('components.layouts.app', ['title' => 'খরচ ও ব্যয় ট্র্যাকিং']);
    }
}
