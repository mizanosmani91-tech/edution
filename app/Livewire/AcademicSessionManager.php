<?php

namespace App\Livewire;

use App\Models\AcademicSession;
use Livewire\Attributes\Validate;
use Livewire\Component;

class AcademicSessionManager extends Component
{
    public bool $showModal = false;
    public ?string $editingId = null;

    #[Validate('required|string|max:100')]
    public string $name = '';

    #[Validate('required|date')]
    public string $startDate = '';

    #[Validate('required|date')]
    public string $endDate = '';

    public function openModal(): void
    {
        $this->reset(['editingId', 'name', 'startDate', 'endDate']);
        $this->showModal = true;
    }

    public function edit(string $id): void
    {
        $s = AcademicSession::findOrFail($id);
        $this->editingId = $id;
        $this->name = $s->name;
        $this->startDate = $s->start_date->toDateString();
        $this->endDate = $s->end_date->toDateString();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        AcademicSession::updateOrCreate(
            ['id' => $this->editingId],
            ['name' => $this->name, 'start_date' => $this->startDate, 'end_date' => $this->endDate]
        );

        $this->showModal = false;
    }

    public function setCurrent(string $id): void
    {
        AcademicSession::query()->update(['is_current' => false]);
        AcademicSession::where('id', $id)->update(['is_current' => true]);
    }

    public function delete(string $id): void
    {
        AcademicSession::findOrFail($id)->delete();
    }

    public function render()
    {
        return view('livewire.academic-session-manager', [
            'sessions' => AcademicSession::orderByDesc('start_date')->get(),
        ])->layout('components.layouts.app', ['title' => 'একাডেমিক সেশন']);
    }
}
