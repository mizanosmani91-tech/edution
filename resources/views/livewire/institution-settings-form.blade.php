<div class="mx-auto max-w-lg p-4 md:p-6">
    <h2 class="mb-6 text-lg font-semibold text-gray-900">প্রতিষ্ঠান সেটিংস</h2>

    <div class="space-y-4">
        {{-- টগল সুইচ — মোবাইলে বড় tap area (পুরো row-ই ক্লিকযোগ্য) --}}
        <label class="flex items-center justify-between rounded-lg border border-gray-200 p-4">
            <div>
                <p class="font-medium text-gray-900">বিভাগ (Department) সিস্টেম</p>
                <p class="text-sm text-gray-500">চালু করলে ক্লাসের সাথে বিভাগ যোগ করা যাবে (বিজ্ঞান/মানবিক ইত্যাদি)</p>
            </div>
            <input type="checkbox" wire:model="hasDepartments"
                   class="h-6 w-11 shrink-0 rounded-full accent-blue-600">
        </label>

        <label class="flex items-center justify-between rounded-lg border border-gray-200 p-4">
            <div>
                <p class="font-medium text-gray-900">পরপর পিরিয়ড ব্লকিং</p>
                <p class="text-sm text-gray-500">চালু থাকলে একজন শিক্ষকের রুটিনে পরপর দুই পিরিয়ড দেওয়া যাবে না</p>
            </div>
            <input type="checkbox" wire:model="consecutivePeriodBlocking"
                   class="h-6 w-11 shrink-0 rounded-full accent-blue-600">
        </label>
    </div>

    <div class="mt-6 rounded-lg border border-gray-200 p-4">
        <p class="mb-3 font-medium text-gray-900">ব্র্যান্ড কালার (White-labeling)</p>
        <div class="flex gap-4">
            <div>
                <label class="mb-1 block text-sm text-gray-700">প্রাইমারি</label>
                <input type="color" wire:model="themePrimaryColor" class="h-11 w-16 rounded border border-gray-300">
            </div>
            <div>
                <label class="mb-1 block text-sm text-gray-700">অ্যাকসেন্ট</label>
                <input type="color" wire:model="themeAccentColor" class="h-11 w-16 rounded border border-gray-300">
            </div>
        </div>
    </div>

    <button wire:click="save"
            class="mt-6 w-full rounded-lg bg-blue-600 py-3 text-base font-medium text-white active:bg-blue-700 md:w-auto md:px-8">
        {{ $saved ? '✓ সেভ হয়েছে' : 'সেভ করুন' }}
    </button>
</div>
