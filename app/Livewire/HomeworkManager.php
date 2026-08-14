<?php

namespace App\Livewire;

use App\Models\Homework;
use App\Models\HomeworkCompletion;
use App\Models\SchoolClass;
use App\Models\Student;
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

    // ================= পড়া আদায়/চেক-অফ (per-student completion) =================
    public ?string $checkingHomeworkId = null;

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

        $user = auth()->user();

        // ⚠️ শিক্ষক শুধু নিজের নামেই হোমওয়ার্ক দিতে পারবেন — অন্য শিক্ষকের
        // নাম বসিয়ে দেওয়ার সুযোগ নেই।
        $teacherId = $user->role === 'teacher' ? $user->teacher_id : ($this->teacherId ?: null);

        if ($user->role === 'teacher' && $this->editingId) {
            abort_unless(Homework::where('id', $this->editingId)->where('teacher_id', $user->teacher_id)->exists(), 403);
        }

        Homework::updateOrCreate(
            ['id' => $this->editingId],
            [
                'title' => $this->title,
                'description' => $this->description ?: null,
                'class_id' => $this->classId,
                'section_id' => $this->sectionId ?: null,
                'subject_id' => $this->subjectId ?: null,
                'teacher_id' => $teacherId,
                'assigned_date' => $this->assignedDate,
                'due_date' => $this->dueDate,
            ]
        );

        $this->showModal = false;
    }

    public function delete(string $id): void
    {
        $homework = Homework::findOrFail($id);

        if (auth()->user()->role === 'teacher') {
            abort_unless($homework->teacher_id === auth()->user()->teacher_id, 403);
        }

        $homework->delete();
    }

    // ================= পড়া আদায়/চেক-অফ =================

    public function openCheckModal(string $id): void
    {
        $homework = Homework::findOrFail($id);

        if (auth()->user()->role === 'teacher') {
            abort_unless($homework->teacher_id === auth()->user()->teacher_id, 403, 'শুধু নিজের দেওয়া হোমওয়ার্কের পড়া আদায় করা যাবে।');
        }

        $this->checkingHomeworkId = $id;
    }

    public function closeCheckModal(): void
    {
        $this->checkingHomeworkId = null;
    }

    public function markCompletion(string $studentId, string $status): void
    {
        abort_unless($this->checkingHomeworkId, 400);

        $homework = Homework::findOrFail($this->checkingHomeworkId);

        if (auth()->user()->role === 'teacher') {
            abort_unless($homework->teacher_id === auth()->user()->teacher_id, 403);
        }

        HomeworkCompletion::updateOrCreate(
            ['homework_id' => $homework->id, 'student_id' => $studentId],
            ['status' => $status, 'marked_by' => auth()->id()]
        );
    }

    public function render()
    {
        $user = auth()->user();

        $homeworks = Homework::with(['schoolClass', 'section', 'subject', 'teacher'])
            ->withCount([
                'completions',
                'completions as done_count' => fn ($q) => $q->where('status', 'done'),
            ])
            // ⚠️ শিক্ষক শুধু নিজের দেওয়া হোমওয়ার্ক দেখবেন/ম্যানেজ করবেন —
            // অন্য শিক্ষকের ক্লাসের হোমওয়ার্কে হাত দিতে পারবেন না।
            ->when($user->role === 'teacher', fn ($q) => $q->where('teacher_id', $user->teacher_id))
            ->when($this->classFilter, fn ($q) => $q->where('class_id', $this->classFilter))
            ->latest('assigned_date')
            ->paginate(15);

        $checkingStudents = collect();
        $checkingCompletions = collect();
        $checkingHomework = null;

        if ($this->checkingHomeworkId) {
            $checkingHomework = Homework::find($this->checkingHomeworkId);

            if ($checkingHomework) {
                $checkingStudents = Student::where('class_id', $checkingHomework->class_id)
                    ->when($checkingHomework->section_id, fn ($q) => $q->where('section_id', $checkingHomework->section_id))
                    ->orderBy('name')
                    ->get();

                $checkingCompletions = HomeworkCompletion::where('homework_id', $checkingHomework->id)
                    ->get()
                    ->keyBy('student_id');
            }
        }

        return view('livewire.homework-manager', [
            'homeworks' => $homeworks,
            'classes' => SchoolClass::orderBy('display_order')->get(),
            'subjects' => Subject::orderBy('name')->get(),
            'teachers' => Teacher::where('status', 'active')->orderBy('name')->get(),
            'isTeacher' => $user->role === 'teacher',
            'checkingHomework' => $checkingHomework,
            'checkingStudents' => $checkingStudents,
            'checkingCompletions' => $checkingCompletions,
        ])->layout('components.layouts.app', ['title' => 'হোমওয়ার্ক/অ্যাসাইনমেন্ট']);
    }
}
