<div class="p-4 md:p-6">
    {{-- পেন্ডিং পেমেন্ট — সবচেয়ে জরুরি, উপরে --}}
    @if ($pendingPayments->isNotEmpty())
        <h2 class="mb-3 font-medium text-gray-900">পেন্ডিং পেমেন্ট অনুমোদন</h2>
        <div class="mb-6 space-y-2">
            @foreach ($pendingPayments as $payment)
                <div wire:key="payment-{{ $payment->id }}"
                     class="flex flex-col gap-2 rounded-lg border border-amber-200 bg-amber-50 p-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="font-medium text-gray-900">{{ $payment->institution->name }}</p>
                        <p class="text-sm text-gray-600">
                            ৳{{ $payment->amount }} · {{ $payment->method }} · {{ $payment->for_month }}
                            · রেফ: {{ $payment->transaction_ref }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="approvePayment('{{ $payment->id }}')"
                                class="flex-1 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white md:flex-none">
                            অনুমোদন
                        </button>
                        <button wire:click="rejectPayment('{{ $payment->id }}')"
                                class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 md:flex-none">
                            বাতিল
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <h2 class="mb-3 font-medium text-gray-900">সব প্রতিষ্ঠান</h2>

    {{-- মোবাইল: কার্ড --}}
    <div class="space-y-3 md:hidden">
        @foreach ($institutions as $institution)
            <div wire:key="mobile-inst-{{ $institution->id }}"
                 class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-medium text-gray-900">{{ $institution->name }}</p>
                        <p class="text-sm text-gray-500">{{ $institution->slug }}.edution.xyz</p>
                    </div>
                    <span class="rounded-full px-2.5 py-1 text-xs font-medium
                        {{ match($institution->status) {
                            'active' => 'bg-green-50 text-green-700',
                            'suspended' => 'bg-red-50 text-red-700',
                            default => 'bg-amber-50 text-amber-700',
                        } }}">
                        {{ $institution->status }}
                    </span>
                </div>
                <p class="mt-2 text-sm text-gray-600">{{ $institution->students_count }} জন ছাত্র</p>
            </div>
        @endforeach
    </div>

    {{-- ডেস্কটপ: টেবিল --}}
    <div class="hidden overflow-x-auto rounded-lg border border-gray-200 md:block">
        <table class="min-w-full divide-y divide-gray-200 bg-white">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">প্রতিষ্ঠান</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Subdomain</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">ছাত্র</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">স্ট্যাটাস</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">ট্রায়াল শেষ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($institutions as $institution)
                    <tr wire:key="desktop-inst-{{ $institution->id }}" class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $institution->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $institution->slug }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $institution->students_count }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $institution->status }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $institution->trial_ends_at?->format('d M, Y') ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
