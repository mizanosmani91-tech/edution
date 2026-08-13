<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\StaffAttendance;
use App\Models\Student;
use App\Models\Teacher;
use Livewire\Attributes\Validate;
use Livewire\Component;

class LeaveRequestsList extends Component
{
    public string $tab = 'pending'; // pending / all

    public bool $showModal = false;

    #[Validate('required|in:student,teacher')]
    public string $applicantType = 'student';

    public ?string $studentId = null;
    public ?string $teacherId = null;

    #[Validate('required|string')]
    public string $leaveType = 'casual';

    #[Validate('required|date')]
    public string $dateFrom = '';

    #[Validate('required|date|after_or_equal:dateFrom')]
    public string $dateTo = '';

    #[Validate('required|string|min:3')]
    public string $reason = '';

    public function openModal(): void
    {
        $this->reset(['studentId', 'teacherId', 'leaveType', 'dateFrom', 'dateTo', 'reason']);
        $this->applicantType = 'student';
        $this->leaveType = 'casual';
        $this->showModal = true;
    }

    public function submit(): void
    {
        $this->validate();

        if ($this->applicantType === 'student') {
            $this->validate(['studentId' => 'required|exists:students,id']);
        } else {
            $this->validate(['teacherId' => 'required|exists:teachers,id']);
        }

        LeaveRequest::create([
            'applicant_type' => $this->applicantType,
            'student_id' => $this->applicantType === 'student' ? $this->studentId : null,
            'teacher_id' => $this->applicantType === 'teacher' ? $this->teacherId : null,
            'leave_type' => $this->leaveType,
            'requested_by' => auth()->id(),
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'reason' => $this->reason,
            'status' => 'pending',
        ]);

        $this->showModal = false;
    }

    public function approve(string $id): void
    {
        $leave = LeaveRequest::findOrFail($id);

        $leave->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $period = \Carbon\CarbonPeriod::create($leave->date_from, $leave->date_to);

        if ($leave->applicant_type === 'teacher') {
            foreach ($period as $date) {
                StaffAttendance::updateOrCreate(
                    ['teacher_id' => $leave->teacher_id, 'date' => $date->toDateString()],
                    ['status' => 'leave', 'marked_by' => auth()->id()]
                );
            }
        } else {
            foreach ($period as $date) {
                Attendance::updateOrCreate(
                    ['student_id' => $leave->student_id, 'date' => $date->toDateString()],
                    [
                        'class_id' => $leave->student->class_id,
                        'section_id' => $leave->student->section_id,
                        'status' => 'leave',
                        'marked_by' => auth()->id(),
                    ]
                );
            }
        }
    }

    public function reject(string $id): void
    {
        LeaveRequest::findOrFail($id)->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
    }

    public function render()
    {
        $query = LeaveRequest::with(['student', 'teacher'])->latest('date_from');

        $leaves = $this->tab === 'pending'
            ? $query->where('status', 'pending')->get()
            : $query->limit(100)->get();

        return view('livewire.leave-requests-list', [
            'leaves' => $leaves,
            'pendingCount' => LeaveRequest::where('status', 'pending')->count(),
            'approvedThisMonth' => LeaveRequest::where('status', 'approved')
                ->whereBetween('reviewed_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
            'students' => Student::orderBy('name')->limit(300)->get(),
            'teachers' => Teacher::where('status', 'active')->orderBy('name')->get(),
        ])->layout('components.layouts.app', ['title' => 'ছুটির আবেদন']);
    }
}
