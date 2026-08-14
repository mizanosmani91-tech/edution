<?php

namespace App\Livewire;

use App\Models\Section;
use Livewire\Component;

class SeatManagement extends Component
{
    public array $capacities = [];

    public function updateCapacity(string $sectionId, $value): void
    {
        $value = max(0, (int) $value);
        Section::where('id', $sectionId)->update(['capacity' => $value]);
    }

    public function render()
    {
        $sections = Section::with(['schoolClass', 'students' => fn ($q) => $q->where('status', 'active')])
            ->get()
            ->sortBy(fn ($s) => ($s->schoolClass->display_order ?? 0).'-'.$s->name);

        return view('livewire.seat-management', [
            'sections' => $sections,
        ])->layout('components.layouts.app', ['title' => 'আসন ব্যবস্থাপনা']);
    }
}
