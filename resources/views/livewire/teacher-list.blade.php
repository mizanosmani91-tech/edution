<div class="p-4 md:p-6">
    <div class="mb-4">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="নাম বা আইডি দিয়ে খুঁজুন..."
            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-base
                   focus:border-blue-500 focus:ring-2 focus:ring-blue-200 md:max-w-sm"
        >
    </div>

    {{-- মোবাইল: কার্ড --}}
    <div class="space-y-3 md:hidden">
        @forelse ($teachers as $teacher)
            <div wire:key="mobile-{{ $teacher->id }}"
                 class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <p class="font-medium text-gray-900">{{ $teacher->name }}</p>
                <p class="text-sm text-gray-500">আইডি: {{ $teacher->teacher_id_no }}</p>
                <div class="mt-2 flex items-center justify-between text-sm">
                    <span class="text-gray-600">{{ $teacher->designation ?? '—' }}</span>
                    <a href="tel:{{ $teacher->phone }}" class="font-medium text-blue-600">
                        {{ $teacher->phone ?? '—' }}
                    </a>
                </div>
            </div>
        @empty
            <p class="py-8 text-center text-gray-500">কোনো শিক্ষক পাওয়া যায়নি।</p>
        @endforelse
    </div>

    {{-- ডেস্কটপ: টেবিল --}}
    <div class="hidden overflow-x-auto rounded-lg border border-gray-200 md:block">
        <table class="min-w-full divide-y divide-gray-200 bg-white">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">নাম</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">আইডি</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">পদবি</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">ফোন</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($teachers as $teacher)
                    <tr wire:key="desktop-{{ $teacher->id }}" class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $teacher->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $teacher->teacher_id_no }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $teacher->designation ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $teacher->phone ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">কোনো শিক্ষক পাওয়া যায়নি।</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $teachers->links() }}</div>
</div>
