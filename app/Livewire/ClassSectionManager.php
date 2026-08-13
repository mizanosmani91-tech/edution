<?php

namespace App\Livewire;

use App\Models\Department;
use App\Models\SchoolClass;
use App\Models\Section;
use Livewire\Component;

class ClassSectionManager extends Component
{
    public bool $showClassModal = false;
    public bool $showSectionModal = false;

    public string $className = '';
    public ?string $classDepartmentId = null;
    public int $classOrder = 1;

    public ?string $activeClassId = null;
    public string $sectionName = '';
    public int $sectionCapacity = 40;

    public function openClassModal(): void
    {
        $this->reset(['className', 'classDepartmentId', 'classOrder']);
        $this->classOrder = SchoolClass::max('display_order') + 1;
        $this->showClassModal = true;
    }

    public function saveClass(): void
    {
        $this->validate(['className' => 'required|string|max:255']);

        SchoolClass::create([
            'name' => $this->className,
            'department_id' => $this->classDepartmentId ?: null,
            'display_order' => $this->classOrder,
        ]);

        $this->showClassModal = false;
    }

    public function deleteClass(string $id): void
    {
        SchoolClass::findOrFail($id)->delete();
    }

    public function openSectionModal(string $classId): void
    {
        $this->activeClassId = $classId;
        $this->reset(['sectionName', 'sectionCapacity']);
        $this->sectionCapacity = 40;
        $this->showSectionModal = true;
    }

    public function saveSection(): void
    {
        $this->validate(['sectionName' => 'required|string|max:255']);

        Section::create([
            'class_id' => $this->activeClassId,
            'name' => $this->sectionName,
            'capacity' => $this->sectionCapacity,
        ]);

        $this->showSectionModal = false;
    }

    public function deleteSection(string $id): void
    {
        Section::findOrFail($id)->delete();
    }

    public function render()
    {
        $institution = auth()->user()->institution;

        return view('livewire.class-section-manager', [
            'classes' => SchoolClass::with(['department', 'sections' => fn ($q) => $q->withCount('students')])
                ->orderBy('display_order')
                ->get(),
            'departments' => Department::orderBy('display_order')->get(),
            'hasDepartments' => $institution->hasDepartments(),
        ])->layout('components.layouts.app', ['title' => 'ক্লাস ও সেকশন']);
    }
}
