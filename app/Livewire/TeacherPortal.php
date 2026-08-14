<?php

namespace App\Livewire;

use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\LeaveRequest;
use App\Models\RoutinePeriod;
use App\Models\StaffAttendance;
use App\Models\Teacher;
use App\Support\RoutineWeek;
use Illuminate\Support\Carbon;
use Livewire\Component;

/**
 * TeacherPortal — শিক্ষকের নিজের ল্যান্ডিং পেজ। এখানে থাকে:
 *   - আজকের রুটিন ও পেন্ডিং মার্ক এন্ট্রি (আগে থেকেই ছিল)
 *   - নিজে চেক-ইন/চেক-আউট (geofence দিয়ে সুরক্ষিত — প্রতিষ্ঠানের বাইরে থেকে করা যাবে না)
 *   - নিজের ছুটির আবেদন (শুধু নিজের জন্য, অন্য কারো নামে না)
 *   - নিজের প্রোফাইল (ফোন নম্বর, ছবি)
 *
 * ⚠️ কোথাও teacher_id request/param থেকে নেওয়া হয়নি — সবসময়
 * auth()->user()->teacher_id থেকে আসে, তাই URL manipulate করে অন্য
 * teacher এর ডেটা দেখার/পরিবর্তনের কোনো উপায় নেই।
 */
class TeacherPortal extends Component
{
    public string $activeTab = 'overview'; // overview / leave / profile

    public string $leaveType = 'casual';
    public string $leaveDateFrom = '';
    public string $leaveDateTo = '';
    public string $leaveReason = '';
    public bool $showLeaveModal = false;

    public string $profilePhone = '';

    public string $checkInError = '';
    public string $checkInSuccess = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->teacher_id, 403, 'এই একাউন্ট কোনো শিক্ষকের সাথে যুক্ত না।');

        $this->profilePhone = Teacher::find(auth()->user()->teacher_id)?->phone ?? '';
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // ================= চেক-ইন/চেক-আউট =================

    public function checkIn(?string $lat = null, ?string $lng = null): void
    {
        $this->checkInError = '';
        $this->checkInSuccess = '';

        $teacherId = auth()->user()->teacher_id;
        $institution = auth()->user()->institution;

        if ($institution->hasGeofence()) {
            if ($lat === null || $lng === null) {
                $this->checkInError = 'অবস্থান পাওয়া যায়নি — লোকেশন পারমিশন দিন।';
                return;
            }

            if (! $institution->isWithinGeofence((float) $lat, (float) $lng)) {
                $this->checkInError = 'আপনি প্রতিষ্ঠানের নির্ধারিত এলাকার বাইরে আছেন, চেক-ইন করা যাবে না।';
                return;
            }
        }

        $today = Carbon::today()->toDateString();
        $existing = StaffAttendance::where('teacher_id', $teacherId)->where('date', $today)->first();

        if ($existing?->check_in) {
            $this->checkInError = 'আজ ইতিমধ্যে চেক-ইন করা হয়েছে।';
            return;
        }

        $now = Carbon::now();
        $cutoff = Carbon::parse($today . ' 09:15:00');

        StaffAttendance::updateOrCreate(
            ['teacher_id' => $teacherId, 'date' => $today],
            [
                'status' => $now->gt($cutoff) ? 'late' : 'present',
                'check_in' => $now,
                'marked_by' => auth()->id(),
            ]
        );

        $this->checkInSuccess = 'চেক-ইন সফল হয়েছে — ' . $now->format('h:i A');
    }

    public function checkOut(?string $lat = null, ?string $lng = null): void
    {
        $this->checkInError = '';
        $this->checkInSuccess = '';

        $teacherId = auth()->user()->teacher_id;
        $institution = auth()->user()->institution;

        if ($institution->hasGeofence()) {
            if ($lat === null || $lng === null) {
                $this->checkInError = 'অবস্থান পাওয়া যায়নি — লোকেশন পারমিশন দিন।';
                return;
            }

            if (! $institution->isWithinGeofence((float) $lat, (float) $lng)) {
                $this->checkInError = 'আপনি প্রতিষ্ঠানের নির্ধারিত এলাকার বাইরে আছেন, চেক-আউট করা যাবে না।';
                return;
            }
        }

        $today = Carbon::today()->toDateString();
        $record = StaffAttendance::where('teacher_id', $teacherId)->where('date', $today)->first();

        if (! $record || ! $record->check_in) {
            $this->checkInError = 'আগে চেক-ইন করুন।';
            return;
        }

        if ($record->check_out) {
            $this->checkInError = 'আজ ইতিমধ্যে চেক-আউট করা হয়েছে।';
            return;
        }

        $record->update(['check_out' => Carbon::now()]);
        $this->checkInSuccess = 'চেক-আউট সফল হয়েছে — ' . Carbon::now()->format('h:i A');
    }

    // ================= ছুটির আবেদন (শুধু নিজের) =================

    public function openLeaveModal(): void
    {
        $this->reset(['leaveType', 'leaveDateFrom', 'leaveDateTo', 'leaveReason']);
        $this->leaveType = 'casual';
        $this->showLeaveModal = true;
    }

    public function submitLeave(): void
    {
        $this->validate([
            'leaveType' => ['required', 'string'],
            'leaveDateFrom' => ['required', 'date'],
            'leaveDateTo' => ['required', 'date', 'after_or_equal:leaveDateFrom'],
            'leaveReason' => ['required', 'string', 'min:3'],
        ]);

        LeaveRequest::create([
            'applicant_type' => 'teacher',
            'teacher_id' => auth()->user()->teacher_id,
            'leave_type' => $this->leaveType,
            'requested_by' => auth()->id(),
            'date_from' => $this->leaveDateFrom,
            'date_to' => $this->leaveDateTo,
            'reason' => $this->leaveReason,
            'status' => 'pending',
        ]);

        $this->showLeaveModal = false;
        $this->dispatch('toast', message: 'ছুটির আবেদন জমা হয়েছে, এডমিনের অনুমোদনের অপেক্ষায় আছে।');
    }

    // ================= প্রোফাইল =================

    public function saveProfile(): void
    {
        $this->validate(['profilePhone' => ['nullable', 'string', 'max:20']]);

        Teacher::find(auth()->user()->teacher_id)?->update(['phone' => $this->profilePhone ?: null]);

        $this->dispatch('toast', message: 'প্রোফাইল আপডেট হয়েছে।');
    }

    public function render()
    {
        $teacherId = auth()->user()->teacher_id;

        $todayRoutine = RoutinePeriod::with('subject')
            ->where('teacher_id', $teacherId)
            ->where('day_of_week', RoutineWeek::todayNumber())
            ->orderBy('period_number')
            ->get();

        $examSubjects = ExamSubject::with(['exam', 'schoolClass'])
            ->where('teacher_id', $teacherId)
            ->whereHas('exam', fn ($q) => $q->where('is_published', false))
            ->get();

        $today = Carbon::today()->toDateString();
        $todayAttendance = StaffAttendance::where('teacher_id', $teacherId)->where('date', $today)->first();

        $myLeaves = LeaveRequest::where('applicant_type', 'teacher')
            ->where('teacher_id', $teacherId)
            ->latest('date_from')
            ->limit(15)
            ->get();

        $teacher = Teacher::find($teacherId);

        return view('livewire.teacher-portal', [
            'todayRoutine' => $todayRoutine,
            'examSubjects' => $examSubjects,
            'todayAttendance' => $todayAttendance,
            'institution' => auth()->user()->institution,
            'myLeaves' => $myLeaves,
            'teacher' => $teacher,
        ])->layout('components.layouts.app', ['title' => 'শিক্ষক পোর্টাল']);
    }
}
