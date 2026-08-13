<?php

namespace App\Livewire;

use App\Models\SchoolClass;
use App\Models\Student;
use Livewire\Component;
use Livewire\WithPagination;

class AlumniDirectory extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $classId = null;

    public bool $showAddModal = false;
    public string $addSearch = '';

    public function openAddModal(): void
    {
        $this->addSearch = '';
        $this->showAddModal = true;
    }

    public function markAlumni(string $id): void
    {
        Student::where('id', $id)->update(['status' => 'alumni']);
    }

    public function restoreActive(string $id): void
    {
        Student::where('id', $id)->update(['status' => 'active']);
    }

    public function getActiveMatchesProperty()
    {
        if (! $this->addSearch) {
            return collect();
        }

        return Student::where('status', 'active')
            ->where('name', 'ilike', '%'.$this->addSearch.'%')
            ->orderBy('name')
            ->limit(15)
            ->get();
    }

    public function render()
    {
        $alumni = Student::with(['schoolClass', 'section'])
            ->where('status', 'alumni')
            ->when($this->classId, fn ($q) => $q->where('class_id', $this->classId))
            ->when($this->search, fn ($q) => $q->where('name', 'ilike', '%'.$this->search.'%'))
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('livewire.alumni-directory', [
            'alumni' => $alumni,
            'classes' => SchoolClass::orderBy('display_order')->get(),
            'totalAlumni' => Student::where('status', 'alumni')->count(),
        ])->layout('components.layouts.app', ['title' => 'Alumni ডিরেক্টরি']);
    }
}
