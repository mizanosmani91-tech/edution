<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\FeeCollection;
use App\Models\Student;
use App\Models\Teacher;
use Livewire\Component;

class DashboardStats extends Component
{
    public function render()
    {
        $todayAttendance = Attendance::where('date', now()->toDateString())->get();
        $attendanceRate = $todayAttendance->count() > 0
            ? round($todayAttendance->where('status', 'present')->count() / $todayAttendance->count() * 100)
            : null;

        return view('livewire.dashboard-stats', [
            'totalStudents' => Student::where('status', 'active')->count(),
            'totalTeachers' => Teacher::count(),
            'attendanceRate' => $attendanceRate,
            'monthCollection' => FeeCollection::where('due_month', now()->format('Y-m'))->sum('amount_paid'),
            'monthDue' => FeeCollection::whereIn('status', ['due', 'partial', 'overdue'])->get()->sum('due_amount'),
        ]);
    }
}
