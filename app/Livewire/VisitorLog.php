<?php

namespace App\Livewire;

use App\Models\Visitor;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * VisitorLog — রিসেপশন/গেটে ভিজিটর এন্ট্রি ও এক্সিট রেকর্ড রাখার জন্য।
 * "আজকের ভিজিটর" ডিফল্টভাবে দেখায় (সবচেয়ে কমন ব্যবহার), পুরনো তারিখও ফিল্টার
 * করে দেখা যায়।
 */
class VisitorLog extends Component
{
    use WithPagination;

    public string $dateFilter;

    public bool $showModal = false;

    #[Validate('required|string|min:2')]
    public string $name = '';

    public string $phone = '';

    #[Validate('required|string|min:2')]
    public string $purpose = '';

    public string $meetingWith = '';
    public string $idType = '';
    public string $idNumber = '';

    public function mount(): void
    {
        $this->dateFilter = now()->toDateString();
    }

    public function openModal(): void
    {
        $this->reset(['name', 'phone', 'purpose', 'meetingWith', 'idType', 'idNumber']);
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        Visitor::create([
            'name' => trim($this->name),
            'phone' => $this->phone ?: null,
            'purpose' => trim($this->purpose),
            'meeting_with' => $this->meetingWith ?: null,
            'id_type' => $this->idType ?: null,
            'id_number' => $this->idNumber ?: null,
            'check_in' => now(),
            'recorded_by' => auth()->id(),
        ]);

        $this->showModal = false;
        $this->dispatch('toast', message: 'ভিজিটর এন্ট্রি করা হয়েছে।');
    }

    public function checkOut(string $id): void
    {
        $visitor = Visitor::findOrFail($id);

        if ($visitor->check_out) {
            return;
        }

        $visitor->update(['check_out' => now()]);
        $this->dispatch('toast', message: 'চেক-আউট করা হয়েছে।');
    }

    public function render()
    {
        $visitors = Visitor::with('recordedBy')
            ->when($this->dateFilter, fn ($q) => $q->whereDate('check_in', $this->dateFilter))
            ->latest('check_in')
            ->paginate(20);

        $stillInside = Visitor::whereDate('check_in', now()->toDateString())->whereNull('check_out')->count();

        return view('livewire.visitor-log', [
            'visitors' => $visitors,
            'stillInside' => $stillInside,
        ])->layout('components.layouts.app', ['title' => 'ভিজিটর লগ']);
    }
}
