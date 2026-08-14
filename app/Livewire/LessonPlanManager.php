<?php

namespace App\Livewire;

use App\Models\LessonPlan;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class LessonPlanManager extends Component
{
    use WithPagination;

    public ?string $classFilter = null;

    public bool $showModal = false;
    public ?string $editingId = null;

    #[Validate('required|string|max:150')]
    public string $title = '';

    #[Validate('required|uuid')]
    public string $classId = '';

    public ?string $subjectId = null;
    public ?string $teacherId = null;

    #[Validate('required|date')]
    public string $date = '';

    public string $objectives = '';
    public string $content = '';

    public function openModal(): void
    {
        $this->reset(['editingId', 'title', 'classId', 'subjectId', 'teacherId', 'objectives', 'content']);
        $this->date = now()->toDateString();
        $this->showModal = true;
    }

    public function edit(string $id): void
    {
        $lp = LessonPlan::findOrFail($id);
        $this->editingId = $id;
        $this->title = $lp->title;
        $this->classId = $lp->class_id;
        $this->subjectId = $lp->subject_id;
        $this->teacherId = $lp->teacher_id;
        $this->date = $lp->date->toDateString();
        $this->objectives = $lp->objectives ?? '';
        $this->content = $lp->content ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        LessonPlan::updateOrCreate(
            ['id' => $this->editingId],
            [
                'title' => $this->title,
                'class_id' => $this->classId,
                'subject_id' => $this->subjectId ?: null,
                'teacher_id' => $this->teacherId ?: null,
                'date' => $this->date,
                'objectives' => $this->objectives ?: null,
                'content' => $this->content ?: null,
            ]
        );

        $this->showModal = false;
    }

    public function delete(string $id): void
    {
        LessonPlan::findOrFail($id)->delete();
    }

    public function render()
    {
        $plans = LessonPlan::with(['schoolClass', 'subject', 'teacher'])
            ->when($this->classFilter, fn ($q) => $q->where('class_id', $this->classFilter))
            ->latest('date')
            ->paginate(15);

        return view('livewire.lesson-plan-manager', [
            'plans' => $plans,
            'classes' => SchoolClass::orderBy('display_order')->get(),
            'subjects' => Subject::orderBy('name')->get(),
            'teachers' => Teacher::where('status', 'active')->orderBy('name')->get(),
        ])->layout('components.layouts.app', ['title' => 'লেসন প্ল্যান']);
    }
}
