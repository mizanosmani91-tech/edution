<?php

namespace App\Livewire;

use App\Models\AuditLog;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogViewer extends Component
{
    use WithPagination;

    public string $typeFilter = '';
    public string $eventFilter = '';
    public string $userFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    public ?string $expandedId = null;

    public function mount(): void
    {
        $this->authorizeAccess();
    }

    /** অডিট লগ শুধু অ্যাডমিন দেখতে পারে (শিক্ষক/অভিভাবক নয়)। */
    private function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['typeFilter', 'eventFilter', 'userFilter', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    public function toggle(string $id): void
    {
        $this->authorizeAccess();
        $this->expandedId = $this->expandedId === $id ? null : $id;
    }

    public function clearFilters(): void
    {
        $this->reset(['typeFilter', 'eventFilter', 'userFilter', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function render()
    {
        $this->authorizeAccess();

        // ভুল/অচেনা ফিল্টার মান এলে উপেক্ষা করা হয়
        $type = array_key_exists($this->typeFilter, AuditLog::TYPES) ? $this->typeFilter : null;
        $event = array_key_exists($this->eventFilter, AuditLog::EVENTS) ? $this->eventFilter : null;
        $dateFrom = $this->validDate($this->dateFrom);
        $dateTo = $this->validDate($this->dateTo);

        $logs = AuditLog::query()
            ->when($type, fn ($q) => $q->where('auditable_type', $type))
            ->when($event, fn ($q) => $q->where('event', $event))
            ->when($this->userFilter, fn ($q) => $q->where('user_id', $this->userFilter))
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->latest('created_at')
            ->paginate(25);

        return view('livewire.audit-log-viewer', [
            'logs' => $logs,
            'types' => AuditLog::TYPES,
            'events' => AuditLog::EVENTS,
            // যাদের নামে কখনো লগ আছে (ইউজার মুছে গেলেও snapshot নাম থাকে)
            'actors' => AuditLog::query()
                ->whereNotNull('user_id')
                ->select('user_id', 'user_name')
                ->distinct()
                ->orderBy('user_name')
                ->get(),
        ])->layout('components.layouts.app', ['title' => 'অডিট লগ']);
    }

    private function validDate(string $value): ?string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) && strtotime($value) !== false ? $value : null;
    }
}
