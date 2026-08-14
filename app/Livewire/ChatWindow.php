<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class ChatWindow extends Component
{
    use WithFileUploads;

    public ?string $activeConversationId = null;
    public string $newMessage = '';
    public $attachment = null; // Livewire temp upload

    public function mount(): void
    {
        // গার্ডিয়ান পোর্টাল থেকে "মেসেজ করুন" চাপলে /chat-page?open=<id> এ
        // পাঠানো হয় — সেই কনভারসেশনটা সাথে সাথে খুলে দেওয়ার জন্য।
        $conversationId = request()->query('open');

        if ($conversationId && ConversationParticipant::where('conversation_id', $conversationId)->where('user_id', auth()->id())->exists()) {
            $this->openConversation($conversationId);
        }
    }

    public function openConversation(string $conversationId): void
    {
        $this->activeConversationId = $conversationId;

        ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', auth()->id())
            ->update(['last_read_at' => now()]);
    }

    public function send(FileUploadService $uploads): void
    {
        $body = trim($this->newMessage);

        if ($body === '' && !$this->attachment) {
            return;
        }

        if (!$this->activeConversationId) {
            return;
        }

        $conversation = Conversation::findOrFail($this->activeConversationId);

        abort_unless(
            $conversation->participants()->where('user_id', auth()->id())->exists(),
            403
        );

        $attachmentPath = null;
        $attachmentType = null;

        if ($this->attachment) {
            $this->validate(['attachment' => 'file|max:5120']); // 5MB
            $attachmentPath = $uploads->store($this->attachment, 'chat-attachments');
            $attachmentType = str_starts_with($this->attachment->getMimeType(), 'image/') ? 'image' : 'file';
        }

        DB::transaction(function () use ($conversation, $body, $attachmentPath, $attachmentType) {
            Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => auth()->id(),
                'body' => $body,
                'attachment_path' => $attachmentPath,
                'attachment_type' => $attachmentType,
            ]);
            $conversation->update(['last_message_at' => now()]);
        });

        $this->newMessage = '';
        $this->attachment = null;
    }

    // wire:poll প্রতি রেন্ডারে এমনিতেই নতুন মেসেজ আনবে (render() থেকে fresh
    // query হয়), তাই আলাদা কোনো poll() মেথড লাগছে না
    public function render()
    {
        $userId = auth()->id();

        $conversations = Conversation::whereHas('participants', fn ($q) => $q->where('user_id', $userId))
            ->with(['participants.user:id,name'])
            ->orderByDesc('last_message_at')
            ->get();

        $messages = $this->activeConversationId
            ? Message::where('conversation_id', $this->activeConversationId)
                ->with('sender:id,name')
                ->orderBy('created_at')
                ->get()
            : collect();

        return view('livewire.chat-window', [
            'conversations' => $conversations,
            'messages' => $messages,
        ])->layout('components.layouts.app');
    }
}
