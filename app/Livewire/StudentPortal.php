<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\Exam;
use App\Models\FeeCollection;
use App\Services\ExamResultService;
use Livewire\Component;

/**
 * StudentPortal — ⚠️ এখানেও একই নিয়ম: auth()->user()->student_id ছাড়া
 * অন্য কোনো student_id কখনো ব্যবহার হচ্ছে না। শুধু "published" exam-এর
 * ফলাফল দেখানো হচ্ছে — unpublished exam-এর মার্ক এখনো preliminary/ভুল
 * হতে পারে, ছাত্র/অভিভাবককে দেখানো ঠিক না admin publish করার আগে।
 */
class StudentPortal extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()->student_id, 403, 'এই একাউন্ট কোনো ছাত্রের সাথে যুক্ত না।');
    }

    public function render(ExamResultService $examResults)
    {
        $studentId = auth()->user()->student_id;
        $student = auth()->user()->studentProfile;

        $attendance = Attendance::where('student_id', $studentId)
            ->where('date', '>=', now()->subDays(30))
            ->get();

        $publishedExams = Exam::where('is_published', true)->get();

        $results = [];
        foreach ($publishedExams as $exam) {
            $subjectResults = \App\Models\ExamSubject::where('exam_id', $exam->id)
                ->where('class_id', $student->class_id)
                ->with('subject')
                ->get()
                ->map(function ($es) use ($examResults, $studentId, $exam) {
                    $result = $examResults->computeStudentSubjectResult($studentId, $exam->id, $es->subject_id);
                    return [
                        'subject' => $es->subject->name,
                        'marks' => $result->final_marks,
                        'max' => $result->final_max_marks,
                        'is_pass' => $result->is_pass,
                    ];
                });

            $results[] = ['exam' => $exam->name, 'subjects' => $subjectResults];
        }

        $dueFees = FeeCollection::where('student_id', $studentId)
            ->whereIn('status', ['due', 'partial', 'overdue'])
            ->get();

        return view('livewire.student-portal', [
            'attendance' => [
                'present' => $attendance->where('status', 'present')->count(),
                'absent' => $attendance->where('status', 'absent')->count(),
            ],
            'results' => $results,
            'dueFees' => $dueFees,
        ])->layout('components.layouts.app');
    }
}
