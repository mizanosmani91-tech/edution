<div class="relative">
    <button wire:click="$toggle('open')" class="relative rounded-full p-2 hover:bg-gray-100">
        <span class="text-xl">🔔</span>
        @if ($unreadCount > 0)
            <span class="absolute -right-0.5 -top-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-xs text-white">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    @if ($open)
        <div class="absolute right-0 z-50 mt-2 w-80 rounded-lg border border-gray-200 bg-white shadow-lg">
            <div class="flex items-center justify-between border-b border-gray-100 p-3">
                <p class="font-medium text-gray-900">নোটিফিকেশন</p>
                @if ($unreadCount > 0)
                    <button wire:click="markAllRead" class="text-sm text-blue-600">সব পড়া হয়েছে মার্ক করুন</button>
                @endif
            </div>
            <div class="max-h-96 overflow-y-auto">
                @forelse ($notifications as $n)
                    <a href="{{ $n->link ?? '#' }}"
                       wire:click="markAsRead('{{ $n->id }}')"
                       class="block border-b border-gray-50 p-3 {{ $n->read_at ? '' : 'bg-blue-50' }}">
                        <p class="text-sm font-medium text-gray-900">{{ $n->title }}</p>
                        @if ($n->body)
                            <p class="text-xs text-gray-500">{{ $n->body }}</p>
                        @endif
                        <p class="mt-1 text-xs text-gray-400">{{ $n->created_at->diffForHumans() }}</p>
                    </a>
                @empty
                    <p class="p-4 text-center text-sm text-gray-500">কোনো নোটিফিকেশন নেই।</p>
                @endforelse
            </div>
        </div>
    @endif
</div>
