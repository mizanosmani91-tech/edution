<?php

namespace App\Livewire;

use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Support\Concerns\GuardsPrerequisites;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ExamScheduleManager extends Component
{
    use GuardsPrerequisites;

    public bool $showExamModal = false;
    public ?string $editingExamId = null;

    #[Validate('required|string|max:100')]
    public string $name = '';

    #[Validate('required|string|max:40')]
    public string $examType = 'term';

    #[Validate('required|string|max:20')]
    public string $academicYear = '';

    #[Validate('nullable|date')]
    public string $startDate = '';

    #[Validate('nullable|date')]
    public string $endDate = '';

    public ?string $selectedExamId = null;

    public bool $showSubjectModal = false;
    public ?string $editingSubjectRowId = null;
    public string $subjectId = '';
    public string $classId = '';
    public ?string $teacherId = null;
    public string $fullMarks = '100';
    public string $passMarks = '33';
    public string $examDate = '';
    public string $startTime = '';
    public string $endTime = '';
    public string $room = '';

    public function openExamModal(): void
    {
        $this->reset(['editingExamId', 'name', 'startDate', 'endDate']);
        $this->examType = 'term';
        $this->academicYear = (string) now()->year;
        $this->showExamModal = true;
    }

    public function editExam(string $id): void
    {
        $exam = Exam::findOrFail($id);
        $this->editingExamId = $id;
        $this->name = $exam->name;
        $this->examType = $exam->exam_type;
        $this->academicYear = $exam->academic_year;
        $this->startDate = $exam->start_date?->toDateString() ?? '';
        $this->endDate = $exam->end_date?->toDateString() ?? '';
        $this->showExamModal = true;
    }

    public function saveExam(): void
    {
        $this->validate();

        Exam::updateOrCreate(
            ['id' => $this->editingExamId],
            [
                'name' => $this->name,
                'exam_type' => $this->examType,
                'academic_year' => $this->academicYear,
                'start_date' => $this->startDate ?: null,
                'end_date' => $this->endDate ?: null,
            ]
        );

        $this->showExamModal = false;
    }

    public function deleteExam(string $id): void
    {
        Exam::findOrFail($id)->delete();

        if ($this->selectedExamId === $id) {
            $this->selectedExamId = null;
        }
    }

    public function togglePublish(string $id): void
    {
        $exam = Exam::findOrFail($id);
        $exam->update(['is_published' => ! $exam->is_published]);
    }

    public function selectExam(string $id): void
    {
        $this->selectedExamId = $id;
    }

    public function openSubjectModal(): void
    {
        if (! $this->guardPrerequisite(SchoolClass::exists(), 'academic.classes', 'পরীক্ষায় বিষয় যোগ করার আগে অন্তত একটি ক্লাস যোগ করুন।')) {
            return;
        }
        if (! $this->guardPrerequisite(Subject::exists(), 'academic.subjects', 'পরীক্ষায় বিষয় যোগ করার আগে অন্তত একটি বিষয় যোগ করুন।')) {
            return;
        }

        $this->reset(['editingSubjectRowId', 'subjectId', 'classId', 'teacherId', 'examDate', 'startTime', 'endTime', 'room']);
        $this->fullMarks = '100';
        $this->passMarks = '33';
        $this->showSubjectModal = true;
    }

    public function editSubjectRow(string $id): void
    {
        $row = ExamSubject::findOrFail($id);
        $this->editingSubjectRowId = $id;
        $this->subjectId = $row->subject_id;
        $this->classId = $row->class_id;
        $this->teacherId = $row->teacher_id;
        $this->fullMarks = (string) $row->full_marks;
        $this->passMarks = (string) $row->pass_marks;
        $this->examDate = $row->exam_date?->toDateString() ?? '';
        $this->startTime = $row->start_time ?? '';
        $this->endTime = $row->end_time ?? '';
        $this->room = $row->room ?? '';
        $this->showSubjectModal = true;
    }

    public function saveSubjectRow(): void
    {
        $this->validate([
            'subjectId' => 'required|uuid',
            'classId' => 'required|uuid',
            'fullMarks' => 'required|numeric|min:1',
            'passMarks' => 'required|numeric|min:0',
        ]);

        ExamSubject::updateOrCreate(
            ['id' => $this->editingSubjectRowId],
            [
                'exam_id' => $this->selectedExamId,
                'subject_id' => $this->subjectId,
                'class_id' => $this->classId,
                'teacher_id' => $this->teacherId ?: null,
                'full_marks' => $this->fullMarks,
                'pass_marks' => $this->passMarks,
                'exam_date' => $this->examDate ?: null,
                'start_time' => $this->startTime ?: null,
                'end_time' => $this->endTime ?: null,
                'room' => $this->room ?: null,
            ]
        );

        $this->showSubjectModal = false;
    }

    public function deleteSubjectRow(string $id): void
    {
        ExamSubject::findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.exam-schedule-manager', [
            'exams' => Exam::withCount('examSubjects')->latest('start_date')->get(),
            'selectedExam' => $this->selectedExamId ? Exam::find($this->selectedExamId) : null,
            'scheduleRows' => $this->selectedExamId
                ? ExamSubject::with(['subject', 'schoolClass', 'teacher'])->where('exam_id', $this->selectedExamId)->get()
                : collect(),
            'subjects' => Subject::orderBy('name')->get(),
            'classes' => SchoolClass::orderBy('display_order')->get(),
            'teachers' => Teacher::where('status', 'active')->orderBy('name')->get(),
        ])->layout('components.layouts.app', ['title' => 'পরীক্ষার সময়সূচি']);
    }
}
