<?php

namespace App\Livewire;

use App\Models\HifzProgress;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Support\Carbon;
use Livewire\Component;

/**
 * HifzProgressManager — মাদ্রাসার হিফজ শিক্ষক প্রতিদিন প্রতিটা ছাত্রের সবক
 * (আজকের নতুন পড়া), সবকি (সাম্প্রতিক রিভিশন), মঞ্জিল (পুরাতন রিভিশন) এখান
 * থেকে এক সাথে এন্ট্রি করেন — AttendanceTaker এর মতোই class+date নির্বাচন
 * করে বাল্ক এন্ট্রি প্যাটার্ন ব্যবহার করা হয়েছে।
 */
class HifzProgressManager extends Component
{
    public ?string $classId = null;
    public ?string $sectionId = null;
    public string $date;

    /** @var array<string,array> student_id => ['sabak_para'=>,'sabak_range'=>,'sabak_quality'=>,'sabqi_range'=>,'sabqi_quality'=>,'manzil_range'=>,'manzil_quality'=>,'remarks'=>] */
    public array $rows = [];

    public bool $saved = false;

    public function mount(): void
    {
        $this->date = Carbon::today()->toDateString();
    }

    public function updatedClassId(): void
    {
        $this->loadExisting();
    }

    public function updatedSectionId(): void
    {
        $this->loadExisting();
    }

    public function updatedDate(): void
    {
        $this->loadExisting();
    }

    private function loadExisting(): void
    {
        if (! $this->classId) {
            return;
        }

        $students = $this->students();

        $existing = HifzProgress::whereIn('student_id', $students->pluck('id'))
            ->where('date', $this->date)
            ->get()
            ->keyBy('student_id');

        $this->rows = [];
        foreach ($students as $student) {
            $row = $existing->get($student->id);
            $this->rows[$student->id] = [
                'sabak_para' => $row->sabak_para ?? '',
                'sabak_range' => $row->sabak_range ?? '',
                'sabak_quality' => $row->sabak_quality ?? '',
                'sabqi_range' => $row->sabqi_range ?? '',
                'sabqi_quality' => $row->sabqi_quality ?? '',
                'manzil_range' => $row->manzil_range ?? '',
                'manzil_quality' => $row->manzil_quality ?? '',
                'remarks' => $row->remarks ?? '',
            ];
        }

        $this->saved = false;
    }

    public function save(): void
    {
        foreach ($this->rows as $studentId => $data) {
            // পুরোপুরি খালি সারি এড়িয়ে যাওয়া — শুধু যাদের কিছু একটা এন্ট্রি করা হয়েছে তাদেরই সেভ করা হয়
            $hasData = collect($data)->filter(fn ($v) => trim((string) $v) !== '')->isNotEmpty();
            if (! $hasData) {
                continue;
            }

            HifzProgress::updateOrCreate(
                ['student_id' => $studentId, 'date' => $this->date],
                array_merge($data, ['recorded_by' => auth()->id()])
            );
        }

        $this->saved = true;
        $this->dispatch('toast', message: 'হিফজ অগ্রগতি সংরক্ষণ করা হয়েছে।');
    }

    private function students()
    {
        if (! $this->classId) {
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
        return view('livewire.hifz-progress-manager', [
            'classes' => SchoolClass::orderBy('display_order')->get(),
            'sections' => $this->classId ? Section::where('class_id', $this->classId)->get() : collect(),
            'students' => $this->students(),
        ])->layout('components.layouts.app', ['title' => 'হিফজ অগ্রগতি']);
    }
}
