<div class="p-4 md:p-6">
    <div class="mb-4 flex flex-col gap-3 md:flex-row">
        <select wire:model.live="classId" class="rounded-lg border border-gray-300 px-3 py-2.5 text-base md:w-64">
            <option value="">ক্লাস নির্বাচন করুন</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}">{{ $class->full_label }}</option>
            @endforeach
        </select>
        @if ($sections->isNotEmpty())
            <select wire:model.live="sectionId" class="rounded-lg border border-gray-300 px-3 py-2.5 text-base md:w-48">
                <option value="">সব শাখা</option>
                @foreach ($sections as $section)
                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                @endforeach
            </select>
        @endif
    </div>

    @if ($classId)
        {{-- দিনের ট্যাব — মোবাইলে horizontal scroll --}}
        <div class="mb-4 flex gap-2 overflow-x-auto pb-1">
            @foreach ($dayLabels as $dayNum => $label)
                <button wire:click="$set('activeDay', {{ $dayNum }})"
                    class="whitespace-nowrap rounded-full px-4 py-2 text-sm font-medium
                        {{ $activeDay === $dayNum ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="space-y-2">
            @forelse ($periods as $period)
                <div wire:key="period-{{ $period->id }}"
                     class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-3">
                    <div>
                        <p class="font-medium text-gray-900">{{ $period->subject->name }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $period->teacher->name }} · {{ $period->start_time }}–{{ $period->end_time }}
                        </p>
                    </div>
                    <button wire:click="deletePeriod('{{ $period->id }}')"
                            wire:confirm="এই পিরিয়ড মুছে ফেলবেন?"
                            class="text-red-600">মুছুন</button>
                </div>
            @empty
                <p class="py-6 text-center text-gray-500">এই দিনে কোনো পিরিয়ড নেই।</p>
            @endforelse
        </div>

        <button wire:click="$set('showForm', true)"
                class="mt-4 w-full rounded-lg border-2 border-dashed border-gray-300 py-3 text-gray-500 md:w-auto md:px-8">
            + নতুন পিরিয়ড যোগ করুন
        </button>

        @if ($showForm)
            <div class="fixed inset-0 z-50 flex items-end bg-black/40 md:items-center md:justify-center">
                <div class="w-full rounded-t-2xl bg-white p-5 md:w-96 md:rounded-2xl">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">নতুন পিরিয়ড — {{ $dayLabels[$activeDay] }}</h3>

                    <select wire:model="subjectId" class="mb-3 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-base">
                        <option value="">বিষয় নির্বাচন করুন</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                    @error('subjectId') <p class="mb-2 text-sm text-red-600">{{ $message }}</p> @enderror

                    <select wire:model="teacherId" class="mb-3 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-base">
                        <option value="">শিক্ষক নির্বাচন করুন</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                    @error('teacherId') <p class="mb-2 text-sm text-red-600">{{ $message }}</p> @enderror

                    <div class="mb-4 flex gap-3">
                        <input type="time" wire:model="startTime" class="w-1/2 rounded-lg border border-gray-300 px-3 py-2.5 text-base">
                        <input type="time" wire:model="endTime" class="w-1/2 rounded-lg border border-gray-300 px-3 py-2.5 text-base">
                    </div>

                    <div class="flex gap-3">
                        <button wire:click="$set('showForm', false)"
                                class="flex-1 rounded-lg border border-gray-300 py-2.5 font-medium text-gray-700">বাতিল</button>
                        <button wire:click="addPeriod"
                                class="flex-1 rounded-lg bg-blue-600 py-2.5 font-medium text-white">যোগ করুন</button>
                    </div>
                </div>
            </div>
        @endif
    @else
        <p class="py-12 text-center text-gray-500">শুরু করতে একটা ক্লাস নির্বাচন করুন।</p>
    @endif
</div>
