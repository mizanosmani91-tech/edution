<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * ChatController
 *
 * Supabase Realtime নেই বলে polling-ভিত্তিক ডিজাইন — client প্রতি কয়েক
 * সেকেন্ডে `/chat/conversations/{id}/messages?after=...` কল করে নতুন
 * মেসেজ চেক করবে (long-polling না, simple interval polling — শেয়ার্ড
 * হোস্টিং এও কাজ করবে)।
 */
class ChatController extends Controller
{
    /**
     * এই ইউজারের সব conversation, unread count সহ — chat list sidebar এর জন্য
     */
    public function index()
    {
        $userId = auth()->id();

        return Conversation::whereHas('participants', fn ($q) => $q->where('user_id', $userId))
            ->with(['participants.user:id,name', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function ($conversation) use ($userId) {
                $myParticipant = $conversation->participants->firstWhere('user_id', $userId);

                $unreadCount = Message::where('conversation_id', $conversation->id)
                    ->when($myParticipant?->last_read_at, fn ($q, $lastRead) => $q->where('created_at', '>', $lastRead))
                    ->count();

                return [
                    'id' => $conversation->id,
                    'title' => $conversation->title,
                    'participants' => $conversation->participants->pluck('user.name'),
                    'last_message' => $conversation->messages->first()?->body,
                    'last_message_at' => $conversation->last_message_at,
                    'unread_count' => $unreadCount,
                ];
            });
    }

    /**
     * নতুন conversation শুরু (বা আগে থেকে থাকলে সেটাই রিটার্ন) —
     * আগের /api/portal/chat/start route এর সমতুল্য। একই দুইজনের মধ্যে
     * duplicate direct conversation তৈরি হওয়া আটকানো হয়েছে।
     */
    public function start(Request $request)
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                'uuid',
                Rule::exists('users', 'id')->where('institution_id', app('tenant.institution_id')),
            ],
        ]);

        $myId = auth()->id();
        $otherId = $validated['user_id'];

        if ($otherId === $myId) {
            abort(422, 'নিজের সাথে চ্যাট শুরু করা যাবে না।');
        }

        // আগে থেকেই direct conversation থাকলে সেটাই ব্যবহার করব, নতুন বানাব না
        $existing = Conversation::where('type', 'direct')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $myId))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $otherId))
            ->first();

        if ($existing) {
            return response()->json($existing->load('participants.user:id,name'));
        }

        $conversation = DB::transaction(function () use ($myId, $otherId) {
            $conversation = Conversation::create(['type' => 'direct']);

            ConversationParticipant::create(['conversation_id' => $conversation->id, 'user_id' => $myId]);
            ConversationParticipant::create(['conversation_id' => $conversation->id, 'user_id' => $otherId]);

            return $conversation;
        });

        return response()->json($conversation->load('participants.user:id,name'), 201);
    }

    /**
     * পোলিং endpoint — `after` দিলে শুধু নতুন মেসেজ, না দিলে সাম্প্রতিক ৫০টা
     */
    public function messages(Request $request, Conversation $conversation)
    {
        // route model binding + BelongsToTenant + participant scope (Message
        // মডেলে না, Conversation এ) — Conversation নিজেই participant-check করে
        // না, তাই এখানে ম্যানুয়ালি নিশ্চিত হচ্ছি এই ইউজার সত্যিই participant
        abort_unless(
            $conversation->participants()->where('user_id', auth()->id())->exists(),
            403,
            'আপনি এই কনভারসেশনের অংশ না।'
        );

        $validated = $request->validate([
            'after' => ['nullable', 'date'],
        ]);

        return $conversation->messages()
            ->when($validated['after'] ?? null, fn ($q, $after) => $q->where('created_at', '>', $after))
            ->when(!($validated['after'] ?? null), fn ($q) => $q->latest()->limit(50))
            ->get()
            ->sortBy('created_at')
            ->values();
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        abort_unless(
            $conversation->participants()->where('user_id', auth()->id())->exists(),
            403,
            'আপনি এই কনভারসেশনের অংশ না।'
        );

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $message = DB::transaction(function () use ($conversation, $validated) {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => auth()->id(),
                'body' => $validated['body'],
            ]);

            $conversation->update(['last_message_at' => now()]);

            return $message;
        });

        return response()->json($message->load('sender:id,name'), 201);
    }

    /**
     * এই ইউজার conversation-টা পড়েছে বলে মার্ক করা — unread badge ঠিক
     * রাখার জন্য, chat খোলার সময় client এটা কল করবে
     */
    public function markRead(Conversation $conversation)
    {
        ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', auth()->id())
            ->update(['last_read_at' => now()]);

        return response()->noContent();
    }
}
