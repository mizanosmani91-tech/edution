<?php

namespace App\Livewire;

use App\Models\StaffAttendance;
use App\Models\Teacher;
use Illuminate\Support\Carbon;
use Livewire\Component;

/**
 * StaffAttendanceTaker — শিক্ষক ও স্টাফদের দৈনিক চেক-ইন/চেক-আউট হাজিরা।
 * সকাল ৯:১৫ এর পর চেক-ইন করলে স্বয়ংক্রিয়ভাবে "দেরিতে" হিসেবে মার্ক হয়।
 */
class StaffAttendanceTaker extends Component
{
    public string $date;
    public string $search = '';

    protected string $lateCutoff = '09:15:00';

    public function mount(): void
    {
        $this->date = Carbon::today()->toDateString();
    }

    public function checkIn(string $teacherId): void
    {
        $now = Carbon::now();
        $cutoff = Carbon::parse($this->date.' '.$this->lateCutoff);

        StaffAttendance::updateOrCreate(
            ['teacher_id' => $teacherId, 'date' => $this->date],
            [
                'status' => $this->date === Carbon::today()->toDateString() && $now->gt($cutoff) ? 'late' : 'present',
                'check_in' => $this->date === Carbon::today()->toDateString() ? $now : Carbon::parse($this->date.' 09:00:00'),
                'marked_by' => auth()->id(),
            ]
        );
    }

    public function checkOut(string $teacherId): void
    {
        $record = StaffAttendance::where('teacher_id', $teacherId)->where('date', $this->date)->first();

        if (!$record) {
            return;
        }

        $record->update([
            'check_out' => $this->date === Carbon::today()->toDateString() ? Carbon::now() : Carbon::parse($this->date.' 17:00:00'),
        ]);
    }

    public function markStatus(string $teacherId, string $status): void
    {
        StaffAttendance::updateOrCreate(
            ['teacher_id' => $teacherId, 'date' => $this->date],
            ['status' => $status, 'marked_by' => auth()->id()]
        );
    }

    public function render()
    {
        $teachers = Teacher::where('status', 'active')
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->orderBy('name')
            ->get();

        $records = StaffAttendance::where('date', $this->date)
            ->get()
            ->keyBy('teacher_id');

        $presentCount = $records->whereIn('status', ['present', 'late'])->count();

        return view('livewire.staff-attendance-taker', [
            'teachers' => $teachers,
            'records' => $records,
            'presentCount' => $presentCount,
            'totalCount' => $teachers->count(),
        ])->layout('components.layouts.app', ['title' => 'স্টাফ হাজিরা']);
    }
}
