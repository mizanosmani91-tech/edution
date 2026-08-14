<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>লগইন — {{ $institution->name ?? 'EDUTION' }}</title>
    @if ($institution && $institution->favicon_path)
        <link rel="icon" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($institution->favicon_path) }}">
    @endif
    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
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
    @if ($__primary || $__accent)
        <style>
            :root {
                @if ($__primary)
                    --color-maroon: {{ $__primary }};
                    --color-maroon-deep: {{ $__darken($__primary, 0.35) }};
                    --color-maroon-light: {{ $__darken($__primary, -0.15) }};
                @endif
                @if ($__accent)
                    --color-gold: {{ $__accent }};
                    --color-gold-light: {{ $__darken($__accent, -0.25) }};
                @endif
            }
        </style>
    @endif
</head>
<body class="flex min-h-screen items-center justify-center bg-[radial-gradient(1200px_700px_at_15%_10%,#EFE7D3_0%,transparent_60%),radial-gradient(1000px_600px_at_90%_90%,#E7DEC5_0%,transparent_55%),#E5DCC5] p-4"
      x-data="{ role: 'admin', roles: {
          admin: { color: '#C9A227', title: 'এডমিন হিসেবে লগইন করুন', sub: 'প্রতিষ্ঠানের সার্বিক নিয়ন্ত্রণ ও পরিচালনার জন্য প্রবেশ করুন।', idLabel: 'এডমিন আইডি অথবা ইমেইল' },
          teacher: { color: '#35528F', title: 'শিক্ষক/স্টাফ হিসেবে লগইন করুন', sub: 'ক্লাস, হাজিরা ও ফলাফল ব্যবস্থাপনায় প্রবেশ করুন।', idLabel: 'স্টাফ ইমেইল' },
          guardian: { color: '#2F6E52', title: 'অভিভাবক হিসেবে লগইন করুন', sub: 'সন্তানের হাজিরা, ফলাফল ও নোটিশ দেখতে প্রবেশ করুন।', idLabel: 'অভিভাবক ইমেইল' },
          student: { color: '#A65A2E', title: 'শিক্ষার্থী হিসেবে লগইন করুন', sub: 'রুটিন, ফলাফল ও লার্নিং ম্যাটেরিয়াল দেখতে প্রবেশ করুন।', idLabel: 'শিক্ষার্থী ইমেইল' }
      } }">

    <div class="grid w-full max-w-[1080px] overflow-hidden rounded-[22px] bg-[var(--color-paper)] shadow-[0_30px_60px_-20px_rgba(60,30,20,.35)] md:grid-cols-[0.86fr_28px_1.14fr]">

        {{-- কভার (বাম) --}}
        <div class="relative hidden flex-col overflow-hidden bg-[radial-gradient(120%_140%_at_8%_0%,#6E2136_0%,var(--color-maroon)_45%,var(--color-maroon-deep)_100%)] p-10 text-[var(--color-gold-light)] md:flex">
            <div class="relative z-10 mb-5 flex h-14 w-14 items-center justify-center rounded-full border border-[rgba(231,199,103,.65)] overflow-hidden bg-[rgba(231,199,103,.08)]">
                @if ($institution && $institution->logo_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($institution->logo_path) }}" alt="{{ $institution->name }}" class="h-full w-full object-cover">
                @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="#E7C767" stroke-width="1.6" class="h-7 w-7">
                        <path d="M4 6.5c2.8-1.4 5.6-1.4 8 0v11c-2.4-1.4-5.2-1.4-8 0v-11Z"/>
                        <path d="M20 6.5c-2.8-1.4-5.6-1.4-8 0v11c2.4-1.4 5.2-1.4 8 0v-11Z"/>
                    </svg>
                @endif
            </div>
            <p class="relative z-10 mb-2 text-[11px] font-semibold uppercase tracking-[.28em] text-[rgba(231,199,103,.72)]">{{ $institution ? 'EDUTION দ্বারা পরিচালিত' : 'EDUCATION MANAGEMENT' }}</p>
            <h1 class="font-serif-bn relative z-10 mb-3 text-[36px] leading-tight text-[var(--color-gold-light)]">{{ $institution->name ?? 'EDUTION' }}</h1>
            <p class="relative z-10 mb-8 max-w-[34ch] text-[15.5px] leading-7 text-[rgba(248,243,230,.82)]">
                @if ($institution)
                    <strong>{{ $institution->name }}</strong>-এর ডিজিটাল হাজিরা খাতায় স্বাগতম — ভর্তি থেকে ফলাফল, সবকিছু এক জায়গায়।
                @else
                    স্কুল, কিন্ডারগার্টেন ও মাদরাসা পরিচালনার জন্য একটি সম্পূর্ণ ডিজিটাল হাজিরা খাতা — ভর্তি থেকে ফলাফল, সবকিছু এক জায়গায়।
                @endif
            </p>

            <div class="relative z-10 mt-auto flex flex-col gap-3.5 border-t border-dashed border-[rgba(231,199,103,.3)] pt-4.5">
                <p class="text-xs tracking-wider text-[rgba(231,199,103,.6)]">আজকের হাজিরা</p>
                @foreach (['এডমিন', 'শিক্ষক / স্টাফ', 'অভিভাবক', 'শিক্ষার্থী'] as $i => $role)
                    <div class="flex items-center gap-3 text-[15px] text-[rgba(248,243,230,.92)]">
                        <span class="flex h-[19px] w-[19px] shrink-0 items-center justify-center rounded-[5px] border border-[rgba(231,199,103,.55)] bg-[var(--color-gold)]">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#3E1120" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="h-3 w-3"><polyline points="20 6 9 17 4 12"/></svg>
                        </span>
                        <span>{{ $role }}</span>
                        <span class="flex-1 border-b border-dotted border-[rgba(231,199,103,.35)]"></span>
                    </div>
                @endforeach
            </div>

            <p class="relative z-10 mt-5 text-[12.5px] text-[rgba(231,199,103,.55)]">
                @if ($institution)
                    Powered by EDUTION
                @else
                    ৫০০+ প্রতিষ্ঠান প্রতিদিন EDUTION ব্যবহার করছে
                @endif
            </p>
        </div>

        {{-- SPINE — বইয়ের বাঁধাইয়ের মতো ডট --}}
        <div class="relative hidden bg-gradient-to-b from-[#3E1120] via-[#2C0C17] to-[#3E1120] shadow-[inset_3px_0_8px_rgba(0,0,0,.4),inset_-3px_0_8px_rgba(0,0,0,.4)] md:block">
            <div class="absolute left-1/2 top-0 flex h-full -translate-x-1/2 flex-col justify-evenly py-6">
                @for ($i = 0; $i < 10; $i++)
                    <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-br from-[#cfc4a8] to-[#8b8062] shadow"></span>
                @endfor
            </div>
        </div>

        {{-- ফর্ম (ডান) --}}
        <div class="relative flex flex-col p-8 md:p-10">
            {{-- Role tabs --}}
            <div class="mb-6 flex flex-wrap gap-1.5">
                <template x-for="(data, key) in roles" :key="key">
                    <button type="button" @click="role = key"
                        :style="role === key ? 'border-color:' + data.color + ';color:' + data.color + ';background:' + data.color + '1A' : ''"
                        class="flex items-center gap-1.5 rounded-full border border-[var(--color-line)] px-3.5 py-1.5 text-[13px] font-medium text-[var(--color-ink-muted)]">
                        <span class="h-1.5 w-1.5 rounded-full" :style="'background:' + data.color"></span>
                        <span x-text="{admin:'এডমিন',teacher:'শিক্ষক/স্টাফ',guardian:'অভিভাবক',student:'শিক্ষার্থী'}[key]"></span>
                    </button>
                </template>
            </div>

            <div class="mb-6">
                <h1 class="font-serif-bn text-2xl text-[var(--color-ink)]" x-text="roles[role].title">এডমিন হিসেবে লগইন করুন</h1>
                <p class="mt-1 text-sm text-[var(--color-ink-muted)]" x-text="roles[role].sub">প্রতিষ্ঠানের সার্বিক নিয়ন্ত্রণ ও পরিচালনার জন্য প্রবেশ করুন।</p>
            </div>

            @if (!$institution)
                <div class="mb-5 rounded-lg border border-[var(--color-gold)]/50 bg-[var(--color-gold)]/10 px-4 py-3.5 text-sm">
                    <p class="mb-2 font-semibold text-[var(--color-ink)]">লাইভ ডেমো দিয়ে ঘুরে দেখুন</p>
                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-center justify-between gap-2 rounded-md bg-white px-3 py-1.5">
                            <span class="text-[var(--color-ink-muted)]">ইমেইল</span>
                            <span class="flex items-center gap-2 font-medium text-[var(--color-ink)]">
                                <span id="demo-email">{{ \Database\Seeders\DemoSeeder::EMAIL }}</span>
                                <button type="button" onclick="navigator.clipboard.writeText('{{ \Database\Seeders\DemoSeeder::EMAIL }}'); this.textContent='কপি হয়েছে'; setTimeout(() => this.textContent='কপি', 1500)"
                                    class="rounded border border-[var(--color-line)] px-2 py-0.5 text-[11px] text-[var(--color-ink-muted)] hover:bg-[var(--color-paper-deep)]">কপি</button>
                            </span>
                        </div>
                        <div class="flex items-center justify-between gap-2 rounded-md bg-white px-3 py-1.5">
                            <span class="text-[var(--color-ink-muted)]">পাসওয়ার্ড</span>
                            <span class="flex items-center gap-2 font-medium text-[var(--color-ink)]">
                                <span id="demo-password">{{ \Database\Seeders\DemoSeeder::PASSWORD }}</span>
                                <button type="button" onclick="navigator.clipboard.writeText('{{ \Database\Seeders\DemoSeeder::PASSWORD }}'); this.textContent='কপি হয়েছে'; setTimeout(() => this.textContent='কপি', 1500)"
                                    class="rounded border border-[var(--color-line)] px-2 py-0.5 text-[11px] text-[var(--color-ink-muted)] hover:bg-[var(--color-paper-deep)]">কপি</button>
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-[var(--color-bad)]/30 bg-[var(--color-bad)]/10 px-4 py-3 text-sm text-[var(--color-bad)]">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="flex flex-1 flex-col">
                @csrf

                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-[var(--color-ink)]" x-text="roles[role].idLabel">এডমিন আইডি অথবা ইমেইল</label>
                    <div class="flex items-center gap-2.5 rounded-lg border border-[var(--color-line)] bg-white px-3.5 py-2.5 focus-within:border-[var(--color-gold)] focus-within:ring-2 focus-within:ring-[var(--color-gold)]/20">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-[18px] w-[18px] shrink-0 text-[var(--color-ink-soft)]"><circle cx="12" cy="8" r="3.2"/><path d="M5 20c1.2-3.6 4-5.4 7-5.4s5.8 1.8 7 5.4"/></svg>
                        <input type="email" name="email" required placeholder="ইমেইল লিখুন"
                            class="w-full border-0 bg-transparent text-[15px] text-[var(--color-ink)] outline-none">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-[var(--color-ink)]">পাসওয়ার্ড</label>
                    <div class="flex items-center gap-2.5 rounded-lg border border-[var(--color-line)] bg-white px-3.5 py-2.5 focus-within:border-[var(--color-gold)] focus-within:ring-2 focus-within:ring-[var(--color-gold)]/20"
                         x-data="{ show: false }">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-[18px] w-[18px] shrink-0 text-[var(--color-ink-soft)]"><rect x="5" y="10.5" width="14" height="9" rx="2"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3"/></svg>
                        <input :type="show ? 'text' : 'password'" name="password" required placeholder="••••••••"
                            class="w-full border-0 bg-transparent text-[15px] text-[var(--color-ink)] outline-none">
                        <button type="button" @click="show = !show" class="shrink-0 text-[var(--color-ink-soft)]">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-[18px] w-[18px]"><path d="M1.5 12S5 5 12 5s10.5 7 10.5 7-3.5 7-10.5 7S1.5 12 1.5 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <div class="mb-5 flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 text-[var(--color-ink-muted)]">
                        <input type="checkbox" class="rounded border-[var(--color-line)]"> আমাকে মনে রাখুন
                    </label>
                    <a href="#" class="text-[var(--color-maroon)] hover:underline">পাসওয়ার্ড ভুলে গেছেন?</a>
                </div>

                <button type="submit"
                    class="flex items-center justify-center gap-2 rounded-lg bg-[var(--color-maroon)] py-3 font-medium text-white transition hover:brightness-110"
                    :style="'background:' + roles[role].color">
                    লগইন করুন
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
                </button>
            </form>

            <div class="my-5 flex items-center gap-3 text-xs text-[var(--color-ink-soft)]">
                <span class="h-px flex-1 bg-[var(--color-line)]"></span> অথবা <span class="h-px flex-1 bg-[var(--color-line)]"></span>
            </div>

            <button type="button" class="flex items-center justify-center gap-2 rounded-lg border border-[var(--color-line)] py-2.5 text-sm text-[var(--color-ink-muted)] hover:bg-[var(--color-paper-deep)]">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="6" y="2.5" width="12" height="19" rx="2"/><path d="M10.5 18.5h3"/></svg>
                মোবাইল OTP দিয়ে লগইন করুন
            </button>

            <div class="mt-auto pt-6 text-[13px] text-[var(--color-ink-muted)]">
                নতুন প্রতিষ্ঠান? <a href="#" class="font-bold text-[var(--color-maroon)] hover:underline">বিনামূল্যে ট্রায়াল শুরু করুন</a>
            </div>
        </div>
    </div>

</body>
</html>
