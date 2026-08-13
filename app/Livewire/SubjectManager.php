<?php

namespace App\Livewire;

use App\Models\Subject;
use Livewire\Component;

class SubjectManager extends Component
{
    public bool $showModal = false;
    public ?string $editingId = null;

    public string $name = '';
    public string $code = '';
    public string $syllabus_note = '';

    public function openCreateModal(): void
    {
        $this->reset(['editingId', 'name', 'code', 'syllabus_note']);
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        $subject = Subject::findOrFail($id);
        $this->editingId = $subject->id;
        $this->name = $subject->name;
        $this->code = $subject->code ?? '';
        $this->syllabus_note = $subject->syllabus_note ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate(['name' => 'required|string|max:255']);

        Subject::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $this->name,
                'code' => $this->code ?: null,
                'syllabus_note' => $this->syllabus_note ?: null,
            ]
        );

        $this->showModal = false;
    }

    public function delete(string $id): void
    {
        Subject::findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.subject-manager', [
            'subjects' => Subject::orderBy('name')->get(),
        ])->layout('components.layouts.app', ['title' => 'বিষয় ও সিলেবাস']);
    }
}
