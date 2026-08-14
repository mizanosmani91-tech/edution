<?php

namespace App\Livewire;

use App\Models\Exam;
use App\Models\ExamResultWeighting;
use App\Models\SchoolClass;
use App\Models\Subject;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ResultWeightingManager extends Component
{
    public bool $showModal = false;
    public ?string $editingId = null;

    #[Validate('required|uuid')]
    public string $targetExamId = '';

    #[Validate('required|uuid')]
    public string $sourceExamId = '';

    public ?string $classId = null;
    public ?string $subjectId = null;

    #[Validate('required|string')]
    public string $contributionType = 'percentage';

    public string $groupKey = '';
    public string $convertedMaxMarks = '';
    public string $weightPercentage = '';
    public bool $requireSourcePass = false;

    public function openModal(): void
    {
        $this->reset(['editingId', 'targetExamId', 'sourceExamId', 'classId', 'subjectId', 'groupKey', 'convertedMaxMarks', 'weightPercentage', 'requireSourcePass']);
        $this->contributionType = 'percentage';
        $this->showModal = true;
    }

    public function edit(string $id): void
    {
        $w = ExamResultWeighting::findOrFail($id);
        $this->editingId = $id;
        $this->targetExamId = $w->target_exam_id;
        $this->sourceExamId = $w->source_exam_id;
        $this->classId = $w->class_id;
        $this->subjectId = $w->subject_id;
        $this->contributionType = $w->contribution_type;
        $this->groupKey = $w->group_key ?? '';
        $this->convertedMaxMarks = $w->converted_max_marks !== null ? (string) $w->converted_max_marks : '';
        $this->weightPercentage = $w->weight_percentage !== null ? (string) $w->weight_percentage : '';
        $this->requireSourcePass = $w->require_source_pass;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->targetExamId === $this->sourceExamId) {
            $this->addError('sourceExamId', 'উৎস ও লক্ষ্য পরীক্ষা একই হতে পারবে না।');

            return;
        }

        if ($this->contributionType === 'scale' && $this->convertedMaxMarks === '') {
            $this->addError('convertedMaxMarks', 'স্কেল টাইপের জন্য এই ফিল্ড আবশ্যক।');

            return;
        }

        if ($this->contributionType === 'percentage' && $this->weightPercentage === '') {
            $this->addError('weightPercentage', 'পার্সেন্টেজ টাইপের জন্য এই ফিল্ড আবশ্যক।');

            return;
        }

        ExamResultWeighting::updateOrCreate(
            ['id' => $this->editingId],
            [
                'target_exam_id' => $this->targetExamId,
                'source_exam_id' => $this->sourceExamId,
                'class_id' => $this->classId ?: null,
                'subject_id' => $this->subjectId ?: null,
                'contribution_type' => $this->contributionType,
                'group_key' => $this->groupKey ?: null,
                'converted_max_marks' => $this->contributionType === 'scale' ? $this->convertedMaxMarks : null,
                'weight_percentage' => $this->contributionType === 'percentage' ? $this->weightPercentage : null,
                'require_source_pass' => $this->requireSourcePass,
            ]
        );

        $this->showModal = false;
    }

    public function delete(string $id): void
    {
        ExamResultWeighting::findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.result-weighting-manager', [
            'weightings' => ExamResultWeighting::with(['targetExam', 'sourceExam', 'schoolClass', 'subject'])->latest()->get(),
            'exams' => Exam::orderByDesc('start_date')->get(),
            'classes' => SchoolClass::orderBy('display_order')->get(),
            'subjects' => Subject::orderBy('name')->get(),
        ])->layout('components.layouts.app', ['title' => 'Result Weighting']);
    }
}
