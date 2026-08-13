<?php

namespace App\Livewire;

use App\Models\SchoolClass;
use App\Models\Student;
use Livewire\Component;

class StudentPromotion extends Component
{
    public ?string $fromClassId = null;
    public ?string $fromSectionId = null;
    public ?string $toClassId = null;
    public ?string $toSectionId = null;

    public array $selected = [];
    public bool $selectAll = false;

    public ?string $message = null;

    public function updatedFromClassId(): void
    {
        $this->fromSectionId = null;
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatedFromSectionId(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll($value): void
    {
        $this->selected = $value ? $this->students->pluck('id')->map(fn ($id) => (string) $id)->toArray() : [];
    }

    public function getStudentsProperty()
    {
        if (! $this->fromClassId) {
            return collect();
        }

        return Student::where('class_id', $this->fromClassId)
            ->when($this->fromSectionId, fn ($q) => $q->where('section_id', $this->fromSectionId))
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function promote(): void
    {
        $this->message = null;

        if (! $this->toClassId || empty($this->selected)) {
            $this->message = 'টার্গেট ক্লাস এবং অন্তত একজন শিক্ষার্থী নির্বাচন করুন।';
            return;
        }

        Student::whereIn('id', $this->selected)->update([
            'class_id' => $this->toClassId,
            'section_id' => $this->toSectionId,
        ]);

        $count = count($this->selected);
        $this->selected = [];
        $this->selectAll = false;
        $this->message = "{$count} জন শিক্ষার্থীকে সফলভাবে প্রমোট করা হয়েছে।";
    }

    public function render()
    {
        return view('livewire.student-promotion', [
            'classes' => SchoolClass::orderBy('display_order')->get(),
            'fromSections' => $this->fromClassId ? SchoolClass::find($this->fromClassId)?->sections()->orderBy('name')->get() : collect(),
            'toSections' => $this->toClassId ? SchoolClass::find($this->toClassId)?->sections()->orderBy('name')->get() : collect(),
            'students' => $this->students,
        ])->layout('components.layouts.app', ['title' => 'শিক্ষার্থী প্রমোশন']);
    }
}
