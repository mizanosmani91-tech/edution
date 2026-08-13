<?php

namespace App\Livewire;

use App\Models\Notice;
use Livewire\Attributes\Validate;
use Livewire\Component;

class NoticeBoard extends Component
{
    public bool $showModal = false;

    #[Validate('required|string|min:3|max:150')]
    public string $title = '';

    #[Validate('required|string|min:3')]
    public string $body = '';

    #[Validate('required|string')]
    public string $category = 'general';

    /** @var array<int,string> */
    public array $audience = [];

    public bool $isPinned = false;
    public bool $isUrgent = false;
    public string $publishAt = '';
    public string $expiresAt = '';

    public function openModal(): void
    {
        $this->reset(['title', 'body', 'audience', 'isPinned', 'isUrgent', 'expiresAt']);
        $this->category = 'general';
        $this->publishAt = now()->format('Y-m-d\TH:i');
        $this->showModal = true;
    }

    public function submit(): void
    {
        $this->validate();

        Notice::create([
            'title' => $this->title,
            'body' => $this->body,
            'category' => $this->isUrgent ? 'urgent' : $this->category,
            'audience' => empty($this->audience) ? null : $this->audience,
            'is_pinned' => $this->isPinned,
            'is_urgent' => $this->isUrgent,
            'publish_at' => $this->publishAt ?: now(),
            'expires_at' => $this->expiresAt ?: null,
            'created_by' => auth()->id(),
        ]);

        $this->showModal = false;
    }

    public function delete(string $id): void
    {
        Notice::findOrFail($id)->delete();
    }

    public function togglePin(string $id): void
    {
        $notice = Notice::findOrFail($id);
        $notice->update(['is_pinned' => !$notice->is_pinned]);
    }

    public function render()
    {
        $pinned = Notice::published()->where('is_pinned', true)->latest('publish_at')->get();
        $recent = Notice::published()->where('is_pinned', false)->latest('publish_at')->limit(30)->get();

        return view('livewire.notice-board', [
            'pinned' => $pinned,
            'recent' => $recent,
            'publishedThisMonth' => Notice::whereBetween('publish_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'urgentCount' => Notice::published()->where('is_urgent', true)->count(),
            'totalViews' => Notice::sum('views'),
        ])->layout('components.layouts.app', ['title' => 'নোটিশ বোর্ড']);
    }
}
