<?php

namespace App\Livewire;

use App\Models\Department;
use Livewire\Component;

class DepartmentManager extends Component
{
    public bool $showModal = false;
    public ?string $editingId = null;

    public string $name = '';
    public string $name_bn = '';
    public int $display_order = 1;

    public function openCreateModal(): void
    {
        $this->reset(['editingId', 'name', 'name_bn', 'display_order']);
        $this->display_order = Department::max('display_order') + 1;
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $dept = Department::findOrFail($id);
        $this->editingId = $dept->id;
        $this->name = $dept->name;
        $this->name_bn = $dept->name_bn ?? '';
        $this->display_order = $dept->display_order;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
        ]);

        Department::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $this->name,
                'name_bn' => $this->name_bn ?: null,
                'display_order' => $this->display_order,
            ]
        );

        $this->showModal = false;
    }

    public function delete(string $id): void
    {
        Department::findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.department-manager', [
            'departments' => Department::withCount('classes')->orderBy('display_order')->get(),
            'hasDepartments' => auth()->user()->institution->hasDepartments(),
        ])->layout('components.layouts.app', ['title' => 'বিভাগ']);
    }
}
