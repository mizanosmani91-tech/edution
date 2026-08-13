<?php

namespace App\Livewire;

use App\Models\Section;
use App\Models\SchoolClass;
use App\Models\Student;
use Livewire\Component;

class IdCardGenerator extends Component
{
    public string $search = '';
    public ?string $classId = null;
    public ?string $sectionId = null;
    public ?string $selectedStudentId = null;

    public function selectStudent(string $id): void
    {
        $this->selectedStudentId = $id;
    }

    public function render()
    {
        $students = Student::with(['schoolClass', 'section', 'guardians'])
            ->where('status', 'active')
            ->when($this->classId, fn ($q) => $q->where('class_id', $this->classId))
            ->when($this->sectionId, fn ($q) => $q->where('section_id', $this->sectionId))
            ->when($this->search, fn ($q) => $q->where(function ($qq) {
                $qq->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('student_id_no', 'like', '%'.$this->search.'%');
            }))
            ->orderBy('name')
            ->limit(100)
            ->get();

        if (!$this->selectedStudentId || !$students->firstWhere('id', $this->selectedStudentId)) {
            $this->selectedStudentId = $students->first()?->id;
        }

        $selected = $students->firstWhere('id', $this->selectedStudentId);

        return view('livewire.id-card-generator', [
            'students' => $students,
            'selected' => $selected,
            'classes' => SchoolClass::orderBy('display_order')->get(),
            'sections' => $this->classId ? Section::where('class_id', $this->classId)->get() : collect(),
        ])->layout('components.layouts.app', ['title' => 'আইডি কার্ড জেনারেশন']);
    }
}
