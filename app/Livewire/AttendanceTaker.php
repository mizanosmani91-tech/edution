<?php

namespace App\Livewire;

use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Support\Carbon;
use Livewire\Component;

/**
 * AttendanceTaker — শিক্ষক মোবাইল থেকে ক্লাসে দাঁড়িয়েও ব্যবহার করবেন ধরে
 * ডিজাইন করা: বড় tap-target বাটন (present/absent/late/leave), টাইপ করা
 * লাগে না প্রায় কিছুই।
 */
class AttendanceTaker extends Component
{
    public ?string $classId = null;
    public ?string $sectionId = null;
    public string $date;

    /** @var array<string,string> student_id => status */
    public array $marks = [];

    public bool $saved = false;

    public function mount(): void
    {
        $this->date = Carbon::today()->toDateString();
    }

    public function updatedClassId(): void
    {
        $this->loadExistingMarks();
    }

    public function updatedSectionId(): void
    {
        $this->loadExistingMarks();
    }

    public function updatedDate(): void
    {
        $this->loadExistingMarks();
    }

    private function loadExistingMarks(): void
    {
        if (!$this->classId) {
            return;
        }

        $this->marks = Attendance::where('class_id', $this->classId)
            ->when($this->sectionId, fn ($q) => $q->where('section_id', $this->sectionId))
            ->where('date', $this->date)
            ->pluck('status', 'student_id')
            ->toArray();
    }

    public function mark(string $studentId, string $status): void
    {
        $this->marks[$studentId] = $status;
        $this->saved = false;
    }

    public function markAllPresent(): void
    {
        foreach ($this->students() as $student) {
            $this->marks[$student->id] = 'present';
        }
        $this->saved = false;
    }

    public function save(): void
    {
        $records = [];
        foreach ($this->marks as $studentId => $status) {
            $records[] = ['student_id' => $studentId, 'status' => $status];
        }

        if (empty($records)) {
            return;
        }

        foreach ($records as $record) {
            Attendance::updateOrCreate(
                ['student_id' => $record['student_id'], 'date' => $this->date],
                [
                    'class_id' => $this->classId,
                    'section_id' => $this->sectionId,
                    'status' => $record['status'],
                    'marked_by' => auth()->id(),
                ]
            );
        }

        $this->saved = true;
    }

    private function students()
    {
        if (!$this->classId) {
            return collect();
        }

        return Student::where('class_id', $this->classId)
            ->when($this->sectionId, fn ($q) => $q->where('section_id', $this->sectionId))
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.attendance-taker', [
            'classes' => SchoolClass::orderBy('display_order')->get(),
            'sections' => $this->classId ? Section::where('class_id', $this->classId)->get() : collect(),
            'students' => $this->students(),
        ])->layout('components.layouts.app');
    }
}
