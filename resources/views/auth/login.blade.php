<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#6C5CE7">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
        }
    </script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>লগইন — {{ $institution->name ?? 'EDUTION' }}</title>
    @if ($institution && $institution->favicon_path)
        <link rel="icon" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($institution->favicon_path) }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
<body class="flex min-h-screen items-center justify-center bg-[radial-gradient(1200px_700px_at_15%_10%,#EEF1FA_0%,transparent_60%),radial-gradient(1000px_600px_at_90%_90%,#E7DEC5_0%,transparent_55%),#E5DCC5] p-4"
      x-data="{
          role: 'admin',
          roles: {
              admin: { color: '#F59E0B', title: 'এডমিন হিসেবে লগইন করুন', sub: 'প্রতিষ্ঠানের সার্বিক নিয়ন্ত্রণ ও পরিচালনার জন্য প্রবেশ করুন।', idLabel: 'এডমিন আইডি অথবা ইমেইল', demoRole: 'admin', demoEmail: '{{ \Database\Seeders\DemoSeeder::EMAIL }}', demoPassword: '{{ \Database\Seeders\DemoSeeder::PASSWORD }}' },
              teacher: { color: '#3B82F6', title: 'শিক্ষক/স্টাফ হিসেবে লগইন করুন', sub: 'ক্লাস, হাজিরা ও ফলাফল ব্যবস্থাপনায় প্রবেশ করুন।', idLabel: 'স্টাফ ইমেইল', demoRole: 'teacher', demoEmail: '{{ \Database\Seeders\DemoSeeder::TEACHER_EMAIL }}', demoPassword: '{{ \Database\Seeders\DemoSeeder::STAFF_PASSWORD }}' },
              guardian: { color: '#10B981', title: 'অভিভাবক হিসেবে লগইন করুন', sub: 'সন্তানের হাজিরা, ফলাফল ও নোটিশ দেখতে প্রবেশ করুন।', idLabel: 'অভিভাবক ইমেইল', demoRole: 'guardian', demoEmail: '{{ \Database\Seeders\DemoSeeder::GUARDIAN_EMAIL }}', demoPassword: '{{ \Database\Seeders\DemoSeeder::STAFF_PASSWORD }}' },
              student: { color: '#EC4899', title: 'শিক্ষার্থী হিসেবে লগইন করুন', sub: 'রুটিন, ফলাফল ও লার্নিং ম্যাটেরিয়াল দেখতে প্রবেশ করুন।', idLabel: 'শিক্ষার্থী ইমেইল', demoRole: null, demoEmail: null, demoPassword: null }
          },
          demoToken: localStorage.getItem('edution_demo_token'),
          demoStatus: null,
          regBusy: false,
          reg: { name: '', phone: '', institution_name: '' },
          init() {
              if (this.demoToken) { this.refreshStatus(); }
          },
          csrf() { return document.querySelector('meta[name=csrf-token]').content; },
          async register() {
              if (!this.reg.name || !this.reg.phone) return;
              this.regBusy = true;
              try {
                  const res = await fetch('{{ route('demo.register') }}', {
                      method: 'POST',
                      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                      body: JSON.stringify(this.reg),
                  });
                  const data = await res.json();
                  this.demoToken = data.token;
                  localStorage.setItem('edution_demo_token', data.token);
                  await this.refreshStatus();
              } finally {
                  this.regBusy = false;
              }
          },
          async refreshStatus() {
              if (!this.demoToken) return;
              const res = await fetch('{{ route('demo.status') }}?token=' + this.demoToken);
              this.demoStatus = await res.json();
          },
          async requestAccess(r) {
              await fetch('{{ route('demo.request-access') }}', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                  body: JSON.stringify({ token: this.demoToken, role: r }),
              });
              await this.refreshStatus();
          },
          minutesLeft(iso) {
              if (!iso) return 0;
              return Math.max(0, Math.ceil((new Date(iso) - new Date()) / 60000));
          },
      }">

    <div class="grid w-full max-w-[1080px] overflow-hidden rounded-[22px] bg-[var(--color-paper)] shadow-[0_30px_60px_-20px_rgba(31,36,50,.35)] md:grid-cols-[0.86fr_28px_1.14fr]">

        {{-- কভার (বাম) --}}
        <div class="relative hidden flex-col overflow-hidden p-10 text-[var(--color-gold-light)] md:flex" style="background:var(--color-maroon);">
            <div class="relative z-10 mb-5 flex h-14 w-14 items-center justify-center rounded-full border border-[rgba(251,191,36,.65)] overflow-hidden bg-[rgba(251,191,36,.08)]">
                @if ($institution && $institution->logo_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($institution->logo_path) }}" alt="{{ $institution->name }}" class="h-full w-full object-cover">
                @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="#FBBF24" stroke-width="1.6" class="h-7 w-7">
                        <path d="M4 6.5c2.8-1.4 5.6-1.4 8 0v11c-2.4-1.4-5.2-1.4-8 0v-11Z"/>
                        <path d="M20 6.5c-2.8-1.4-5.6-1.4-8 0v11c2.4-1.4 5.2-1.4 8 0v-11Z"/>
                    </svg>
                @endif
            </div>
            <p class="relative z-10 mb-2 text-[11px] font-semibold uppercase tracking-[.28em] text-[rgba(251,191,36,.72)]">{{ $institution ? 'EDUTION দ্বারা পরিচালিত' : 'EDUCATION MANAGEMENT' }}</p>
            <h1 class="font-serif-bn relative z-10 mb-3 text-[36px] leading-tight text-[var(--color-gold-light)]">{{ $institution->name ?? 'EDUTION' }}</h1>
            <p class="relative z-10 mb-8 max-w-[34ch] text-[15.5px] leading-7 text-[rgba(248,243,230,.82)]">
                @if ($institution)
                    <strong>{{ $institution->name }}</strong>-এর ডিজিটাল হাজিরা খাতায় স্বাগতম — ভর্তি থেকে ফলাফল, সবকিছু এক জায়গায়।
                @else
                    স্কুল, কিন্ডারগার্টেন ও মাদরাসা পরিচালনার জন্য একটি সম্পূর্ণ ডিজিটাল হাজিরা খাতা — ভর্তি থেকে ফলাফল, সবকিছু এক জায়গায়।
                @endif
            </p>

            <div class="relative z-10 mt-auto flex flex-col gap-3.5 border-t border-dashed border-[rgba(251,191,36,.3)] pt-4.5">
                <p class="text-xs tracking-wider text-[rgba(251,191,36,.6)]">আজকের হাজিরা</p>
                @foreach (['এডমিন', 'শিক্ষক / স্টাফ', 'অভিভাবক', 'শিক্ষার্থী'] as $i => $role)
                    <div class="flex items-center gap-3 text-[15px] text-[rgba(248,243,230,.92)]">
                        <span class="flex h-[19px] w-[19px] shrink-0 items-center justify-center rounded-[5px] border border-[rgba(251,191,36,.55)] bg-[var(--color-gold)]">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#4B3FC4" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="h-3 w-3"><polyline points="20 6 9 17 4 12"/></svg>
                        </span>
                        <span>{{ $role }}</span>
                        <span class="flex-1 border-b border-dotted border-[rgba(251,191,36,.35)]"></span>
                    </div>
                @endforeach
            </div>

            <p class="relative z-10 mt-5 text-[12.5px] text-[rgba(251,191,36,.55)]">
                @if ($institution)
                    Powered by EDUTION
                @else
                    ৫০০+ প্রতিষ্ঠান প্রতিদিন EDUTION ব্যবহার করছে
                @endif
            </p>
        </div>

        {{-- SPINE — বইয়ের বাঁধাইয়ের মতো ডট --}}
        <div class="relative hidden bg-gradient-to-b from-[#4B3FC4] via-[#2C0C17] to-[#4B3FC4] shadow-[inset_3px_0_8px_rgba(0,0,0,.4),inset_-3px_0_8px_rgba(0,0,0,.4)] md:block">
            <div class="absolute left-1/2 top-0 flex h-full -translate-x-1/2 flex-col justify-evenly py-6">
                @for ($i = 0; $i < 10; $i++)
                    <span class="h-1.5 w-1.5 rounded-full bg-gradient-to-br from-[#cfc4a8] to-[#8b8062] shadow"></span>
                @endfor
            </div>
        </div>

        {{-- ফর্ম (ডান) --}}
        <div class="relative flex flex-col p-8 md:p-10">
            {{-- মোবাইল/অ্যাপে দেখা যাওয়া ব্র্যান্ডিং হেডার — বাম কভার প্যানেল
                 md এর নিচে hidden থাকে বলে ছোট স্ক্রিনে লোগো/প্রতিষ্ঠানের নাম
                 আলাদাভাবে এখানে দেখানো হচ্ছে (Alpine নির্ভর নয়, তাই সবসময় দেখা যাবে) --}}
            <div class="mb-6 flex items-center gap-3 md:hidden">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full border border-[var(--color-gold)]/50 bg-[var(--color-maroon)]">
                    @if ($institution && $institution->logo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($institution->logo_path) }}" alt="{{ $institution->name }}" class="h-full w-full object-cover">
                    @else
                        <svg viewBox="0 0 24 24" fill="none" stroke="#FBBF24" stroke-width="1.6" class="h-6 w-6">
                            <path d="M4 6.5c2.8-1.4 5.6-1.4 8 0v11c-2.4-1.4-5.2-1.4-8 0v-11Z"/>
                            <path d="M20 6.5c-2.8-1.4-5.6-1.4-8 0v11c2.4-1.4 5.2-1.4 8 0v-11Z"/>
                        </svg>
                    @endif
                </div>
                <div>
                    <p class="font-serif-bn text-lg leading-tight text-[var(--color-ink)]">{{ $institution->name ?? 'EDUTION' }}</p>
                    <p class="text-[11px] uppercase tracking-[.2em] text-[var(--color-ink-muted)]">{{ $institution ? 'EDUTION দ্বারা পরিচালিত' : 'EDUCATION MANAGEMENT' }}</p>
                </div>
            </div>

            {{-- Role tabs --}}
            <div class="mb-6 grid grid-cols-2 gap-2 sm:flex sm:flex-wrap">
                <template x-for="(data, key) in roles" :key="key">
                    <button type="button" @click="role = key"
                        :style="role === key ? 'border-color:' + data.color + ';color:' + data.color + ';background:' + data.color + '1A' : ''"
                        class="flex w-full items-center justify-center gap-1.5 rounded-full border border-[var(--color-line)] px-3.5 py-1.5 text-[13px] font-medium text-[var(--color-ink-muted)] sm:w-auto">
                        <span class="h-1.5 w-1.5 rounded-full" :style="'background:' + data.color"></span>
                        <span x-text="{admin:'এডমিন',teacher:'শিক্ষক/স্টাফ',guardian:'অভিভাবক',student:'শিক্ষার্থী'}[key]"></span>
                    </button>
                </template>
            </div>

            <div class="mb-6">
                <h1 class="font-serif-bn text-2xl text-[var(--color-ink)]" x-text="roles[role].title">এডমিন হিসেবে লগইন করুন</h1>
                <p class="mt-1 text-sm text-[var(--color-ink-muted)]" x-text="roles[role].sub">প্রতিষ্ঠানের সার্বিক নিয়ন্ত্রণ ও পরিচালনার জন্য প্রবেশ করুন।</p>

                {{-- শুধু নেটিভ Android অ্যাপ থেকে খোলা হলেই দেখা যাবে (লঞ্চার ?app=1 যোগ করে) —
                     সাধারণ ব্রাউজারে এই লিংক লাগবে না, কারণ URL সরাসরি লিখেই প্রতিষ্ঠান বদলানো যায় --}}
                @if (request()->query('app') === '1')
                    <a href="https://localhost/index.html?reset=1" class="mt-2 inline-flex items-center gap-1 text-[12.5px] text-[var(--color-ink-muted)] underline underline-offset-2">
                        অন্য প্রতিষ্ঠানে প্রবেশ করতে চান? এখানে চাপুন
                    </a>
                @endif
            </div>

            @if (!$institution)
                <div class="mb-5 rounded-lg border border-[var(--color-gold)]/50 bg-[var(--color-gold)]/10 px-4 py-3.5 text-sm" x-show="role !== 'student'" x-cloak>

                    {{-- ১) এখনো রেজিস্ট্রেশন করেনি --}}
                    <template x-if="!demoToken">
                        <div>
                            <p class="mb-2 font-semibold text-[var(--color-ink)]">লাইভ ডেমো দেখতে ছোট্ট একটা তথ্য দিন</p>
                            <div class="flex flex-col gap-1.5">
                                <input type="text" x-model="reg.name" placeholder="আপনার নাম" class="rounded-md border border-[var(--color-line)] bg-white px-3 py-1.5 text-[13px] outline-none">
                                <input type="text" x-model="reg.phone" placeholder="মোবাইল নম্বর" class="rounded-md border border-[var(--color-line)] bg-white px-3 py-1.5 text-[13px] outline-none">
                                <input type="text" x-model="reg.institution_name" placeholder="প্রতিষ্ঠানের নাম (ঐচ্ছিক)" class="rounded-md border border-[var(--color-line)] bg-white px-3 py-1.5 text-[13px] outline-none">
                                <button type="button" @click="register()" :disabled="regBusy || !reg.name || !reg.phone"
                                    class="mt-1 rounded-md px-3 py-2 text-[13px] font-semibold text-white disabled:opacity-50" style="background:var(--color-maroon);">
                                    <span x-text="regBusy ? 'অপেক্ষা করুন...' : 'ডেমো দেখতে চাই'"></span>
                                </button>
                            </div>
                        </div>
                    </template>

                    {{-- ২) রেজিস্ট্রেশন করা আছে --}}
                    <template x-if="demoToken && demoStatus">
                        <div>
                            {{-- এডমিন: সবসময় সাথে সাথে দেখা যায় --}}
                            <template x-if="role === 'admin'">
                                <div>
                                    <p class="mb-2 font-semibold text-[var(--color-ink)]">এডমিন ডেমো দিয়ে ঘুরে দেখুন</p>
                                    <div class="flex flex-col gap-1.5">
                                        <div class="flex items-center justify-between gap-2 rounded-md bg-white px-3 py-1.5">
                                            <span class="text-[var(--color-ink-muted)]">ইমেইল</span>
                                            <span class="flex items-center gap-2 font-medium text-[var(--color-ink)]">
                                                <span x-text="roles.admin.demoEmail"></span>
                                                <button type="button" @click="navigator.clipboard.writeText(roles.admin.demoEmail); $el.textContent='কপি হয়েছে'; setTimeout(() => $el.textContent='কপি', 1500)"
                                                    class="rounded border border-[var(--color-line)] px-2 py-0.5 text-[11px] text-[var(--color-ink-muted)] hover:bg-[var(--color-paper-deep)]">কপি</button>
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between gap-2 rounded-md bg-white px-3 py-1.5">
                                            <span class="text-[var(--color-ink-muted)]">পাসওয়ার্ড</span>
                                            <span class="flex items-center gap-2 font-medium text-[var(--color-ink)]">
                                                <span x-text="roles.admin.demoPassword"></span>
                                                <button type="button" @click="navigator.clipboard.writeText(roles.admin.demoPassword); $el.textContent='কপি হয়েছে'; setTimeout(() => $el.textContent='কপি', 1500)"
                                                    class="rounded border border-[var(--color-line)] px-2 py-0.5 text-[11px] text-[var(--color-ink-muted)] hover:bg-[var(--color-paper-deep)]">কপি</button>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- শিক্ষক/অভিভাবক: আনলক আছে কিনা দেখে ভিন্ন অবস্থা দেখাবে --}}
                            <template x-if="role === 'teacher' || role === 'guardian'">
                                <div>
                                    <template x-if="demoStatus[role] && demoStatus[role].globallyUnlockedUntil">
                                        <div>
                                            <p class="mb-2 font-semibold text-[var(--color-ink)]">
                                                আনলক করা আছে — <span x-text="minutesLeft(demoStatus[role].globallyUnlockedUntil)"></span> মিনিট বাকি
                                            </p>
                                            <div class="flex flex-col gap-1.5">
                                                <div class="flex items-center justify-between gap-2 rounded-md bg-white px-3 py-1.5">
                                                    <span class="text-[var(--color-ink-muted)]">ইমেইল</span>
                                                    <span class="flex items-center gap-2 font-medium text-[var(--color-ink)]">
                                                        <span x-text="roles[role].demoEmail"></span>
                                                        <button type="button" @click="navigator.clipboard.writeText(roles[role].demoEmail); $el.textContent='কপি হয়েছে'; setTimeout(() => $el.textContent='কপি', 1500)"
                                                            class="rounded border border-[var(--color-line)] px-2 py-0.5 text-[11px] text-[var(--color-ink-muted)] hover:bg-[var(--color-paper-deep)]">কপি</button>
                                                    </span>
                                                </div>
                                                <div class="flex items-center justify-between gap-2 rounded-md bg-white px-3 py-1.5">
                                                    <span class="text-[var(--color-ink-muted)]">পাসওয়ার্ড</span>
                                                    <span class="flex items-center gap-2 font-medium text-[var(--color-ink)]">
                                                        <span x-text="roles[role].demoPassword"></span>
                                                        <button type="button" @click="navigator.clipboard.writeText(roles[role].demoPassword); $el.textContent='কপি হয়েছে'; setTimeout(() => $el.textContent='কপি', 1500)"
                                                            class="rounded border border-[var(--color-line)] px-2 py-0.5 text-[11px] text-[var(--color-ink-muted)] hover:bg-[var(--color-paper-deep)]">কপি</button>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="!(demoStatus[role] && demoStatus[role].globallyUnlockedUntil) && demoStatus[role] && demoStatus[role].myStatus === 'pending'">
                                        <div>
                                            <p class="mb-2 font-medium text-[var(--color-ink)]">আপনার অনুরোধ পেয়েছি — আমরা শীঘ্রই কল করে যাচাই করে আনলক করে দেবো।</p>
                                            <button type="button" @click="refreshStatus()" class="rounded-md border border-[var(--color-line)] bg-white px-3 py-1.5 text-[12.5px] text-[var(--color-ink-muted)]">আবার চেক করুন</button>
                                        </div>
                                    </template>

                                    <template x-if="!(demoStatus[role] && demoStatus[role].globallyUnlockedUntil) && (!demoStatus[role] || demoStatus[role].myStatus !== 'pending')">
                                        <div>
                                            <p class="mb-2 font-medium text-[var(--color-ink)]">এই পোর্টাল দেখতে হলে আগে রিকোয়েস্ট করুন — আমরা কল দিয়ে যাচাই করে সময়সীমার জন্য আনলক করে দেবো।</p>
                                            <button type="button" @click="requestAccess(role)" class="rounded-md px-3 py-2 text-[13px] font-semibold text-white" :style="'background:' + roles[role].color">এক্সেস রিকোয়েস্ট করুন</button>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
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
                    <div class="flex items-center gap-2.5 rounded-xl border border-[var(--color-line)] bg-white px-3.5 py-3 focus-within:border-[var(--color-gold)] focus-within:ring-2 focus-within:ring-[var(--color-gold)]/20">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-[18px] w-[18px] shrink-0 text-[var(--color-ink-soft)]"><circle cx="12" cy="8" r="3.2"/><path d="M5 20c1.2-3.6 4-5.4 7-5.4s5.8 1.8 7 5.4"/></svg>
                        <input type="email" name="email" required placeholder="ইমেইল লিখুন"
                            class="w-full border-0 bg-transparent text-[15px] text-[var(--color-ink)] outline-none">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-[var(--color-ink)]">পাসওয়ার্ড</label>
                    <div class="flex items-center gap-2.5 rounded-xl border border-[var(--color-line)] bg-white px-3.5 py-3 focus-within:border-[var(--color-gold)] focus-within:ring-2 focus-within:ring-[var(--color-gold)]/20"
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
                    <a href="{{ route('password.forgot') }}" class="text-[var(--color-maroon)] hover:underline">পাসওয়ার্ড ভুলে গেছেন?</a>
                </div>

                <button type="submit"
                    class="flex items-center justify-center gap-2 rounded-xl bg-[var(--color-maroon)] py-3.5 font-medium text-white shadow-md shadow-black/10 transition hover:brightness-110 hover:shadow-lg active:scale-[0.99]"
                    :style="'background:' + roles[role].color">
                    লগইন করুন
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
                </button>
            </form>

            <div class="my-5 flex items-center gap-3 text-xs text-[var(--color-ink-soft)]">
                <span class="h-px flex-1 bg-[var(--color-line)]"></span> অথবা <span class="h-px flex-1 bg-[var(--color-line)]"></span>
            </div>

                            <button type="button" class="flex items-center justify-center gap-2 rounded-xl border border-[var(--color-line)] py-3 text-sm text-[var(--color-ink-muted)] transition hover:border-[var(--color-maroon)]/40 hover:bg-[var(--color-paper-deep)]">
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
