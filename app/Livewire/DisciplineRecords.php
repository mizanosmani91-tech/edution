<?php

namespace App\Livewire;

use App\Models\DisciplineRecord;
use App\Models\SchoolClass;
use App\Models\Student;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class DisciplineRecords extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public ?string $classId = null;
    public string $search = '';

    #[Validate('required|uuid')]
    public string $studentId = '';

    #[Validate('required|date')]
    public string $date = '';

    #[Validate('required|string')]
    public string $category = 'general';

    #[Validate('required|string')]
    public string $severity = 'minor';

    #[Validate('required|string|min:3')]
    public string $description = '';

    public string $actionTaken = '';

    public function openModal(): void
    {
        $this->reset(['studentId', 'category', 'severity', 'description', 'actionTaken']);
        $this->category = 'general';
        $this->severity = 'minor';
        $this->date = now()->toDateString();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        DisciplineRecord::create([
            'student_id' => $this->studentId,
            'date' => $this->date,
            'category' => $this->category,
            'severity' => $this->severity,
            'description' => $this->description,
            'action_taken' => $this->actionTaken ?: null,
            'recorded_by' => auth()->id(),
        ]);

        $this->showModal = false;
    }

    public function delete(string $id): void
    {
        DisciplineRecord::findOrFail($id)->delete();
    }

    public function render()
    {
        $records = DisciplineRecord::with('student')
            ->when($this->classId, fn ($q) => $q->whereHas('student', fn ($q2) => $q2->where('class_id', $this->classId)))
            ->when($this->search, fn ($q) => $q->whereHas('student', fn ($q2) => $q2->where('name', 'ilike', '%'.$this->search.'%')))
            ->latest('date')
            ->paginate(20);

        return view('livewire.discipline-records', [
            'records' => $records,
            'classes' => SchoolClass::orderBy('display_order')->get(),
            'studentOptions' => Student::where('status', 'active')->orderBy('name')->limit(300)->get(),
        ])->layout('components.layouts.app', ['title' => 'শিক্ষার্থী আচরণ রেকর্ড']);
    }
}
