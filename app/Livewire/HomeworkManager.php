<?php

namespace App\Livewire;

use App\Models\Homework;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class HomeworkManager extends Component
{
    use WithPagination;

    public ?string $classFilter = null;

    public bool $showModal = false;
    public ?string $editingId = null;

    #[Validate('required|string|max:150')]
    public string $title = '';

    public string $description = '';

    #[Validate('required|uuid')]
    public string $classId = '';

    public ?string $sectionId = null;
    public ?string $subjectId = null;
    public ?string $teacherId = null;

    #[Validate('required|date')]
    public string $assignedDate = '';

    #[Validate('required|date')]
    public string $dueDate = '';

    public function openModal(): void
    {
        $this->reset(['editingId', 'title', 'description', 'classId', 'sectionId', 'subjectId', 'teacherId']);
        $this->assignedDate = now()->toDateString();
        $this->dueDate = now()->addDays(3)->toDateString();
        $this->showModal = true;
    }

    public function edit(string $id): void
    {
        $h = Homework::findOrFail($id);
        $this->editingId = $id;
        $this->title = $h->title;
        $this->description = $h->description ?? '';
        $this->classId = $h->class_id;
        $this->sectionId = $h->section_id;
        $this->subjectId = $h->subject_id;
        $this->teacherId = $h->teacher_id;
        $this->assignedDate = $h->assigned_date->toDateString();
        $this->dueDate = $h->due_date->toDateString();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        Homework::updateOrCreate(
            ['id' => $this->editingId],
            [
                'title' => $this->title,
                'description' => $this->description ?: null,
                'class_id' => $this->classId,
                'section_id' => $this->sectionId ?: null,
                'subject_id' => $this->subjectId ?: null,
                'teacher_id' => $this->teacherId ?: null,
                'assigned_date' => $this->assignedDate,
                'due_date' => $this->dueDate,
            ]
        );

        $this->showModal = false;
    }

    public function delete(string $id): void
    {
        Homework::findOrFail($id)->delete();
    }

    public function render()
    {
        $homeworks = Homework::with(['schoolClass', 'section', 'subject', 'teacher'])
            ->when($this->classFilter, fn ($q) => $q->where('class_id', $this->classFilter))
            ->latest('assigned_date')
            ->paginate(15);

        return view('livewire.homework-manager', [
            'homeworks' => $homeworks,
            'classes' => SchoolClass::orderBy('display_order')->get(),
            'subjects' => Subject::orderBy('name')->get(),
            'teachers' => Teacher::where('status', 'active')->orderBy('name')->get(),
        ])->layout('components.layouts.app', ['title' => 'হোমওয়ার্ক/অ্যাসাইনমেন্ট']);
    }
}
