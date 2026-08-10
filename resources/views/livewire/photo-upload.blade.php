<div class="flex flex-col items-center gap-3">
    <div class="h-24 w-24 overflow-hidden rounded-full bg-gray-100">
        @if ($photo)
            <img src="{{ $photo->temporaryUrl() }}" class="h-full w-full object-cover">
        @elseif ($currentUrl)
            <img src="{{ $currentUrl }}" class="h-full w-full object-cover">
        @else
            <div class="flex h-full w-full items-center justify-center text-2xl text-gray-400">📷</div>
        @endif
    </div>

    <label class="cursor-pointer rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 active:bg-gray-200">
        {{ $currentUrl ? 'ছবি পরিবর্তন করুন' : 'ছবি আপলোড করুন' }}
        <input type="file" wire:model="photo" accept="image/*" class="hidden">
    </label>

    <div wire:loading wire:target="photo" class="text-sm text-gray-500">আপলোড হচ্ছে...</div>
    @error('photo') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
</div>
