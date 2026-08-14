<?php

namespace App\Livewire;

use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\ExamSubject;
use App\Models\Student;
use Livewire\Component;

class MarksEntry extends Component
{
    public ?string $examId = null;
    public ?string $examSubjectId = null;

    public array $marks = [];
    public array $absent = [];

    public ?string $savedMessage = null;

    public function mount(): void
    {
        // ⚠️ teacher role হলে শুধু নিজের assign করা subject-ই দেখতে/এন্ট্রি
        // করতে পারবে — admin/superadmin-এর জন্য উন্মুক্ত।
        if (auth()->user()->role === 'teacher') {
            abort_unless(auth()->user()->teacher_id, 403, 'এই একাউন্ট কোনো শিক্ষকের সাথে যুক্ত না।');
        }
    }

    protected function assertSubjectAccess(ExamSubject $examSubject): void
    {
        $user = auth()->user();

        if ($user->role === 'teacher' && $examSubject->teacher_id !== $user->teacher_id) {
            abort(403, 'আপনি এই বিষয়ের জন্য নিযুক্ত না।');
        }
    }

    public function updatedExamId(): void
    {
        $this->examSubjectId = null;
        $this->marks = [];
        $this->absent = [];
    }

    public function updatedExamSubjectId(): void
    {
        $this->savedMessage = null;
        $this->loadMarks();
    }

    protected function loadMarks(): void
    {
        $this->marks = [];
        $this->absent = [];

        if (! $this->examSubjectId) {
            return;
        }

        $examSubject = ExamSubject::find($this->examSubjectId);
        if (! $examSubject) {
            return;
        }

        $this->assertSubjectAccess($examSubject);

        $students = Student::where('class_id', $examSubject->class_id)->where('status', 'active')->get();
        $existing = ExamMark::where('exam_subject_id', $this->examSubjectId)->get()->keyBy('student_id');

        foreach ($students as $s) {
            $row = $existing->get($s->id);
            $this->marks[$s->id] = $row?->marks_obtained !== null ? (string) $row->marks_obtained : '';
            $this->absent[$s->id] = $row?->is_absent ?? false;
        }
    }

    public function getExamSubjectProperty()
    {
        return $this->examSubjectId ? ExamSubject::with(['subject', 'schoolClass'])->find($this->examSubjectId) : null;
    }

    public function getStudentsProperty()
    {
        if (! $this->examSubject) {
            return collect();
        }

        return Student::where('class_id', $this->examSubject->class_id)->where('status', 'active')->orderBy('name')->get();
    }

    public function save(): void
    {
        if (! $this->examSubjectId) {
            return;
        }

        $examSubject = ExamSubject::findOrFail($this->examSubjectId);
        $this->assertSubjectAccess($examSubject);

        foreach ($this->students as $student) {
            $isAbsent = (bool) ($this->absent[$student->id] ?? false);
            $marksValue = $this->marks[$student->id] ?? '';

            ExamMark::updateOrCreate(
                ['exam_subject_id' => $this->examSubjectId, 'student_id' => $student->id],
                [
                    'marks_obtained' => $isAbsent ? null : ($marksValue !== '' ? min((float) $marksValue, (float) $examSubject->full_marks) : null),
                    'is_absent' => $isAbsent,
                    'entered_by' => auth()->id(),
                ]
            );
        }

        $this->savedMessage = 'মার্কস সংরক্ষণ করা হয়েছে।';
    }

    public function render()
    {
        return view('livewire.marks-entry', [
            'exams' => Exam::orderByDesc('start_date')->get(),
            'examSubjects' => $this->examId
                ? ExamSubject::with('subject', 'schoolClass')
                    ->where('exam_id', $this->examId)
                    ->when(auth()->user()->role === 'teacher', fn ($q) => $q->where('teacher_id', auth()->user()->teacher_id))
                    ->get()
                : collect(),
            'students' => $this->students,
        ])->layout('components.layouts.app', ['title' => 'মার্কস এন্ট্রি']);
    }
}
