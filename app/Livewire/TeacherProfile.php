<?php

namespace App\Livewire;

use App\Models\LeaveRequest;
use App\Models\RoutinePeriod;
use App\Models\StaffAttendance;
use App\Models\Teacher;
use Livewire\Component;

class TeacherProfile extends Component
{
    public Teacher $teacher;

    public string $activeTab = 'overview';

    public function mount(Teacher $teacher): void
    {
        $this->teacher = $teacher;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        $periods = RoutinePeriod::with(['subject', 'schoolClass', 'section'])
            ->where('teacher_id', $this->teacher->id)
            ->get();

        $classChips = $periods
            ->groupBy(fn ($p) => trim(($p->schoolClass->name ?? '').' '.($p->section->name ?? '')))
            ->map(function ($group, $label) {
                return [
                    'label' => $label ?: '—',
                    'subjects' => $group->pluck('subject.name')->unique()->filter()->implode(', '),
                    'periods' => $group->count(),
                ];
            })
            ->values();

        $subjectCount = $periods->pluck('subject_id')->unique()->count();
        $classCount = $periods->pluck('class_id')->unique()->count();

        $grossSalary = (float) ($this->teacher->base_salary ?? 0)
            + (float) ($this->teacher->house_rent ?? 0)
            + (float) ($this->teacher->medical_allowance ?? 0);

        $recentAttendance = StaffAttendance::where('teacher_id', $this->teacher->id)->latest('date')->limit(15)->get();
        $thisMonthAttendance = StaffAttendance::where('teacher_id', $this->teacher->id)
            ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
            ->get();
        $presentDays = $thisMonthAttendance->whereIn('status', ['present', 'late'])->count();
        $leaveHistory = LeaveRequest::where('teacher_id', $this->teacher->id)->latest('date_from')->limit(10)->get();

        return view('livewire.teacher-profile', [
            'classChips' => $classChips,
            'subjectCount' => $subjectCount,
            'classCount' => $classCount,
            'periodCount' => $periods->count(),
            'grossSalary' => $grossSalary,
            'recentAttendance' => $recentAttendance,
            'presentDays' => $presentDays,
            'markedDays' => $thisMonthAttendance->count(),
            'leaveHistory' => $leaveHistory,
        ])->layout('components.layouts.app', ['title' => 'শিক্ষক প্রোফাইল']);
    }
}
