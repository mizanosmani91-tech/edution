<?php

namespace App\Livewire;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Livewire\Component;

/**
 * TenantSupportTickets
 *
 * প্রতিষ্ঠানের নিজস্ব সাইড থেকে সুপার এডমিনকে সাপোর্ট টিকেট পাঠানোর পেজ —
 * SuperadminDashboard-এর "সাপোর্ট টিকেট" সেকশনে এগুলোই দেখা যায় ও রিপ্লাই আসে।
 */
class TenantSupportTickets extends Component
{
    public bool $showModal = false;

    public string $subject = '';
    public string $priority = 'med';
    public string $body = '';

    public ?string $activeTicketId = null;
    public string $replyBody = '';

    public function openModal(): void
    {
        $this->reset(['subject', 'body']);
        $this->priority = 'med';
        $this->showModal = true;
    }

    public function submit(): void
    {
        $this->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $ticket = SupportTicket::create([
            'created_by' => auth()->id(),
            'subject' => $this->subject,
            'priority' => $this->priority,
            'status' => 'open',
        ]);

        SupportTicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_type' => 'institution',
            'sender_name' => auth()->user()->name,
            'body' => $this->body,
        ]);

        $this->showModal = false;
        $this->activeTicketId = $ticket->id;
    }

    public function loadTicket(string $ticketId): void
    {
        $this->activeTicketId = $ticketId;
        $this->replyBody = '';
    }

    public function sendReply(): void
    {
        if (!$this->activeTicketId || trim($this->replyBody) === '') {
            return;
        }

        SupportTicketMessage::create([
            'support_ticket_id' => $this->activeTicketId,
            'sender_type' => 'institution',
            'sender_name' => auth()->user()->name,
            'body' => $this->replyBody,
        ]);

        $this->replyBody = '';
    }

    public function render()
    {
        $tickets = SupportTicket::with('messages')->latest()->get();
        $activeTicket = $this->activeTicketId ? $tickets->firstWhere('id', $this->activeTicketId) : null;

        return view('livewire.tenant-support-tickets', [
            'tickets' => $tickets,
            'activeTicket' => $activeTicket,
        ])->layout('components.layouts.app', ['title' => 'সাপোর্ট টিকেট']);
    }
}
