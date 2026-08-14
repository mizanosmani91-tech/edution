<?php

namespace App\Livewire;

use App\Models\Scholarship;
use App\Models\Student;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class ScholarshipManager extends Component
{
    use WithPagination;

    public string $statusFilter = 'active';

    public bool $showModal = false;
    public ?string $editingId = null;

    #[Validate('required|uuid')]
    public string $studentId = '';

    #[Validate('required|string')]
    public string $type = 'scholarship';

    #[Validate('required|string')]
    public string $discountMode = 'percentage';

    #[Validate('required|numeric|min:0')]
    public string $discountValue = '';

    public string $reason = '';
    public string $validFrom = '';
    public string $validTo = '';

    public function openModal(): void
    {
        $this->reset(['editingId', 'studentId', 'discountValue', 'reason', 'validTo']);
        $this->type = 'scholarship';
        $this->discountMode = 'percentage';
        $this->validFrom = now()->toDateString();
        $this->showModal = true;
    }

    public function edit(string $id): void
    {
        $s = Scholarship::findOrFail($id);
        $this->editingId = $id;
        $this->studentId = $s->student_id;
        $this->type = $s->type;
        $this->discountMode = $s->discount_mode;
        $this->discountValue = (string) $s->discount_value;
        $this->reason = $s->reason ?? '';
        $this->validFrom = $s->valid_from?->toDateString() ?? '';
        $this->validTo = $s->valid_to?->toDateString() ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        Scholarship::updateOrCreate(
            ['id' => $this->editingId],
            [
                'student_id' => $this->studentId,
                'type' => $this->type,
                'discount_mode' => $this->discountMode,
                'discount_value' => $this->discountValue,
                'reason' => $this->reason ?: null,
                'valid_from' => $this->validFrom ?: null,
                'valid_to' => $this->validTo ?: null,
                'status' => 'active',
                'approved_by' => auth()->id(),
            ]
        );

        $this->showModal = false;
    }

    public function revoke(string $id): void
    {
        Scholarship::findOrFail($id)->update(['status' => 'revoked']);
    }

    public function delete(string $id): void
    {
        Scholarship::findOrFail($id)->delete();
    }

    public function render()
    {
        $scholarships = Scholarship::with('student')
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);

        return view('livewire.scholarship-manager', [
            'scholarships' => $scholarships,
            'students' => Student::where('status', 'active')->orderBy('name')->limit(500)->get(),
        ])->layout('components.layouts.app', ['title' => 'বৃত্তি ও মওকুফ']);
    }
}
