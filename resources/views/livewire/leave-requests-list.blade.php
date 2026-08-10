<div class="p-4 md:p-6">
    <h2 class="mb-4 font-medium text-gray-900">ছুটির আবেদন — অনুমোদন বাকি</h2>

    <div class="space-y-3">
        @forelse ($pending as $leave)
            <div wire:key="leave-{{ $leave->id }}"
                 class="rounded-lg border border-gray-200 bg-white p-4">
                <p class="font-medium text-gray-900">{{ $leave->student->name }}</p>
                <p class="text-sm text-gray-600">{{ $leave->date_from->format('d M') }} – {{ $leave->date_to->format('d M, Y') }}</p>
                <p class="mt-1 text-sm text-gray-500">{{ $leave->reason }}</p>
                <div class="mt-3 flex gap-2">
                    <button wire:click="approve('{{ $leave->id }}')"
                            class="flex-1 rounded-lg bg-green-600 py-2 text-sm font-medium text-white">অনুমোদন</button>
                    <button wire:click="reject('{{ $leave->id }}')"
                            class="flex-1 rounded-lg border border-gray-300 py-2 text-sm font-medium text-gray-700">বাতিল</button>
                </div>
            </div>
        @empty
            <p class="py-8 text-center text-gray-500">কোনো পেন্ডিং আবেদন নেই।</p>
        @endforelse
    </div>
</div>
