<div class="p-4 md:p-6">
    {{-- ফিল্টার — মোবাইলে স্ট্যাক, ডেস্কটপে পাশাপাশি --}}
    <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-end">
        <div class="flex-1">
            <label class="mb-1 block text-sm font-medium text-gray-700">ক্লাস</label>
            <select wire:model.live="classId" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-base">
                <option value="">নির্বাচন করুন</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->full_label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1">
            <label class="mb-1 block text-sm font-medium text-gray-700">শাখা (ঐচ্ছিক)</label>
            <select wire:model.live="sectionId" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-base">
                <option value="">সব শাখা</option>
                @foreach ($sections as $section)
                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1">
            <label class="mb-1 block text-sm font-medium text-gray-700">তারিখ</label>
            <input type="date" wire:model.live="date"
                   class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-base">
        </div>
    </div>

    @if ($classId)
        <div class="mb-3 flex items-center justify-between">
            <button wire:click="markAllPresent"
                    class="rounded-lg bg-green-50 px-4 py-2 text-sm font-medium text-green-700 active:bg-green-100">
                সবাইকে উপস্থিত মার্ক করুন
            </button>
            <span class="text-sm text-gray-500">{{ count($marks) }}/{{ $students->count() }} মার্ক করা হয়েছে</span>
        </div>

        {{-- প্রতিটা student card — ৪টা বড় tap button (present/absent/late/leave) --}}
        <div class="space-y-2">
            @forelse ($students as $student)
                @php $current = $marks[$student->id] ?? null; @endphp
                <div wire:key="student-{{ $student->id }}"
                     class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-3">
                    <span class="font-medium text-gray-900">{{ $student->name }}</span>

                    {{-- মোবাইলে ৪টা আইকন বাটন, thumb দিয়ে সহজে চাপা যায় এমন সাইজ (min 44px) --}}
                    <div class="flex gap-1.5">
                        <button wire:click="mark('{{ $student->id }}', 'present')"
                            class="flex h-11 w-11 items-center justify-center rounded-full text-lg
                                {{ $current === 'present' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-400' }}"
                            title="উপস্থিত">✓</button>
                        <button wire:click="mark('{{ $student->id }}', 'absent')"
                            class="flex h-11 w-11 items-center justify-center rounded-full text-lg
                                {{ $current === 'absent' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-400' }}"
                            title="অনুপস্থিত">✗</button>
                        <button wire:click="mark('{{ $student->id }}', 'late')"
                            class="flex h-11 w-11 items-center justify-center rounded-full text-lg
                                {{ $current === 'late' ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-400' }}"
                            title="দেরি">⏰</button>
                        <button wire:click="mark('{{ $student->id }}', 'leave')"
                            class="flex h-11 w-11 items-center justify-center rounded-full text-lg
                                {{ $current === 'leave' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-400' }}"
                            title="ছুটি">🏠</button>
                    </div>
                </div>
            @empty
                <p class="py-8 text-center text-gray-500">এই ক্লাসে কোনো সক্রিয় ছাত্র নেই।</p>
            @endforelse
        </div>

        {{-- Sticky সেভ বাটন — মোবাইলে স্ক্রল করা লম্বা লিস্টের নিচে বার বার
             যেতে না হয় এজন্য bottom-fixed --}}
        <div class="sticky bottom-0 mt-4 -mx-4 border-t border-gray-200 bg-white/95 p-4 backdrop-blur md:static md:mx-0 md:border-0 md:bg-transparent md:p-0">
            <button wire:click="save"
                    class="w-full rounded-lg bg-blue-600 py-3 text-base font-medium text-white active:bg-blue-700 md:w-auto md:px-8">
                {{ $saved ? '✓ সেভ হয়েছে' : 'সেভ করুন' }}
            </button>
        </div>
    @else
        <p class="py-12 text-center text-gray-500">শুরু করতে একটা ক্লাস নির্বাচন করুন।</p>
    @endif
</div>
