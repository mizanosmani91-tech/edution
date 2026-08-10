<div class="p-4 md:p-6">
    {{-- স্ট্যাটাস ফিল্টার — মোবাইলে scroll করা চিপ, ডেস্কটপে সাধারণ বাটন --}}
    <div class="mb-4 flex gap-2 overflow-x-auto pb-1">
        @foreach (['due' => 'বকেয়া', 'partial' => 'আংশিক', 'paid' => 'পরিশোধিত', 'overdue' => 'ওভারডিউ', '' => 'সব'] as $value => $label)
            <button
                wire:click="$set('statusFilter', '{{ $value }}')"
                class="whitespace-nowrap rounded-full px-4 py-2 text-sm font-medium
                    {{ $statusFilter === $value ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' }}"
            >{{ $label }}</button>
        @endforeach
    </div>

    <div class="mb-4">
        <input type="month" wire:model.live="monthFilter"
               class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
    </div>

    {{-- মোবাইল: কার্ড, প্রতিটাতে "পেমেন্ট নিন" বাটন সরাসরি --}}
    <div class="space-y-3 md:hidden">
        @forelse ($fees as $fee)
            <div wire:key="mobile-{{ $fee->id }}"
                 class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-medium text-gray-900">{{ $fee->student->name }}</p>
                        <p class="text-sm text-gray-500">{{ $fee->due_month }} · {{ $fee->fee_type }}</p>
                    </div>
                    <span class="rounded-full px-2.5 py-1 text-xs font-medium
                        {{ $fee->status === 'paid' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">
                        {{ $fee->status }}
                    </span>
                </div>
                <div class="mt-2 flex items-center justify-between">
                    <p class="text-sm text-gray-600">
                        বাকি: <span class="font-semibold text-gray-900">৳{{ number_format($fee->due_amount, 2) }}</span>
                    </p>
                    @if ($fee->status !== 'paid')
                        <button
                            wire:click="openPayModal('{{ $fee->id }}')"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white active:bg-blue-700"
                        >পেমেন্ট নিন</button>
                    @endif
                </div>
            </div>
        @empty
            <p class="py-8 text-center text-gray-500">কোনো রেকর্ড পাওয়া যায়নি।</p>
        @endforelse
    </div>

    {{-- ডেস্কটপ: টেবিল --}}
    <div class="hidden overflow-x-auto rounded-lg border border-gray-200 md:block">
        <table class="min-w-full divide-y divide-gray-200 bg-white">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">ছাত্র</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">মাস</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">বাকি</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">স্ট্যাটাস</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($fees as $fee)
                    <tr wire:key="desktop-{{ $fee->id }}" class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $fee->student->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $fee->due_month }}</td>
                        <td class="px-4 py-3 text-gray-600">৳{{ number_format($fee->due_amount, 2) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $fee->status }}</td>
                        <td class="px-4 py-3 text-right">
                            @if ($fee->status !== 'paid')
                                <button wire:click="openPayModal('{{ $fee->id }}')"
                                        class="font-medium text-blue-600 hover:text-blue-800">পেমেন্ট নিন</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">কোনো রেকর্ড পাওয়া যায়নি।</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $fees->links() }}</div>

    {{-- পেমেন্ট মোডাল — মোবাইল-ফ্রেন্ডলি (bottom-sheet স্টাইল ছোট স্ক্রিনে) --}}
    @if ($payingId)
        <div class="fixed inset-0 z-50 flex items-end bg-black/40 md:items-center md:justify-center">
            <div class="w-full rounded-t-2xl bg-white p-5 md:w-96 md:rounded-2xl">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">পেমেন্ট এন্ট্রি</h3>

                <label class="mb-1 block text-sm font-medium text-gray-700">পরিমাণ</label>
                <input type="number" step="0.01" wire:model="payAmount"
                       class="mb-3 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-base">
                @error('payAmount') <p class="mb-2 text-sm text-red-600">{{ $message }}</p> @enderror

                <label class="mb-1 block text-sm font-medium text-gray-700">পদ্ধতি</label>
                <select wire:model="payMethod" class="mb-4 w-full rounded-lg border border-gray-300 px-3 py-2.5 text-base">
                    <option value="cash">নগদ</option>
                    <option value="bkash">bKash</option>
                    <option value="nagad">Nagad</option>
                    <option value="bank_transfer">ব্যাংক ট্রান্সফার</option>
                </select>

                <div class="flex gap-3">
                    <button wire:click="$set('payingId', null)"
                            class="flex-1 rounded-lg border border-gray-300 py-2.5 font-medium text-gray-700">বাতিল</button>
                    <button wire:click="recordPayment"
                            class="flex-1 rounded-lg bg-blue-600 py-2.5 font-medium text-white">সেভ করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>
