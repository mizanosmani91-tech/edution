<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\FeeCollection;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use Livewire\Component;

class DashboardStats extends Component
{
    public function render()
    {
        $totalStudents = Student::where('status', 'active')->count();
        $totalTeachers = Teacher::count();

        $todayAttendance = Attendance::where('date', now()->toDateString())->get();
        $attendanceRate = $todayAttendance->count() > 0
            ? round($todayAttendance->where('status', 'present')->count() / $todayAttendance->count() * 100)
            : null;

        $monthCollection = FeeCollection::where('due_month', now()->format('Y-m'))->sum('amount_paid');
        $totalDue = FeeCollection::whereIn('status', ['due', 'partial', 'overdue'])->get()->sum('due_amount');

        // ক্লাস-ভিত্তিক ছাত্র সংখ্যা (bar chart-এর জন্য)
        $classDistribution = SchoolClass::withCount(['students' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('display_order')
            ->get()
            ->map(fn ($c) => ['label' => $c->name, 'count' => $c->students_count]);

        // ফি বকেয়া শীর্ষ তালিকা
        $topDefaulters = FeeCollection::with('student')
            ->whereIn('status', ['due', 'partial', 'overdue'])
            ->get()
            ->groupBy('student_id')
            ->map(fn ($group) => [
                'student' => $group->first()->student,
                'total_due' => $group->sum(fn ($f) => $f->due_amount),
            ])
            ->sortByDesc('total_due')
            ->take(5)
            ->values();

        return view('livewire.dashboard-stats', [
            'totalStudents' => $totalStudents,
            'totalTeachers' => $totalTeachers,
            'attendanceRate' => $attendanceRate,
            'monthCollection' => $monthCollection,
            'totalDue' => $totalDue,
            'classDistribution' => $classDistribution,
            'topDefaulters' => $topDefaulters,
        ]);
    }
}
