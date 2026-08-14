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
        // ⚠️ প্রতিষ্ঠানে অন্তত ১টা বিভাগ থাকলে নতুন ক্লাসে বিভাগ নির্বাচন
        // বাধ্যতামূলক — নাহলে কিছু ক্লাস বিভাগসহ, কিছু বিভাগ ছাড়া তৈরি হয়ে
        // অসামঞ্জস্যপূর্ণ ডেটা তৈরি হতো (মাদরাসায় সাধারণ/হিফয বিভাগ থাকলে
        // প্রতিটা ক্লাস কোনো না কোনো বিভাগের হওয়া উচিত)।
        $this->validate([
            'className' => 'required|string|max:255',
            'classDepartmentId' => Department::exists() ? 'required' : 'nullable',
        ], [
            'classDepartmentId.required' => 'বিভাগ নির্বাচন করুন (এই প্রতিষ্ঠানে বিভাগ চালু আছে)।',
        ]);

        SchoolClass::create([
            'name' => $this->className,
            'department_id' => $this->classDepartmentId ?: null,
            'display_order' => $this->classOrder,
        ]);

        $this->showClassModal = false;
    }

    /**
     * পুরোনো ক্লাস (বিভাগ চালু হওয়ার আগে তৈরি হওয়া, department_id null) —
     * এগুলোতে দ্রুত বিভাগ বসিয়ে দেওয়ার জন্য ইনলাইন কুইক-অ্যাকশন।
     */
    public function quickAssignDepartment(string $classId, string $departmentId): void
    {
        SchoolClass::findOrFail($classId)->update(['department_id' => $departmentId ?: null]);
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
