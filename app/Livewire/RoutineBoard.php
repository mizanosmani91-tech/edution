<?php

namespace App\Livewire;

use App\Models\RoutinePeriod;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Support\Concerns\GuardsPrerequisites;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

/**
 * RoutineBoard — সাত দিনের একটা বড় গ্রিড মোবাইলে দেখানো কঠিন (horizontal
 * scroll + tiny tap target)। তার বদলে day-tab প্যাটার্ন: একবারে একদিন
 * দেখাবে, ট্যাব দিয়ে দিন পাল্টানো — এটাই মোবাইলে সহজ।
 */
class RoutineBoard extends Component
{
    use GuardsPrerequisites;

    public function mount(): void
    {
        if (! $this->guardPrerequisite(SchoolClass::exists(), 'academic.classes', 'রুটিন তৈরি করার আগে অন্তত একটি ক্লাস যোগ করুন।')) {
            return;
        }
        if (! $this->guardPrerequisite(Subject::exists(), 'academic.subjects', 'রুটিনে বিষয় বসানোর আগে অন্তত একটি বিষয় যোগ করুন।')) {
            return;
        }
        if (! $this->guardPrerequisite(Teacher::exists(), 'teachers.hire', 'রুটিনে শিক্ষক বসানোর আগে অন্তত একজন শিক্ষক নিয়োগ দিন।')) {
            return;
        }
    }

    public ?string $classId = null;
    public ?string $sectionId = null;
    public int $activeDay = 1; // 1=শনি ধরে নেওয়া হলো (institution কনভেনশন অনুযায়ী বদলাতে পারেন)

    public bool $showForm = false;
    public ?string $teacherId = null;
    public ?string $subjectId = null;
    public string $startTime = '09:00';
    public string $endTime = '09:40';

    public array $dayLabels = [
        1 => 'শনি', 2 => 'রবি', 3 => 'সোম', 4 => 'মঙ্গল', 5 => 'বুধ', 6 => 'বৃহঃ', 7 => 'শুক্র',
    ];

    public function addPeriod(): void
    {
        $this->validate([
            'teacherId' => 'required|uuid',
            'subjectId' => 'required|uuid',
            'startTime' => 'required',
            'endTime' => 'required|after:startTime',
        ]);

        $nextPeriodNumber = RoutinePeriod::where('class_id', $this->classId)
            ->where('day_of_week', $this->activeDay)
            ->max('period_number') + 1;

        $institution = auth()->user()->institution;

        if ($institution->blocksConsecutivePeriods()) {
            $hasAdjacent = RoutinePeriod::where('teacher_id', $this->teacherId)
                ->where('day_of_week', $this->activeDay)
                ->whereIn('period_number', [$nextPeriodNumber - 1, $nextPeriodNumber + 1])
                ->exists();

            if ($hasAdjacent) {
                throw ValidationException::withMessages([
                    'teacherId' => 'এই শিক্ষকের পরপর দুই পিরিয়ড থাকতে পারবে না।',
                ]);
            }
        }

        RoutinePeriod::create([
            'class_id' => $this->classId,
            'section_id' => $this->sectionId,
            'teacher_id' => $this->teacherId,
            'subject_id' => $this->subjectId,
            'day_of_week' => $this->activeDay,
            'period_number' => $nextPeriodNumber,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
        ]);

        $this->showForm = false;
        $this->reset(['teacherId', 'subjectId']);
    }

    public function deletePeriod(string $id): void
    {
        RoutinePeriod::findOrFail($id)->delete();
    }

    public function render()
    {
        $periods = $this->classId
            ? RoutinePeriod::with(['teacher', 'subject'])
                ->where('class_id', $this->classId)
                ->when($this->sectionId, fn ($q) => $q->where('section_id', $this->sectionId))
                ->where('day_of_week', $this->activeDay)
                ->orderBy('period_number')
                ->get()
            : collect();

        return view('livewire.routine-board', [
            'classes' => SchoolClass::orderBy('display_order')->get(),
            'sections' => $this->classId ? Section::where('class_id', $this->classId)->get() : collect(),
            'teachers' => Teacher::orderBy('name')->get(),
            'subjects' => Subject::orderBy('name')->get(),
            'periods' => $periods,
        ])->layout('components.layouts.app');
    }
}
