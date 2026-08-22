<div class="p-4 md:p-6 max-w-2xl mx-auto"
     x-data="{ remaining: {{ $secondsRemaining ?? 0 }}, hasTimer: {{ (!$submitted && $secondsRemaining !== null) ? 'true' : 'false' }} }"
     x-init="
        if (hasTimer) {
            const tick = setInterval(() => {
                if (remaining <= 0) { clearInterval(tick); $wire.submit(); return; }
                remaining--;
            }, 1000);
        }
     "
>
    <div class="mb-5 flex items-center justify-between flex-wrap gap-2">
        <div>
            <h2 class="font-medium text-gray-900 text-lg">{{ $quiz->title }}</h2>
            <p class="text-xs text-gray-500 mt-0.5">{{ $quiz->subject->name ?? '' }} · মোট নম্বর: {{ $quiz->total_marks }}</p>
        </div>

        @if (! $submitted && $secondsRemaining !== null)
            <div class="rounded-lg bg-gray-900 text-white text-sm font-semibold px-4 py-2" x-text="
                (() => {
                    const m = Math.floor(remaining/60).toString().padStart(2,'0');
                    const s = (remaining%60).toString().padStart(2,'0');
                    return m + ':' + s;
                })()
            "></div>
        @endif
    </div>

    @if ($submitted)
        <div class="rounded-lg border border-gray-200 bg-white p-6 text-center">
            <p class="text-sm text-gray-500 mb-1">আপনার ফলাফল</p>
            <p class="text-3xl font-bold text-gray-900">{{ $attempt->score ?? 0 }}/{{ $attempt->total_marks ?? $quiz->total_marks }}</p>
            <p class="text-xs text-gray-500 mt-2">জমা দেওয়া হয়েছে: {{ $attempt->submitted_at?->format('d M Y, h:i A') }}</p>
            <a href="{{ route('student-quizzes.index') }}" class="inline-block mt-5 rounded-lg bg-gray-900 text-white text-sm font-medium px-5 py-2">কুইজ তালিকায় ফিরুন</a>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($questions as $i => $q)
                <div class="rounded-lg border border-gray-200 bg-white p-4">
                    <p class="text-sm font-medium text-gray-900 mb-3">{{ $i + 1 }}. {{ $q->questionBankItem->question_text }} <span class="text-xs text-gray-400">({{ $q->marks }} নম্বর)</span></p>
                    <div class="space-y-2">
                        @foreach (($q->questionBankItem->options ?? []) as $opt)
                            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                <input type="radio" wire:model="answers.{{ $q->id }}" value="{{ $opt }}" name="q-{{ $q->id }}">
                                {{ $opt }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <button type="button" wire:click="submit" wire:confirm="উত্তর জমা দেওয়ার পর আর পরিবর্তন করা যাবে না। জমা দিতে চান?" class="w-full rounded-lg bg-gray-900 text-white text-sm font-semibold px-5 py-3">
                জমা দিন
            </button>
        </div>
    @endif
</div>
