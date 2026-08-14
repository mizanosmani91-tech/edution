<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\FeeCollection;
use App\Models\RoutinePeriod;
use App\Models\Student;
use App\Models\User;
use Livewire\Component;

class StudentProfile extends Component
{
    public Student $student;

    public function mount(Student $student): void
    {
        // ⚠️ Student এ BelongsToTenant থাকায় route-model-binding এমনিতেই
        // অন্য প্রতিষ্ঠানের ছাত্রকে আটকায়, কিন্তু একই প্রতিষ্ঠানে অন্য
        // কারো সন্তান/অন্য ছাত্রের প্রোফাইল দেখা আটকানো দরকার — অভিভাবক
        // শুধু নিজের সন্তানের, ছাত্র শুধু নিজের প্রোফাইল দেখতে পারবে।
        $user = auth()->user();

        if ($user->role === 'guardian') {
            abort_unless($user->children()->where('students.id', $student->id)->exists(), 403, 'এই শিক্ষার্থীর তথ্য দেখার অনুমতি আপনার নেই।');
        } elseif ($user->role === 'student') {
            abort_unless($user->student_id === $student->id, 403, 'এই শিক্ষার্থীর তথ্য দেখার অনুমতি আপনার নেই।');
        }

        $this->student = $student;
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
        $totalPaidThisYear = $fees->filter(fn ($f) => $f->paid_at?->isCurrentYear())->sum('amount_paid');
        $monthlyFee = $fees->firstWhere('fee_type', 'monthly');

        $yearAttendance = Attendance::where('student_id', $this->student->id)
            ->whereBetween('date', [now()->startOfYear(), now()->endOfYear()])
            ->get();
        $presentDaysYear = $yearAttendance->whereIn('status', ['present', 'late'])->count();
        $absentDaysYear = $yearAttendance->where('status', 'absent')->count();
        $leaveDaysYear = $yearAttendance->where('status', 'leave')->count();
        $totalMarkedYear = $yearAttendance->count();
        $attendancePct = $totalMarkedYear > 0 ? round($presentDaysYear / $totalMarkedYear * 100, 1) : 0;

        $recentAttendance = Attendance::where('student_id', $this->student->id)->latest('date')->limit(10)->get();

        $guardians = $this->student->guardians;
        $portalUser = User::where('student_id', $this->student->id)->first();

        $studyingYears = $this->student->created_at ? (int) $this->student->created_at->diffInYears(now()) : 0;

        return view('livewire.student-profile', [
            'subjectRows' => $subjectRows,
            'fees' => $fees,
            'totalDue' => $totalDue,
            'totalPaidThisYear' => $totalPaidThisYear,
            'monthlyFee' => $monthlyFee,
            'attendancePct' => $attendancePct,
            'presentDaysYear' => $presentDaysYear,
            'absentDaysYear' => $absentDaysYear,
            'leaveDaysYear' => $leaveDaysYear,
            'recentAttendance' => $recentAttendance,
            'guardians' => $guardians,
            'portalUser' => $portalUser,
            'studyingYears' => $studyingYears,
        ])->layout('components.layouts.app', ['title' => 'শিক্ষার্থী প্রোফাইল']);
    }
}
