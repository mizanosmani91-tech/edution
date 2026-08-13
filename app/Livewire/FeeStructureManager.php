<?php

namespace App\Livewire;

use App\Models\FeeStructure;
use App\Models\SchoolClass;
use Livewire\Attributes\Validate;
use Livewire\Component;

class FeeStructureManager extends Component
{
    public bool $showModal = false;
    public ?string $editingId = null;

    public ?string $classId = null;

    #[Validate('required|string|max:60')]
    public string $feeType = '';

    #[Validate('required|numeric|min:0')]
    public string $amount = '';

    #[Validate('required|in:monthly,termly,yearly,one_time')]
    public string $frequency = 'monthly';

    public function openModal(?string $id = null): void
    {
        $this->reset(['classId', 'feeType', 'amount']);
        $this->frequency = 'monthly';
        $this->editingId = $id;

        if ($id) {
            $fs = FeeStructure::findOrFail($id);
            $this->classId = $fs->class_id;
            $this->feeType = $fs->fee_type;
            $this->amount = (string) $fs->amount;
            $this->frequency = $fs->frequency;
        }

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        FeeStructure::updateOrCreate(
            ['id' => $this->editingId],
            [
                'class_id' => $this->classId ?: null,
                'fee_type' => $this->feeType,
                'amount' => $this->amount,
                'frequency' => $this->frequency,
            ]
        );

        $this->showModal = false;
    }

    public function toggleActive(string $id): void
    {
        $fs = FeeStructure::findOrFail($id);
        $fs->update(['is_active' => !$fs->is_active]);
    }

    public function delete(string $id): void
    {
        FeeStructure::findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.fee-structure-manager', [
            'structures' => FeeStructure::with('schoolClass')->orderBy('fee_type')->get(),
            'classes' => SchoolClass::orderBy('display_order')->get(),
        ])->layout('components.layouts.app', ['title' => 'ফি স্ট্রাকচার সেটআপ']);
    }
}
