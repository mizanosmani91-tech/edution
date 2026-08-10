<?php

namespace App\Livewire;

use App\Models\ExamSubject;
use App\Models\RoutinePeriod;
use Livewire\Component;

/**
 * TeacherPortal — নিজের routine ও নিজের assign করা exam_subjects দেখায়।
 * ⚠️ কোথাও teacher_id request/param থেকে নেওয়া হয়নি — সবসময়
 * auth()->user()->teacher_id থেকে আসে, তাই URL manipulate করে অন্য
 * teacher এর ডেটা দেখার কোনো উপায় নেই।
 */
class TeacherPortal extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()->teacher_id, 403, 'এই একাউন্ট কোনো শিক্ষকের সাথে যুক্ত না।');
    }

    public function render()
    {
        $teacherId = auth()->user()->teacher_id;

        $todayRoutine = RoutinePeriod::with('subject')
            ->where('teacher_id', $teacherId)
            ->where('day_of_week', now()->dayOfWeekIso)
            ->orderBy('period_number')
            ->get();

        $examSubjects = ExamSubject::with(['exam', 'schoolClass'])
            ->where('teacher_id', $teacherId)
            ->whereHas('exam', fn ($q) => $q->where('is_published', false))
            ->get();

        return view('livewire.teacher-portal', [
            'todayRoutine' => $todayRoutine,
            'examSubjects' => $examSubjects,
        ])->layout('components.layouts.app');
    }
}
