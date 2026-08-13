<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\FeeCollection;
use App\Models\RoutinePeriod;
use App\Models\Student;
use Livewire\Component;

class StudentProfile extends Component
{
    public Student $student;

    public string $activeTab = 'overview';

    public function mount(Student $student): void
    {
        $this->student = $student;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        $periods = RoutinePeriod::with(['subject', 'teacher'])
            ->where('class_id', $this->student->class_id)
            ->when($this->student->section_id, fn ($q) => $q->where('section_id', $this->student->section_id))
            ->get();

        $subjectRows = $periods
            ->groupBy('subject_id')
            ->map(fn ($group) => [
                'subject' => $group->first()->subject->name ?? '—',
                'teacher' => $group->first()->teacher->name ?? '—',
                'periods' => $group->count(),
            ])
            ->values();

        $fees = FeeCollection::where('student_id', $this->student->id)->latest('due_month')->get();
        $totalDue = $fees->whereIn('status', ['due', 'partial', 'overdue'])->sum(fn ($f) => $f->due_amount);
        $totalPaid = $fees->sum('amount_paid');

        $attendances = Attendance::where('student_id', $this->student->id)
            ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
            ->get();
        $presentDays = $attendances->whereIn('status', ['present', 'late'])->count();
        $totalMarkedDays = $attendances->count();
        $attendancePct = $totalMarkedDays > 0 ? round($presentDays / $totalMarkedDays * 100) : 0;

        $recentAttendance = Attendance::where('student_id', $this->student->id)
            ->latest('date')
            ->limit(15)
            ->get();

        $guardians = $this->student->guardians;

        return view('livewire.student-profile', [
            'subjectRows' => $subjectRows,
            'fees' => $fees,
            'totalDue' => $totalDue,
            'totalPaid' => $totalPaid,
            'attendancePct' => $attendancePct,
            'presentDays' => $presentDays,
            'totalMarkedDays' => $totalMarkedDays,
            'recentAttendance' => $recentAttendance,
            'guardians' => $guardians,
        ])->layout('components.layouts.app', ['title' => 'শিক্ষার্থী প্রোফাইল']);
    }
}
