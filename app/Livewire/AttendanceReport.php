<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Carbon;
use Livewire\Component;

class AttendanceReport extends Component
{
    public ?string $classId = null;
    public ?string $sectionId = null;
    public string $from;
    public string $to;

    public function mount(): void
    {
        $this->from = Carbon::now()->startOfMonth()->toDateString();
        $this->to = Carbon::now()->toDateString();
    }

    public function render()
    {
        $students = collect();
        $classAvg = 0;

        if ($this->classId) {
            $students = Student::where('class_id', $this->classId)
                ->when($this->sectionId, fn ($q) => $q->where('section_id', $this->sectionId))
                ->orderBy('name')
                ->get()
                ->map(function ($student) {
                    $records = Attendance::where('student_id', $student->id)
                        ->whereBetween('date', [$this->from, $this->to])
                        ->get();

                    $total = $records->count();
                    $present = $records->whereIn('status', ['present', 'late'])->count();

                    return [
                        'student' => $student,
                        'total' => $total,
                        'present' => $present,
                        'absent' => $records->where('status', 'absent')->count(),
                        'leave' => $records->where('status', 'leave')->count(),
                        'pct' => $total > 0 ? round($present / $total * 100, 1) : 0,
                    ];
                });

            $classAvg = $students->count() > 0 ? round($students->avg('pct'), 1) : 0;
        }

        return view('livewire.attendance-report', [
            'classes' => SchoolClass::orderBy('display_order')->get(),
            'sections' => $this->classId ? Section::where('class_id', $this->classId)->get() : collect(),
            'students' => $students,
            'classAvg' => $classAvg,
        ])->layout('components.layouts.app', ['title' => 'হাজিরা রিপোর্ট']);
    }
}
