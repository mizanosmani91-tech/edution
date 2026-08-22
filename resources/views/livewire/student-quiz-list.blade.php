<div class="p-4 md:p-6">
    <div class="mb-6">
        <h2 class="mb-1 font-medium text-gray-900 text-lg">অনলাইন কুইজ</h2>
        <p class="text-sm text-gray-600">আপনার ক্লাসের জন্য প্রকাশিত কুইজগুলো এখানে দেখা যাবে</p>
    </div>

    <div class="space-y-3">
        @forelse ($rows as $row)
            <div class="rounded-lg border border-gray-200 bg-white p-4">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div>
                        <h3 class="font-medium text-gray-900">{{ $row['quiz']->title }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $row['quiz']->subject->name ?? '' }} · {{ $row['quiz']->duration_minutes }} মিনিট · {{ $row['quiz']->questions->count() }}টা প্রশ্ন
                            @if ($row['quiz']->ends_at)
                                · শেষ সময়: {{ $row['quiz']->ends_at->format('d M, h:i A') }}
                            @endif
                        </p>
                    </div>

                    @if ($row['status'] === 'submitted')
                        <div class="text-right">
                            <span class="inline-block rounded-full bg-green-100 text-green-700 text-xs font-medium px-3 py-1">জমা দেওয়া হয়েছে</span>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $row['attempt']->score }}/{{ $row['attempt']->total_marks }}</p>
                        </div>
                    @elseif ($row['status'] === 'in_progress')
                        <a href="{{ route('student-quizzes.take', $row['quiz']->id) }}" class="rounded-lg bg-amber-600 text-white text-sm font-medium px-4 py-2">চালিয়ে যান</a>
                    @elseif ($row['is_open'])
                        <a href="{{ route('student-quizzes.take', $row['quiz']->id) }}" class="rounded-lg bg-gray-900 text-white text-sm font-medium px-4 py-2">কুইজ শুরু করুন</a>
                    @else
                        <span class="inline-block rounded-full bg-gray-100 text-gray-500 text-xs font-medium px-3 py-1">এখন খোলা নেই</span>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500">এখনো কোনো কুইজ প্রকাশিত হয়নি।</p>
        @endforelse
    </div>
</div>
