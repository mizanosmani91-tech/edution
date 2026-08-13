<?php

namespace App\Livewire;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentHealthRecord;
use Livewire\Component;

class StudentHealth extends Component
{
    public string $search = '';
    public ?string $classId = null;
    public ?string $selectedStudentId = null;

    public ?string $heightCm = null;
    public ?string $weightKg = null;
    public ?string $bloodGroup = null;
    public string $allergies = '';
    public string $chronicConditions = '';
    public string $emergencyContactName = '';
    public string $emergencyContactPhone = '';
    public ?string $lastCheckupDate = null;
    public string $notes = '';

    public ?string $savedMessage = null;

    public function selectStudent(string $id): void
    {
        $this->selectedStudentId = $id;
        $this->savedMessage = null;

        $record = StudentHealthRecord::where('student_id', $id)->first();

        $this->heightCm = $record?->height_cm;
        $this->weightKg = $record?->weight_kg;
        $this->bloodGroup = $record?->blood_group;
        $this->allergies = $record?->allergies ?? '';
        $this->chronicConditions = $record?->chronic_conditions ?? '';
        $this->emergencyContactName = $record?->emergency_contact_name ?? '';
        $this->emergencyContactPhone = $record?->emergency_contact_phone ?? '';
        $this->lastCheckupDate = $record?->last_checkup_date?->toDateString();
        $this->notes = $record?->notes ?? '';
    }

    public function getStudentsProperty()
    {
        return Student::where('status', 'active')
            ->when($this->classId, fn ($q) => $q->where('class_id', $this->classId))
            ->when($this->search, fn ($q) => $q->where(function ($q2) {
                $q2->where('name', 'ilike', '%'.$this->search.'%')
                    ->orWhere('student_id_no', 'ilike', '%'.$this->search.'%');
            }))
            ->orderBy('name')
            ->limit(30)
            ->get();
    }

    public function getSelectedProperty()
    {
        return $this->selectedStudentId ? Student::find($this->selectedStudentId) : null;
    }

    public function save(): void
    {
        if (! $this->selectedStudentId) {
            return;
        }

        $this->validate([
            'heightCm' => 'nullable|numeric|min:0|max:300',
            'weightKg' => 'nullable|numeric|min:0|max:300',
            'emergencyContactPhone' => 'nullable|string|max:20',
        ]);

        StudentHealthRecord::updateOrCreate(
            ['student_id' => $this->selectedStudentId],
            [
                'height_cm' => $this->heightCm ?: null,
                'weight_kg' => $this->weightKg ?: null,
                'blood_group' => $this->bloodGroup ?: null,
                'allergies' => $this->allergies ?: null,
                'chronic_conditions' => $this->chronicConditions ?: null,
                'emergency_contact_name' => $this->emergencyContactName ?: null,
                'emergency_contact_phone' => $this->emergencyContactPhone ?: null,
                'last_checkup_date' => $this->lastCheckupDate ?: null,
                'notes' => $this->notes ?: null,
            ]
        );

        $this->savedMessage = 'স্বাস্থ্য তথ্য সংরক্ষণ করা হয়েছে।';
    }

    public function render()
    {
        return view('livewire.student-health', [
            'classes' => SchoolClass::orderBy('display_order')->get(),
            'students' => $this->students,
            'selected' => $this->selected,
        ])->layout('components.layouts.app', ['title' => 'শিক্ষার্থী স্বাস্থ্য তথ্য']);
    }
}
