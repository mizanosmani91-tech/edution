<div class="p-4 md:p-6">
    {{-- সার্চ বার --}}
    <div class="mb-4">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="নাম বা আইডি দিয়ে খুঁজুন..."
            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-base
                   focus:border-blue-500 focus:ring-2 focus:ring-blue-200
                   md:max-w-sm"
        >
    </div>

    {{--
        ⚠️ রেসপন্সিভ কৌশল: মোবাইলে (< md) কার্ড-স্ট্যাক, ডেস্কটপে (>= md) টেবিল।
        দুইটা আলাদা markup রাখা হয়েছে (একটাই টেবিলকে CSS দিয়ে "কার্ড বানানো"
        চেষ্টা করলে সাধারণত অগোছালো/hacky দেখায়) — Tailwind এর `md:hidden` ও
        `hidden md:table` দিয়ে সহজে টগল হয়।
    --}}

    {{-- মোবাইল: কার্ড লেআউট --}}
    <div class="space-y-3 md:hidden">
        @forelse ($students as $student)
            <div wire:key="mobile-{{ $student->id }}"
                 class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-medium text-gray-900">{{ $student->name }}</p>
                        <p class="text-sm text-gray-500">আইডি: {{ $student->student_id_no }}</p>
                    </div>
                    <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
                        {{ $student->schoolClass?->full_label ?? '—' }}
                    </span>
                </div>
                <div class="mt-2 flex items-center justify-between text-sm text-gray-600">
                    <span>শাখা: {{ $student->section?->name ?? '—' }}</span>
                    <a href="{{ route('students.show', $student) }}"
                       class="font-medium text-blue-600 active:text-blue-800">
                        বিস্তারিত →
                    </a>
                </div>
            </div>
        @empty
            <p class="py-8 text-center text-gray-500">কোনো ছাত্র পাওয়া যায়নি।</p>
        @endforelse
    </div>

    {{-- ডেস্কটপ/ট্যাবলেট: টেবিল লেআউট --}}
    <div class="hidden overflow-x-auto rounded-lg border border-gray-200 md:block">
        <table class="min-w-full divide-y divide-gray-200 bg-white">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">নাম</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">আইডি</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">ক্লাস</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">শাখা</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($students as $student)
                    <tr wire:key="desktop-{{ $student->id }}" class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $student->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $student->student_id_no }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $student->schoolClass?->full_label ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $student->section?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('students.show', $student) }}"
                               class="font-medium text-blue-600 hover:text-blue-800">বিস্তারিত</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">কোনো ছাত্র পাওয়া যায়নি।</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $students->links() }}
    </div>
</div>
