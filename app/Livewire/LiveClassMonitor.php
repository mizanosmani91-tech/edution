<?php

namespace App\Livewire;

use App\Models\RoutinePeriod;
use App\Models\StaffAttendance;
use App\Models\Teacher;
use App\Support\RoutineWeek;
use Livewire\Component;

/**
 * LiveClassMonitor — এডমিন/প্রধান শিক্ষকের জন্য "এখন কে কোথায়" ড্যাশবোর্ড।
 * সরাসরি GPS ট্র্যাকিং না — বরং রুটিন (RoutinePeriod) আর আজকের সময়ের
 * সাথে মিলিয়ে হিসাব করা হয় কোন শিক্ষক এই মুহূর্তে কোন ক্লাসে থাকার কথা,
 * আর পরের পিরিয়ডে কোথায় থাকবে। চেক-ইন/চেক-আউট ডেটার সাথে ক্রস-চেক করে
 * "রুটিনে আছে কিন্তু চেক-ইন করেননি" এর মতো সমস্যাও ধরিয়ে দেয়।
 */
class LiveClassMonitor extends Component
{
    public function render()
    {
        $todayNum = RoutineWeek::todayNumber();
        $now = now()->format('H:i:s');

        $todayPeriods = RoutinePeriod::with(['teacher', 'schoolClass', 'section', 'subject'])
            ->where('day_of_week', $todayNum)
            ->orderBy('start_time')
            ->get();

        $checkins = StaffAttendance::where('date', now()->toDateString())->get()->keyBy('teacher_id');

        $teachers = Teacher::where('status', 'active')->orderBy('name')->get();

        $rows = $teachers->map(function ($teacher) use ($todayPeriods, $now, $checkins) {
            $mine = $todayPeriods->where('teacher_id', $teacher->id)->values();

            $current = $mine->first(fn ($p) => $p->start_time <= $now && $p->end_time >= $now);
            $next = $mine->first(fn ($p) => $p->start_time > $now);
            $checkin = $checkins->get($teacher->id);

            return [
                'teacher' => $teacher,
                'current' => $current,
                'next' => $next,
                'hasScheduleToday' => $mine->isNotEmpty(),
                'checkedIn' => (bool) $checkin?->check_in,
                'checkedOut' => (bool) $checkin?->check_out,
            ];
        });

        // যেসব শিক্ষকের এখন ক্লাসে থাকার কথা অথচ চেক-ইনই করেননি — আলাদাভাবে হাইলাইট
        $missingCheckins = $rows->filter(fn ($r) => $r['current'] && ! $r['checkedIn'])->count();

        return view('livewire.live-class-monitor', [
            'rows' => $rows,
            'todayLabel' => RoutineWeek::labels()[$todayNum] ?? '—',
            'missingCheckins' => $missingCheckins,
            'inClassCount' => $rows->filter(fn ($r) => $r['current'])->count(),
        ])->layout('components.layouts.app', ['title' => 'লাইভ ক্লাস মনিটর']);
    }
}
