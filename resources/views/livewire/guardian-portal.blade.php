<div class="p-4 md:p-6">
    {{-- Multi-child selector — মোবাইলে horizontal scroll tab --}}
    @if ($children->count() > 1)
        <div class="mb-4 flex gap-2 overflow-x-auto pb-1">
            @foreach ($children as $child)
                <button wire:click="selectChild('{{ $child->id }}')"
                    class="whitespace-nowrap rounded-full px-4 py-2 text-sm font-medium
                        {{ $activeChildId === $child->id ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' }}">
                    {{ $child->name }}
                </button>
            @endforeach
        </div>
    @endif

    @if ($children->isEmpty())
        <p class="py-12 text-center text-gray-500">আপনার সাথে কোনো ছাত্র যুক্ত নেই।</p>
    @elseif ($attendanceSummary)
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            {{-- উপস্থিতি সামারি --}}
            <div class="rounded-lg border border-gray-200 bg-white p-4">
                <h3 class="mb-3 font-medium text-gray-900">গত ৩০ দিনের উপস্থিতি</h3>
                <div class="flex items-end gap-4">
                    <div>
                        <p class="text-2xl font-bold text-green-600">{{ $attendanceSummary['present'] }}</p>
                        <p class="text-xs text-gray-500">উপস্থিত</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-red-600">{{ $attendanceSummary['absent'] }}</p>
                        <p class="text-xs text-gray-500">অনুপস্থিত</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-400">{{ $attendanceSummary['total'] }}</p>
                        <p class="text-xs text-gray-500">মোট দিন</p>
                    </div>
                </div>
            </div>

            {{-- বকেয়া ফি সামারি --}}
            <div class="rounded-lg border border-gray-200 bg-white p-4">
                <h3 class="mb-3 font-medium text-gray-900">বকেয়া ফি</h3>
                @forelse ($feeSummary as $fee)
                    <div class="flex justify-between border-b border-gray-100 py-2 text-sm last:border-0">
                        <span class="text-gray-600">{{ $fee['month'] }}</span>
                        <span class="font-medium text-gray-900">৳{{ number_format($fee['due'], 2) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">কোনো বকেয়া নেই।</p>
                @endforelse
            </div>
        </div>
    @endif

    {{-- ছুটির আবেদন --}}
    @if ($activeChildId)
        <div class="mt-4">
            <button wire:click="$toggle('showLeaveForm')"
                    class="w-full rounded-lg border-2 border-dashed border-gray-300 py-3 text-gray-500 md:w-auto md:px-8">
                + ছুটির আবেদন করুন
            </button>

            @if ($showLeaveForm)
                <div class="mt-3 rounded-lg border border-gray-200 bg-white p-4">
                    <div class="mb-3 flex gap-3">
                        <div class="flex-1">
                            <label class="mb-1 block text-sm text-gray-700">শুরু</label>
                            <input type="date" wire:model="leaveFrom" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                        </div>
                        <div class="flex-1">
                            <label class="mb-1 block text-sm text-gray-700">শেষ</label>
                            <input type="date" wire:model="leaveTo" class="w-full rounded-lg border border-gray-300 px-3 py-2">
                        </div>
                    </div>
                    <textarea wire:model="leaveReason" placeholder="কারণ লিখুন..."
                              class="mb-3 w-full rounded-lg border border-gray-300 px-3 py-2" rows="3"></textarea>
                    @error('leaveReason') <p class="mb-2 text-sm text-red-600">{{ $message }}</p> @enderror

                    <button wire:click="submitLeaveRequest"
                            class="w-full rounded-lg bg-blue-600 py-2.5 font-medium text-white">জমা দিন</button>
                </div>
            @endif

            @if ($leaveRequests->isNotEmpty())
                <div class="mt-3 space-y-2">
                    @foreach ($leaveRequests as $lr)
                        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-3 text-sm">
                            <span class="text-gray-600">{{ $lr->date_from->format('d M') }} – {{ $lr->date_to->format('d M') }}</span>
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium
                                {{ match($lr->status) { 'approved' => 'bg-green-50 text-green-700', 'rejected' => 'bg-red-50 text-red-700', default => 'bg-amber-50 text-amber-700' } }}">
                                {{ $lr->status }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>
