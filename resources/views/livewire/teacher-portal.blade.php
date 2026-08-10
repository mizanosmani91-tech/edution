<div class="p-4 md:p-6">
    <h2 class="mb-3 font-medium text-gray-900">আজকের রুটিন</h2>
    <div class="mb-6 space-y-2">
        @forelse ($todayRoutine as $period)
            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-3">
                <span class="font-medium text-gray-900">{{ $period->subject->name }}</span>
                <span class="text-sm text-gray-500">{{ $period->start_time }}–{{ $period->end_time }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-500">আজ কোনো ক্লাস নেই।</p>
        @endforelse
    </div>

    <h2 class="mb-3 font-medium text-gray-900">মার্ক এন্ট্রি বাকি (unpublished exam)</h2>
    <div class="space-y-2">
        @forelse ($examSubjects as $es)
            <a href="#" class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-3">
                <div>
                    <p class="font-medium text-gray-900">{{ $es->exam->name }}</p>
                    <p class="text-sm text-gray-500">{{ $es->schoolClass->full_label }}</p>
                </div>
                <span class="text-blue-600">→</span>
            </a>
        @empty
            <p class="text-sm text-gray-500">কোনো পেন্ডিং মার্ক এন্ট্রি নেই।</p>
        @endforelse
    </div>
</div>
