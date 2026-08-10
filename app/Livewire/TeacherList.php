<?php

namespace App\Livewire;

use App\Models\Teacher;
use Livewire\Component;
use Livewire\WithPagination;

class TeacherList extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $teachers = Teacher::when($this->search, fn ($q) => $q
                ->where('name', 'ilike', "%{$this->search}%")
                ->orWhere('teacher_id_no', 'ilike', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.teacher-list', ['teachers' => $teachers])->layout('components.layouts.app');
    }
}
