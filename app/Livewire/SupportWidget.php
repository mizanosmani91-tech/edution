<?php

namespace App\Livewire;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Livewire\Component;

/**
 * SupportWidget
 *
 * সব অথেন্টিকেটেড পেজের (superadmin ছাড়া) নিচে-ডানে একটা ফ্লোটিং
 * "সাহায্য দরকার?" বাটন — যেকোনো পেজ থেকে এক ক্লিকে সাপোর্ট টিকেট
 * খোলা যায়। কোন পেজ থেকে পাঠানো হয়েছে ও ব্রাউজার তথ্য অটো-অ্যাটাচ
 * হয়ে যায়, যাতে বারবার "কোন পেজে সমস্যা?" জিজ্ঞেস করতে না হয়।
 * সম্পূর্ণ থ্রেড/রিপ্লাই দেখতে হলে পূর্ণাঙ্গ /support-tickets পেজে যেতে হবে
 * (এই উইজেট শুধু দ্রুত নতুন টিকেট তোলার জন্য)।
 */
class SupportWidget extends Component
{
    public bool $open = false;
    public bool $sent = false;

    public string $subject = '';
    public string $priority = 'med';
    public string $body = '';
    public string $pageUrl = '';
    public string $browserInfo = '';

    public function openModal(): void
    {
        $this->reset(['subject', 'body', 'sent']);
        $this->priority = 'med';
        $this->open = true;
    }

    public function closeModal(): void
    {
        $this->open = false;
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
            'page_url' => $this->pageUrl ?: null,
            'browser_info' => $this->browserInfo ?: null,
        ]);

        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.support-widget');
    }
}
