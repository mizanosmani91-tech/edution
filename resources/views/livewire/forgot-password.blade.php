<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>পাসওয়ার্ড ভুলে গেছেন — {{ $institution->name ?? 'EDUTION' }}</title>
    @php
        $__settings = $institution?->settings;
        $__primary = $__settings->theme_primary_color ?? null;
        $__accent = $__settings->theme_accent_color ?? null;
        $__darken = function (string $hex, float $pct): string {
            $hex = ltrim($hex, '#');
            if (strlen($hex) !== 6) { return '#' . $hex; }
            [$r, $g, $b] = array_map(fn ($c) => (int) min(255, max(0, hexdec($c) * (1 - $pct))), str_split($hex, 2));
            return sprintf('#%02x%02x%02x', $r, $g, $b);
        };
    @endphp
    @if ($institution?->favicon_path)
        <link rel="icon" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($institution->favicon_path) }}">
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
<body class="flex min-h-screen items-center justify-center bg-[radial-gradient(1200px_700px_at_15%_10%,#EFE7D3_0%,transparent_60%),radial-gradient(1000px_600px_at_90%_90%,#E7DEC5_0%,transparent_55%),#E5DCC5] p-4">
<div class="w-full max-w-[440px] rounded-[22px] bg-white p-9 shadow-[0_30px_60px_-20px_rgba(60,30,20,.35)]">

    <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full overflow-hidden" style="background:var(--color-maroon);">
        @if ($institution?->logo_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($institution->logo_path) }}" alt="{{ $institution->name }}" class="h-full w-full object-cover">
        @else
            <svg viewBox="0 0 24 24" fill="none" stroke="var(--color-gold-light)" stroke-width="1.6" class="h-7 w-7">
                <path d="M12 9v4"/><circle cx="12" cy="16" r=".4" fill="var(--color-gold-light)"/>
                <path d="M10.3 4.5 2.6 18a1.8 1.8 0 0 0 1.6 2.7h15.6a1.8 1.8 0 0 0 1.6-2.7L13.7 4.5a1.8 1.8 0 0 0-3.4 0Z"/>
            </svg>
        @endif
    </div>

    @if ($formError)
        <div class="mb-4 rounded-lg border border-[rgba(166,65,46,.3)] bg-[rgba(166,65,46,.08)] px-3.5 py-2.5 text-[13px] text-[var(--color-bad)]">
            {{ $formError }}
        </div>
    @endif

    {{-- ধাপ ১: ইমেইল --}}
    @if ($step === 'email')
        <h1 class="text-center text-[21px] font-bold text-[var(--color-ink)]">পাসওয়ার্ড ভুলে গেছেন?</h1>
        <p class="mt-1.5 text-center text-[13.5px] text-[var(--color-ink-muted)]">আপনার লগইন ইমেইল দিন — সেই ঠিকানায় একটি যাচাই কোড পাঠানো হবে।</p>

        <form wire:submit="sendCode" class="mt-6 space-y-4">
            <div>
                <label class="mb-1.5 block text-[12.5px] font-semibold text-[var(--color-ink)]">ইমেইল</label>
                <input type="email" wire:model="email" class="w-full rounded-lg border border-[var(--color-line)] px-3.5 py-2.5 text-[14px] outline-none focus:border-[var(--color-maroon)]" placeholder="you@example.com" autofocus>
                @error('email') <p class="mt-1 text-[12.5px] text-[var(--color-bad)]">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="w-full rounded-lg py-3 text-[14.5px] font-bold text-[var(--color-ink)]" style="background:linear-gradient(90deg, var(--color-gold-light), var(--color-gold));">
                কোড পাঠান
            </button>
        </form>
    @endif

    {{-- ধাপ ২: OTP + নতুন পাসওয়ার্ড --}}
    @if ($step === 'otp')
        <h1 class="text-center text-[21px] font-bold text-[var(--color-ink)]">কোড ও নতুন পাসওয়ার্ড দিন</h1>
        <p class="mt-1.5 text-center text-[13.5px] text-[var(--color-ink-muted)]">
            {{ $resolvedEmail }} ঠিকানায় একটি ৬ সংখ্যার কোড পাঠানো হয়েছে।
        </p>

        @if ($infoMessage)
            <div class="mt-3 rounded-lg border border-[rgba(47,110,82,.3)] bg-[rgba(47,110,82,.08)] px-3.5 py-2.5 text-center text-[12.5px] text-[var(--color-good)]">
                {{ $infoMessage }}
            </div>
        @endif

        <form wire:submit="resetPassword" class="mt-6 space-y-4">
            <div>
                <label class="mb-1.5 block text-[12.5px] font-semibold text-[var(--color-ink)]">যাচাই কোড</label>
                <input type="text" inputmode="numeric" maxlength="6" wire:model="code" class="w-full rounded-lg border border-[var(--color-line)] px-3.5 py-2.5 text-center text-[18px] tracking-[0.3em] outline-none focus:border-[var(--color-maroon)]" placeholder="000000" autofocus>
                @error('code') <p class="mt-1 text-[12.5px] text-[var(--color-bad)]">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-[12.5px] font-semibold text-[var(--color-ink)]">নতুন পাসওয়ার্ড</label>
                <input type="password" wire:model="password" class="w-full rounded-lg border border-[var(--color-line)] px-3.5 py-2.5 text-[14px] outline-none focus:border-[var(--color-maroon)]" placeholder="কমপক্ষে ৮ ক্যারেক্টার">
                @error('password') <p class="mt-1 text-[12.5px] text-[var(--color-bad)]">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-[12.5px] font-semibold text-[var(--color-ink)]">পাসওয়ার্ড আবার লিখুন</label>
                <input type="password" wire:model="password_confirmation" class="w-full rounded-lg border border-[var(--color-line)] px-3.5 py-2.5 text-[14px] outline-none focus:border-[var(--color-maroon)]" placeholder="পাসওয়ার্ড নিশ্চিত করুন">
            </div>

            <button type="submit" class="w-full rounded-lg py-3 text-[14.5px] font-bold text-[var(--color-ink)]" style="background:linear-gradient(90deg, var(--color-gold-light), var(--color-gold));">
                পাসওয়ার্ড রিসেট করুন
            </button>
            <button type="button" wire:click="sendCode" class="w-full text-center text-[12.5px] font-semibold text-[var(--color-ink-muted)] hover:text-[var(--color-maroon)]">
                ইমেইলে কোড আবার পাঠান
            </button>
            @if ($smsAvailable && ! $smsSent)
                <button type="button" wire:click="sendSmsBackup" class="w-full text-center text-[12.5px] font-semibold text-[var(--color-maroon)] hover:underline">
                    ইমেইলে না পেলে ফোনে SMS পাঠান
                </button>
            @endif
        </form>
    @endif

    {{-- ধাপ ৩: সফল --}}
    @if ($step === 'done')
        <div class="text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full" style="background:rgba(47,110,82,.12);">
                <svg viewBox="0 0 24 24" fill="none" stroke="var(--color-good)" stroke-width="1.8" class="h-6 w-6"><path d="M20 6 9 17l-5-5"/></svg>
            </div>
            <h1 class="text-[21px] font-bold text-[var(--color-ink)]">পাসওয়ার্ড পরিবর্তন হয়েছে</h1>
            <p class="mt-1.5 text-[13.5px] text-[var(--color-ink-muted)]">নতুন পাসওয়ার্ড দিয়ে এখন লগইন করুন।</p>
            <a href="{{ route('login') }}" class="mt-6 inline-block w-full rounded-lg py-3 text-[14.5px] font-bold text-[var(--color-ink)]" style="background:linear-gradient(90deg, var(--color-gold-light), var(--color-gold));">
                লগইন পেজে যান
            </a>
        </div>
    @endif

    @if ($step !== 'done')
        <div class="mt-5 text-center text-[12.5px]">
            <a href="{{ route('login') }}" class="text-[var(--color-ink-muted)] hover:text-[var(--color-maroon)]">← লগইন পেজে ফিরে যান</a>
        </div>
    @endif
</div>
@livewireScripts
</body>
</html>
