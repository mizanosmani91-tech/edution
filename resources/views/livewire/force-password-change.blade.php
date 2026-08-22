<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>পাসওয়ার্ড পরিবর্তন — {{ auth()->user()->institution?->name ?? 'EDUTION' }}</title>
    @php
        $__inst = auth()->user()->institution;
        $__settings = $__inst?->settings;
        $__primary = $__settings->theme_primary_color ?? null;
        $__accent = $__settings->theme_accent_color ?? null;
        $__darken = function (string $hex, float $pct): string {
            $hex = ltrim($hex, '#');
            if (strlen($hex) !== 6) { return '#' . $hex; }
            [$r, $g, $b] = array_map(fn ($c) => (int) min(255, max(0, hexdec($c) * (1 - $pct))), str_split($hex, 2));
            return sprintf('#%02x%02x%02x', $r, $g, $b);
        };
    @endphp
    @if ($__inst?->favicon_path)
        <link rel="icon" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($__inst->favicon_path) }}">
    @endif
    @vite(['resources/css/app.css'])
    @livewireStyles
    @if ($__primary || $__accent)
        <style>
            :root {
                @if ($__primary)
                    --color-maroon: {{ $__primary }};
                    --color-maroon-deep: {{ $__darken($__primary, 0.35) }};
                @endif
                @if ($__accent)
                    --color-gold: {{ $__accent }};
                    --color-gold-light: {{ $__darken($__accent, -0.25) }};
                @endif
            }
        </style>
    @endif
</head>
<body class="flex min-h-screen items-center justify-center bg-[radial-gradient(1200px_700px_at_15%_10%,#EEF1FA_0%,transparent_60%),radial-gradient(1000px_600px_at_90%_90%,#E7DEC5_0%,transparent_55%),#E5DCC5] p-4">
<div class="w-full max-w-[440px] rounded-[22px] bg-white p-9 shadow-[0_30px_60px_-20px_rgba(31,36,50,.35)]">

    <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full overflow-hidden" style="background:var(--color-maroon);">
        @if ($__inst?->logo_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($__inst->logo_path) }}" alt="{{ $__inst->name }}" class="h-full w-full object-cover">
        @else
            <svg viewBox="0 0 24 24" fill="none" stroke="var(--color-gold-light)" stroke-width="1.6" class="h-7 w-7">
                <path d="M4 6.5c2.8-1.4 5.6-1.4 8 0v11c-2.4-1.4-5.2-1.4-8 0v-11Z"/>
                <path d="M20 6.5c-2.8-1.4-5.6-1.4-8 0v11c2.4-1.4 5.2-1.4 8 0v-11Z"/>
            </svg>
        @endif
    </div>

    <h1 class="text-center text-[22px] font-bold text-[var(--color-ink)]">নতুন পাসওয়ার্ড সেট করুন</h1>
    <p class="mt-1.5 text-center text-[13.5px] text-[var(--color-ink-muted)]">
        নিরাপত্তার জন্য প্রথমবার লগইনের সময় নতুন পাসওয়ার্ড দিতে হবে। এই পেজ পার হওয়ার আগ পর্যন্ত অন্য কোনো পেজে যাওয়া যাবে না।
    </p>

    <form wire:submit="save" class="mt-6 space-y-4">
        <div>
            <label class="mb-1.5 block text-[12.5px] font-semibold text-[var(--color-ink)]">নতুন পাসওয়ার্ড</label>
            <input type="password" wire:model="password" class="w-full rounded-lg border border-[var(--color-line)] px-3.5 py-2.5 text-[14px] outline-none focus:border-[var(--color-maroon)]" placeholder="কমপক্ষে ৮ ক্যারেক্টার" autofocus>
            @error('password') <p class="mt-1 text-[12.5px] text-[var(--color-bad)]">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1.5 block text-[12.5px] font-semibold text-[var(--color-ink)]">পাসওয়ার্ড আবার লিখুন</label>
            <input type="password" wire:model="password_confirmation" class="w-full rounded-lg border border-[var(--color-line)] px-3.5 py-2.5 text-[14px] outline-none focus:border-[var(--color-maroon)]" placeholder="পাসওয়ার্ড নিশ্চিত করুন">
        </div>

        <button type="submit" class="mt-2 w-full rounded-lg py-3 text-[14.5px] font-bold text-[var(--color-ink)] transition hover:brightness-105" style="background:linear-gradient(90deg, var(--color-gold-light), var(--color-gold));">
            সংরক্ষণ করে চালিয়ে যান
        </button>
    </form>
</div>
@livewireScripts
</body>
</html>
