<?php

namespace App\Livewire;

use App\Models\LeaveRequest;
use App\Models\RoutinePeriod;
use App\Models\StaffAttendance;
use App\Models\Teacher;
use App\Models\User;
use Livewire\Component;

class TeacherProfile extends Component
{
    public Teacher $teacher;

    public function mount(Teacher $teacher): void
    {
        $this->teacher = $teacher;
    }

    public function render()
    {
        $periods = RoutinePeriod::with(['subject', 'schoolClass', 'section'])
            ->where('teacher_id', $this->teacher->id)
            ->get();

        $classRows = $periods
            ->groupBy(fn ($p) => ($p->class_id ?? '').'|'.($p->section_id ?? '').'|'.($p->subject_id ?? ''))
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'class' => $first->schoolClass->name ?? '—',
                    'section' => $first->section->name ?? '—',
                    'subject' => $first->subject->name ?? '—',
                    'periods' => $group->count(),
                ];
            })
            ->values();

        $subjectCount = $periods->pluck('subject_id')->unique()->filter()->count();
        $classCount = $periods->pluck('class_id')->unique()->filter()->count();

        $chipList = $periods->take(10)->map(fn ($p) => trim(
            ($p->schoolClass->name ?? '').' '.($p->section->name ?? '').' — '.($p->subject->name ?? '')
        ));

        $grossSalary = (float) ($this->teacher->base_salary ?? 0)
            + (float) ($this->teacher->house_rent ?? 0)
            + (float) ($this->teacher->medical_allowance ?? 0);

        // হাজিরা
        $yearAttendance = StaffAttendance::where('teacher_id', $this->teacher->id)
            ->whereBetween('date', [now()->startOfYear(), now()->endOfYear()])
            ->get();
        $presentDaysYear = $yearAttendance->whereIn('status', ['present', 'late'])->count();
        $absentDaysYear = $yearAttendance->where('status', 'absent')->count();
        $lateDaysYear = $yearAttendance->where('status', 'late')->count();
        $totalMarkedYear = $yearAttendance->count();
        $attendancePct = $totalMarkedYear > 0 ? round($presentDaysYear / $totalMarkedYear * 100, 1) : 0;

        $monthAttendance = $yearAttendance->filter(fn ($a) => $a->date->isSameMonth(now()));
        $monthPct = $monthAttendance->count() > 0
            ? round($monthAttendance->whereIn('status', ['present', 'late'])->count() / $monthAttendance->count() * 100)
            : 0;

        $recentAttendance = StaffAttendance::where('teacher_id', $this->teacher->id)->latest('date')->limit(10)->get();

        $trend = collect(range(5, 0))->map(function ($i) {
            $month = now()->subMonths($i);
            $records = StaffAttendance::where('teacher_id', $this->teacher->id)
                ->whereBetween('date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->get();
            $pct = $records->count() > 0
                ? round($records->whereIn('status', ['present', 'late'])->count() / $records->count() * 100)
                : null;
            return ['label' => $month->translatedFormat('M'), 'pct' => $pct];
        });

        // ছুটি
        $leaveHistory = LeaveRequest::where('teacher_id', $this->teacher->id)->latest('date_from')->limit(15)->get();
        $usedLeaveDays = $leaveHistory->where('status', 'approved')
            ->filter(fn ($l) => $l->date_from->isCurrentYear())
            ->sum(fn ($l) => $l->total_days);
        $annualQuota = 18; // প্রাতিষ্ঠানিক নীতিমালা অনুযায়ী নির্দিষ্ট নয়, তাই একটা প্রচলিত ডিফল্ট
        $remainingLeave = max($annualQuota - $usedLeaveDays, 0);

        // পোর্টাল অ্যাক্সেস — এই টিচারের সাথে লিংকড ইউজার অ্যাকাউন্ট আছে কিনা
        $portalUser = User::where('teacher_id', $this->teacher->id)->first();

        // সাম্প্রতিক কার্যক্রম — attendance/leave থেকে তৈরি একটা টাইমলাইন
        $activity = collect();
        foreach ($leaveHistory->take(3) as $leave) {
            $label = match ($leave->status) {
                'approved' => $leave->reason.' ছুটির আবেদন অনুমোদিত হয়েছে',
                'rejected' => $leave->reason.' ছুটির আবেদন প্রত্যাখ্যাত হয়েছে',
                default => $leave->reason.' — ছুটির আবেদন জমা হয়েছে',
            };
            $activity->push(['text' => $label, 'date' => $leave->reviewed_at ?? $leave->created_at]);
        }
        foreach ($recentAttendance->take(3) as $att) {
            if ($att->status === 'late') {
                $activity->push(['text' => 'দেরিতে হাজিরা দিয়েছেন', 'date' => $att->created_at]);
            }
        }
        $activity = $activity->sortByDesc('date')->take(5)->values();

        return view('livewire.teacher-profile', [
            'classRows' => $classRows,
            'chipList' => $chipList,
            'subjectCount' => $subjectCount,
            'classCount' => $classCount,
            'periodCount' => $periods->count(),
            'grossSalary' => $grossSalary,
            'attendancePct' => $attendancePct,
            'monthPct' => $monthPct,
            'presentDaysYear' => $presentDaysYear,
            'absentDaysYear' => $absentDaysYear,
            'lateDaysYear' => $lateDaysYear,
            'recentAttendance' => $recentAttendance,
            'trend' => $trend,
            'leaveHistory' => $leaveHistory,
            'usedLeaveDays' => $usedLeaveDays,
            'annualQuota' => $annualQuota,
            'remainingLeave' => $remainingLeave,
            'portalUser' => $portalUser,
            'activity' => $activity,
        ])->layout('components.layouts.app', ['title' => 'শিক্ষক প্রোফাইল']);
    }
}
