<?php

namespace App\Livewire;

use App\Models\CalendarEvent;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AcademicCalendar extends Component
{
    /** দেখানো মাস, ফরম্যাট 'Y-m' */
    public string $month = '';

    public bool $showModal = false;
    public ?string $editingId = null;

    public string $title = '';
    public string $type = 'event';
    public string $startDate = '';
    public string $endDate = '';
    public bool $isOffDay = false;
    public string $description = '';

    /** @var array<int,string> */
    public array $audience = [];

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
    }

    private function canManage(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    private function monthStart(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d', $this->month . '-01')->startOfDay();
    }

    public function previousMonth(): void
    {
        $this->month = $this->monthStart()->subMonthNoOverflow()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->month = $this->monthStart()->addMonthNoOverflow()->format('Y-m');
    }

    public function today(): void
    {
        $this->month = now()->format('Y-m');
    }

    public function openModal(?string $date = null): void
    {
        abort_unless($this->canManage(), 403);

        $this->resetValidation();
        $this->reset(['editingId', 'title', 'description', 'audience', 'isOffDay']);
        $this->type = 'event';
        $this->startDate = $date ?: now()->toDateString();
        $this->endDate = $this->startDate;
        $this->showModal = true;
    }

    public function edit(string $id): void
    {
        abort_unless($this->canManage(), 403);

        $event = CalendarEvent::findOrFail($id);
        $this->resetValidation();
        $this->editingId = $event->id;
        $this->title = $event->title;
        $this->type = $event->type;
        $this->startDate = $event->start_date->toDateString();
        $this->endDate = $event->end_date->toDateString();
        $this->isOffDay = $event->is_off_day;
        $this->description = (string) $event->description;
        $this->audience = $event->audience ?? [];
        $this->showModal = true;
    }

    /** ছুটির ধরন বেছে নিলে ডিফল্টভাবে "বন্ধের দিন" টিক পড়ে। */
    public function updatedType(string $value): void
    {
        if ($value === 'holiday') {
            $this->isOffDay = true;
        }
    }

    public function save(): void
    {
        abort_unless($this->canManage(), 403);

        $this->validate([
            'title' => 'required|string|min:2|max:150',
            'type' => ['required', Rule::in(array_keys(CalendarEvent::TYPES))],
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'description' => 'nullable|string|max:2000',
            'audience' => 'array',
            'audience.*' => Rule::in(['guardian', 'teacher', 'student']),
        ], [], [
            'title' => 'শিরোনাম',
            'startDate' => 'শুরুর তারিখ',
            'endDate' => 'শেষের তারিখ',
        ]);

        $data = [
            'title' => $this->title,
            'type' => $this->type,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'is_off_day' => $this->isOffDay,
            'audience' => empty($this->audience) ? null : array_values($this->audience),
            'description' => $this->description ?: null,
        ];

        if ($this->editingId) {
            CalendarEvent::findOrFail($this->editingId)->update($data);
        } else {
            CalendarEvent::create($data + ['created_by' => auth()->id()]);
        }

        $this->showModal = false;
        $this->month = Carbon::parse($this->startDate)->format('Y-m');
    }

    public function delete(string $id): void
    {
        abort_unless($this->canManage(), 403);

        CalendarEvent::findOrFail($id)->delete();
        $this->showModal = false;
    }

    public function render()
    {
        $start = $this->monthStart();
        $end = $start->copy()->endOfMonth();

        $query = CalendarEvent::query()->overlapping($start, $end)->orderBy('start_date');

        // অ্যাডমিন ছাড়া বাকিরা শুধু নিজের রোলের (বা সকলের) ইভেন্ট দেখে
        $role = auth()->user()?->role;
        if (! $this->canManage()) {
            $query->where(function ($q) use ($role) {
                $q->whereNull('audience')->orWhereJsonContains('audience', $role);
            });
        }

        $events = $query->get();

        // দিন → ইভেন্ট ম্যাপ
        $byDay = [];
        foreach ($events as $event) {
            $from = $event->start_date->copy()->max($start);
            $to = $event->end_date->copy()->min($end);
            for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                $byDay[$d->toDateString()][] = $event;
            }
        }

        // গ্রিড: শনিবার থেকে শুরু (বাংলাদেশের সপ্তাহ)
        $gridStart = $start->copy()->startOfWeek(Carbon::SATURDAY);
        $gridEnd = $end->copy()->endOfWeek(Carbon::FRIDAY);
        $weeks = [];
        for ($d = $gridStart->copy(); $d->lte($gridEnd); $d->addDay()) {
            $weeks[(int) floor($d->diffInDays($gridStart) / 7)][] = $d->copy();
        }

        return view('livewire.academic-calendar', [
            'events' => $events,
            'byDay' => $byDay,
            'weeks' => $weeks,
            'monthStart' => $start,
            'canManage' => $this->canManage(),
            'offDayCount' => collect($byDay)->filter(fn ($list) => collect($list)->contains(fn ($e) => $e->is_off_day))->count(),
        ])->layout('components.layouts.app', ['title' => 'অ্যাকাডেমিক ক্যালেন্ডার']);
    }
}
