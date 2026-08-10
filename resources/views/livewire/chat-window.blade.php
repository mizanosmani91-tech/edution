<div wire:poll.5s class="flex h-[calc(100vh-4rem)] overflow-hidden rounded-lg border border-gray-200 bg-white">
    {{--
        মোবাইল প্যাটার্ন: এক স্ক্রিনে দুইটা প্যানেল রাখার জায়গা নেই, তাই
        conversation না খোলা থাকলে লিস্ট দেখাবে, খোলা থাকলে থ্রেড — `md:flex`
        দিয়ে ডেস্কটপে দুটোই পাশাপাশি সবসময় দেখাবে।
    --}}

    {{-- কনভারসেশন লিস্ট প্যানেল --}}
    <div class="w-full border-r border-gray-200 md:block md:w-80
                {{ $activeConversationId ? 'hidden md:block' : 'block' }}">
        <div class="border-b border-gray-200 p-4">
            <h2 class="font-semibold text-gray-900">মেসেজ</h2>
        </div>
        <div class="overflow-y-auto">
            @forelse ($conversations as $conversation)
                @php
                    $other = $conversation->participants->firstWhere('user_id', '!=', auth()->id());
                @endphp
                <button
                    wire:click="openConversation('{{ $conversation->id }}')"
                    class="w-full border-b border-gray-100 p-4 text-left active:bg-gray-50
                        {{ $activeConversationId === $conversation->id ? 'bg-blue-50' : '' }}"
                >
                    <p class="font-medium text-gray-900">
                        {{ $conversation->title ?? $other?->user?->name ?? 'কথোপকথন' }}
                    </p>
                    @if ($conversation->last_message_at)
                        <p class="text-xs text-gray-500">{{ $conversation->last_message_at->diffForHumans() }}</p>
                    @endif
                </button>
            @empty
                <p class="p-4 text-center text-sm text-gray-500">কোনো কথোপকথন নেই।</p>
            @endforelse
        </div>
    </div>

    {{-- থ্রেড প্যানেল --}}
    <div class="flex flex-1 flex-col {{ $activeConversationId ? 'flex' : 'hidden md:flex' }}">
        @if ($activeConversationId)
            {{-- মোবাইলে "ফিরে যান" বাটন — যেহেতু single-pane --}}
            <div class="flex items-center gap-2 border-b border-gray-200 p-3 md:hidden">
                <button wire:click="$set('activeConversationId', null)" class="text-blue-600">← ফিরে যান</button>
            </div>

            <div class="flex-1 space-y-3 overflow-y-auto p-4">
                @foreach ($messages as $message)
                    @php $isMine = $message->sender_id === auth()->id(); @endphp
                    <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[75%] rounded-2xl px-4 py-2 text-sm
                            {{ $isMine ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-900' }}">
                            @unless ($isMine)
                                <p class="mb-0.5 text-xs font-medium opacity-70">{{ $message->sender->name }}</p>
                            @endunless

                            @if ($message->attachment_type === 'image')
                                <img src="{{ $message->attachment_url }}" class="mb-1 max-h-48 rounded-lg">
                            @elseif ($message->attachment_type === 'file')
                                <a href="{{ $message->attachment_url }}" target="_blank"
                                   class="mb-1 flex items-center gap-1 underline">📎 ফাইল দেখুন</a>
                            @endif

                            @if ($message->body)
                                <p>{{ $message->body }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($attachment)
                <div class="border-t border-gray-100 p-2">
                    <div class="flex items-center gap-2 rounded-lg bg-gray-50 p-2 text-sm">
                        <span>📎 {{ $attachment->getClientOriginalName() }}</span>
                        <button wire:click="$set('attachment', null)" class="text-red-600">✕</button>
                    </div>
                </div>
            @endif

            <div class="flex items-center gap-2 border-t border-gray-200 p-3">
                <label class="cursor-pointer text-xl">
                    📎
                    <input type="file" wire:model="attachment" class="hidden">
                </label>
                <input
                    type="text"
                    wire:model="newMessage"
                    wire:keydown.enter="send"
                    placeholder="মেসেজ লিখুন..."
                    class="flex-1 rounded-full border border-gray-300 px-4 py-2.5 text-base"
                >
                <button wire:click="send"
                        class="flex h-11 w-11 items-center justify-center rounded-full bg-blue-600 text-white">
                    →
                </button>
            </div>
        @else
            <div class="flex flex-1 items-center justify-center text-gray-400">
                একটা কথোপকথন নির্বাচন করুন
            </div>
        @endif
    </div>
</div>
