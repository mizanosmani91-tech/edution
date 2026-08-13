<?php

namespace App\Livewire;

use App\Models\Complaint;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ComplaintBox extends Component
{
    public string $tab = 'open'; // open / in_progress / resolved / all

    public bool $showModal = false;

    #[Validate('required|string')]
    public string $category = 'general';

    #[Validate('required|string|max:150')]
    public string $subject = '';

    #[Validate('required|string|min:3')]
    public string $description = '';

    public ?string $respondingId = null;
    public string $responseText = '';

    public function openModal(): void
    {
        $this->reset(['subject', 'description']);
        $this->category = 'general';
        $this->showModal = true;
    }

    public function submit(): void
    {
        $this->validate();

        Complaint::create([
            'category' => $this->category,
            'subject' => $this->subject,
            'description' => $this->description,
            'submitted_by' => auth()->id(),
            'status' => 'open',
        ]);

        $this->showModal = false;
    }

    public function openRespond(string $id): void
    {
        $this->respondingId = $id;
        $this->responseText = Complaint::find($id)->response ?? '';
    }

    public function markInProgress(string $id): void
    {
        Complaint::findOrFail($id)->update(['status' => 'in_progress']);
    }

    public function resolve(): void
    {
        $this->validate(['responseText' => 'required|string|min:2']);

        Complaint::findOrFail($this->respondingId)->update([
            'status' => 'resolved',
            'response' => $this->responseText,
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        $this->respondingId = null;
        $this->responseText = '';
    }

    public function render()
    {
        $query = Complaint::with('submittedBy')->latest();

        if ($this->tab !== 'all') {
            $query->where('status', $this->tab);
        }

        return view('livewire.complaint-box', [
            'complaints' => $query->limit(100)->get(),
            'openCount' => Complaint::where('status', 'open')->count(),
            'inProgressCount' => Complaint::where('status', 'in_progress')->count(),
            'resolvedCount' => Complaint::where('status', 'resolved')->count(),
        ])->layout('components.layouts.app', ['title' => 'অভিযোগ বাক্স']);
    }
}
