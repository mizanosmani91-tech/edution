<?php

namespace App\Livewire;

use App\Models\FeeCollection;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use Livewire\Component;
use Livewire\WithPagination;

class StudentList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $classFilter = '';
    public string $sectionFilter = '';
    public string $statusFilter = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingClassFilter(): void { $this->resetPage(); $this->sectionFilter = ''; }
    public function updatingSectionFilter(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->reset(['search', 'classFilter', 'sectionFilter', 'statusFilter']);
    }

    public function render()
    {
        $studentsWithDue = FeeCollection::whereIn('status', ['due', 'partial', 'overdue'])
            ->pluck('student_id')
            ->unique();

        $students = Student::with(['schoolClass', 'section', 'guardians'])
            ->when($this->search, fn ($q) => $q
                ->where('name', 'ilike', "%{$this->search}%")
                ->orWhere('student_id_no', 'ilike', "%{$this->search}%"))
            ->when($this->classFilter, fn ($q) => $q->where('class_id', $this->classFilter))
            ->when($this->sectionFilter, fn ($q) => $q->where('section_id', $this->sectionFilter))
            ->when($this->statusFilter === 'due', fn ($q) => $q->whereIn('id', $studentsWithDue))
            ->when($this->statusFilter === 'active', fn ($q) => $q->where('status', 'active'))
            ->when($this->statusFilter === 'inactive', fn ($q) => $q->where('status', '!=', 'active'))
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.student-list', [
            'students' => $students,
            'classes' => SchoolClass::orderBy('display_order')->get(),
            'sections' => $this->classFilter ? Section::where('class_id', $this->classFilter)->get() : collect(),
            'totalStudents' => Student::count(),
            'activeStudents' => Student::where('status', 'active')->count(),
            'inactiveStudents' => Student::where('status', '!=', 'active')->count(),
            'dueStudents' => $studentsWithDue->count(),
        ])->layout('components.layouts.app', ['title' => 'শিক্ষার্থী তালিকা']);
    }
}
