<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\MonthlyHonor;
use App\Models\PerformanceReview;
use App\Models\StaffAttendance;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

/**
 * MonthlyHonorsService — "মাসের সেরা" শিক্ষার্থী ও শিক্ষক/স্টাফ স্বয়ংক্রিয়ভাবে
 * হিসাব করে monthly_honors টেবিলে সংরক্ষণ করে (প্রতিষ্ঠান-ভিত্তিক, সবার জন্য একই)।
 *
 * স্কোর সূত্র:
 *   শিক্ষার্থী = হাজিরা% × ০.৫ + সাম্প্রতিক প্রকাশিত পরীক্ষার গড় নম্বর% × ০.৫
 *   শিক্ষক/স্টাফ = হাজিরা% × ০.৫ + পারফরম্যান্স রিভিউ গড় (৫ এর মধ্যে, ১০০ স্কেলে) × ০.৫
 *
 * চলতি মাসের honor আগে থেকে গণনা করা থাকলে পুনরায় গণনা করে না (dashboard লোড
 * হওয়ার সময় প্রতিবার ভারী কোয়েরি এড়াতে) — মাস পাল্টালে বা রেকর্ড না থাকলেই চলে।
 */
class MonthlyHonorsService
{
    public function ensureComputedForCurrentMonth(string $institutionId): void
    {
        $month = now()->format('Y-m');

        if (!MonthlyHonor::where('institution_id', $institutionId)->where('month', $month)->exists()) {
            $this->computeStudent($institutionId, $month);
            $this->computeTeacher($institutionId, $month);
        }
    }

    private function computeStudent(string $institutionId, string $month): void
    {
        $start = now()->startOfMonth();
        $end = now();

        $attendance = Attendance::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('student_id, COUNT(*) as total, SUM(CASE WHEN status = \'present\' THEN 1 ELSE 0 END) as present_count')
            ->groupBy('student_id')
            ->havingRaw('COUNT(*) >= 3')
            ->get()
            ->keyBy('student_id');

        if ($attendance->isEmpty()) {
            return;
        }

        $latestExamId = Exam::where('is_published', true)->orderByDesc('end_date')->value('id');
        $avgMarks = collect();
        if ($latestExamId) {
            $avgMarks = ExamMark::whereHas('examSubject', fn ($q) => $q->where('exam_id', $latestExamId))
                ->where('is_absent', false)
                ->whereNotNull('marks_obtained')
                ->selectRaw('student_id, AVG(marks_obtained) as avg_marks')
                ->groupBy('student_id')
                ->get()
                ->keyBy('student_id');
        }

        $best = null;
        $bestScore = -1;
        $bestMetrics = [];

        foreach ($attendance as $studentId => $row) {
            $attPct = $row->total > 0 ? ($row->present_count / $row->total) * 100 : 0;
            $marksPct = $avgMarks->get($studentId)?->avg_marks ?? null;
            $score = $marksPct !== null ? ($attPct * 0.5 + (float) $marksPct * 0.5) : $attPct;

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $studentId;
                $bestMetrics = ['attendance_pct' => round($attPct, 1), 'avg_marks' => $marksPct !== null ? round((float) $marksPct, 1) : null];
            }
        }

        if ($best) {
            MonthlyHonor::updateOrCreate(
                ['institution_id' => $institutionId, 'category' => 'student', 'month' => $month],
                ['student_id' => $best, 'score' => round($bestScore, 2), 'metrics' => $bestMetrics]
            );
        }
    }

    private function computeTeacher(string $institutionId, string $month): void
    {
        $start = now()->startOfMonth();
        $end = now();

        $attendance = StaffAttendance::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('teacher_id, COUNT(*) as total, SUM(CASE WHEN status = \'present\' THEN 1 ELSE 0 END) as present_count')
            ->groupBy('teacher_id')
            ->havingRaw('COUNT(*) >= 3')
            ->get()
            ->keyBy('teacher_id');

        if ($attendance->isEmpty()) {
            return;
        }

        $reviews = PerformanceReview::selectRaw('teacher_id, AVG((teaching_quality + punctuality + discipline + cooperation) / 4.0) as avg_score')
            ->groupBy('teacher_id')
            ->get()
            ->keyBy('teacher_id');

        $best = null;
        $bestScore = -1;
        $bestMetrics = [];

        foreach ($attendance as $teacherId => $row) {
            $attPct = $row->total > 0 ? ($row->present_count / $row->total) * 100 : 0;
            $reviewAvg = $reviews->get($teacherId)?->avg_score ?? null; // ১-৫ স্কেল
            $reviewPct = $reviewAvg !== null ? ((float) $reviewAvg / 5) * 100 : null;
            $score = $reviewPct !== null ? ($attPct * 0.5 + $reviewPct * 0.5) : $attPct;

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $teacherId;
                $bestMetrics = ['attendance_pct' => round($attPct, 1), 'review_score' => $reviewAvg !== null ? round((float) $reviewAvg, 1) : null];
            }
        }

        if ($best) {
            MonthlyHonor::updateOrCreate(
                ['institution_id' => $institutionId, 'category' => 'teacher', 'month' => $month],
                ['teacher_id' => $best, 'score' => round($bestScore, 2), 'metrics' => $bestMetrics]
            );
        }
    }
}
