<?php

namespace App\Livewire;

use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\ExamResultService;
use App\Support\GradeCalculator;
use Livewire\Component;

class MeritList extends Component
{
    public string $mode = 'standard'; // standard / qawmi

    public ?string $examId = null;
    public ?string $classId = null;

    public function mount(string $mode = 'standard'): void
    {
        $this->mode = $mode;
    }

    public function getResultsProperty()
    {
        if (! $this->examId || ! $this->classId) {
            return collect();
        }

        $service = app(ExamResultService::class);
        $rows = collect($service->getEffectiveMarksForClass($this->examId, $this->classId));

        $students = Student::where('class_id', $this->classId)->where('status', 'active')->orderBy('name')->get()->keyBy('id');

        $isQawmi = $this->mode === 'qawmi';

        $results = $students->map(function ($student) use ($rows, $isQawmi) {
            $studentRows = $rows->where('student_id', $student->id);

            $obtained = 0.0;
            $full = 0.0;
            $hasAbsent = false;
            $anyFail = false;

            foreach ($studentRows as $r) {
                $full += (float) ($r->full_marks ?? 0);
                if ($r->is_absent) {
                    $hasAbsent = true;
                    continue;
                }
                $obtained += (float) ($r->marks_obtained ?? 0);
            }

            $percentage = $full > 0 ? round(($obtained / $full) * 100, 2) : 0;
            $grade = GradeCalculator::grade($percentage, $isQawmi);

            return [
                'student' => $student,
                'obtained' => $obtained,
                'full' => $full,
                'percentage' => $percentage,
                'grade' => $grade['label'],
                'gpa' => $grade['gpa'],
                'is_pass' => $grade['pass'] && ! $hasAbsent,
                'is_absent' => $hasAbsent,
            ];
        })->sortByDesc(fn ($r) => $r['is_pass'] ? $r['percentage'] : -1)->values();

        $rank = 0;
        $lastPercentage = null;

        return $results->map(function ($r, $i) use (&$rank, &$lastPercentage) {
            if ($r['is_pass']) {
                if ($lastPercentage === null || $r['percentage'] !== $lastPercentage) {
                    $rank = $i + 1;
                }
                $lastPercentage = $r['percentage'];
                $r['rank'] = $rank;
            } else {
                $r['rank'] = null;
            }

            return $r;
        });
    }

    public function render()
    {
        return view('livewire.merit-list', [
            'exams' => Exam::orderByDesc('start_date')->get(),
            'classes' => SchoolClass::orderBy('display_order')->get(),
            'results' => $this->results,
        ])->layout('components.layouts.app', [
            'title' => $this->mode === 'qawmi' ? 'কওমি গ্রেডিং' : 'GPA / Merit List',
        ]);
    }
}
