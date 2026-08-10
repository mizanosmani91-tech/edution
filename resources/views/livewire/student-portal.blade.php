<div class="p-4 md:p-6">
    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4">
        <h2 class="mb-2 font-medium text-gray-900">গত ৩০ দিনের উপস্থিতি</h2>
        <p class="text-sm text-gray-600">
            উপস্থিত: <span class="font-semibold text-green-600">{{ $attendance['present'] }}</span> ·
            অনুপস্থিত: <span class="font-semibold text-red-600">{{ $attendance['absent'] }}</span>
        </p>
    </div>

    <h2 class="mb-3 font-medium text-gray-900">পরীক্ষার ফলাফল</h2>
    <div class="mb-6 space-y-3">
        @forelse ($results as $result)
            <div class="rounded-lg border border-gray-200 bg-white p-4">
                <h3 class="mb-2 font-medium text-gray-900">{{ $result['exam'] }}</h3>
                @foreach ($result['subjects'] as $subject)
                    <div class="flex justify-between border-b border-gray-100 py-1.5 text-sm last:border-0">
                        <span class="text-gray-600">{{ $subject['subject'] }}</span>
                        <span class="{{ $subject['is_pass'] ? 'text-gray-900' : 'text-red-600' }} font-medium">
                            {{ $subject['marks'] ?? '—' }}/{{ $subject['max'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        @empty
            <p class="text-sm text-gray-500">এখনো কোনো ফলাফল প্রকাশিত হয়নি।</p>
        @endforelse
    </div>

    <h2 class="mb-3 font-medium text-gray-900">বকেয়া ফি</h2>
    <div class="space-y-2">
        @forelse ($dueFees as $fee)
            <div class="flex justify-between rounded-lg border border-gray-200 bg-white p-3 text-sm">
                <span class="text-gray-600">{{ $fee->due_month }}</span>
                <span class="font-medium text-gray-900">৳{{ number_format($fee->due_amount, 2) }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-500">কোনো বকেয়া নেই।</p>
        @endforelse
    </div>
</div>
