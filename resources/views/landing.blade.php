<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EDUTION — স্কুল, মাদরাসা ও কিন্ডারগার্টেন ম্যানেজমেন্ট সিস্টেম</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-[var(--color-paper)]">

    {{-- ন্যাভ --}}
    <header class="sticky top-0 z-20 flex items-center justify-between border-b border-[var(--color-line)] bg-[var(--color-paper)]/90 px-6 py-4 backdrop-blur">
        <p class="font-serif-bn text-2xl text-[var(--color-maroon)]">EDUTION</p>
        <nav class="hidden items-center gap-7 text-sm text-[var(--color-ink-muted)] md:flex">
            <a href="#features" class="hover:text-[var(--color-maroon)]">ফিচার</a>
            <a href="#pricing" class="hover:text-[var(--color-maroon)]">মূল্য</a>
            <a href="#demo" class="hover:text-[var(--color-maroon)]">ডেমো</a>
        </nav>
        <div class="flex gap-3">
            <a href="{{ route('login') }}" class="rounded-lg border border-[var(--color-maroon)] px-5 py-2 text-sm font-medium text-[var(--color-maroon)]">লগইন</a>
            <a href="{{ route('register') }}" class="rounded-lg bg-[var(--color-maroon)] px-5 py-2 text-sm font-medium text-white">রেজিস্ট্রেশন করুন</a>
        </div>
    </header>

    {{-- হিরো --}}
    <main class="mx-auto max-w-3xl px-6 py-20 text-center">
        <h1 class="font-serif-bn text-4xl text-[var(--color-ink)] md:text-5xl">
            স্কুল, মাদরাসা ও কিন্ডারগার্টেনের জন্য<br>সম্পূর্ণ ডিজিটাল ব্যবস্থাপনা
        </h1>
        <p class="mx-auto mt-6 max-w-xl text-lg text-[var(--color-ink-muted)]">
            ভর্তি, হাজিরা, ফলাফল, ফি — সবকিছু এক জায়গায়। ৫০০+ প্রতিষ্ঠান প্রতিদিন EDUTION ব্যবহার করছে।
        </p>
        <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
            <a href="{{ route('register') }}"
               class="inline-block rounded-lg bg-[var(--color-maroon)] px-8 py-3.5 font-medium text-white hover:bg-[var(--color-maroon-deep)]">
                বিনামূল্যে শুরু করুন →
            </a>
            <a href="#demo"
               class="inline-block rounded-lg border border-[var(--color-maroon)] px-8 py-3.5 font-medium text-[var(--color-maroon)]">
                ডেমো দেখুন
            </a>
        </div>
    </main>

    {{-- ডেমো একাউন্ট --}}
    <section id="demo" class="mx-auto max-w-2xl px-6 pb-24">
        <div class="rounded-2xl border border-[var(--color-gold)]/40 bg-white p-8 text-center shadow-[0_20px_50px_-25px_rgba(60,30,20,.3)]">
            <p class="text-xs font-semibold uppercase tracking-[.2em] text-[var(--color-gold)]">সাইনআপ ছাড়াই ঘুরে দেখুন</p>
            <h2 class="font-serif-bn mt-2 text-2xl text-[var(--color-ink)]">লাইভ ডেমো ড্যাশবোর্ড</h2>
            <p class="mt-2 text-sm text-[var(--color-ink-muted)]">নিচের ক্রেডেনশিয়াল দিয়ে সরাসরি লগইন করে EDUTION-এর সবকিছু নিজে হাতে দেখুন।</p>
            <div class="mx-auto mt-5 flex max-w-sm flex-col gap-2 rounded-lg bg-[var(--color-paper)] p-4 text-left text-sm">
                <div class="flex justify-between"><span class="text-[var(--color-ink-muted)]">ইমেইল</span><span class="font-medium text-[var(--color-ink)]">{{ \Database\Seeders\DemoSeeder::EMAIL }}</span></div>
                <div class="flex justify-between"><span class="text-[var(--color-ink-muted)]">পাসওয়ার্ড</span><span class="font-medium text-[var(--color-ink)]">{{ \Database\Seeders\DemoSeeder::PASSWORD }}</span></div>
            </div>
            <a href="{{ route('login') }}"
               class="mt-6 inline-block rounded-lg bg-[var(--color-maroon)] px-7 py-3 text-sm font-medium text-white hover:bg-[var(--color-maroon-deep)]">
                ডেমো ড্যাশবোর্ডে প্রবেশ করুন
            </a>
        </div>
    </section>

    {{-- ফিচার --}}
    <section id="features" class="border-t border-[var(--color-line)] bg-white/60 px-6 py-20">
        <div class="mx-auto max-w-5xl">
            <h2 class="font-serif-bn text-center text-3xl text-[var(--color-ink)]">প্রতিষ্ঠান পরিচালনার জন্য যা যা দরকার</h2>
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['একাডেমিক ব্যবস্থাপনা', 'ক্লাস, শাখা, রুটিন, পরীক্ষা ও ফলাফল — সবকিছু একটি সিস্টেমে সাজিয়ে রাখুন।'],
                    ['ডিজিটাল হাজিরা', 'শিক্ষার্থী ও স্টাফের হাজিরা এক ক্লিকে নিন, ট্র্যাক করুন।'],
                    ['অনলাইন ভর্তি', 'ভর্তি ফরম, ইউনিক আইডি ও পোর্টাল অ্যাক্সেস — স্বয়ংক্রিয়ভাবে তৈরি হয়।'],
                    ['ফি ব্যবস্থাপনা', 'ফি সংগ্রহ, ইনভয়েস, বকেয়া ট্র্যাকিং ও রশিদ — সবকিছু স্বচ্ছভাবে।'],
                    ['অভিভাবক যোগাযোগ', 'নোটিশ, এসএমএস ও পোর্টালের মাধ্যমে সরাসরি সংযোগ।'],
                    ['রিপোর্ট ও এনালিটিক্স', 'একাডেমিক ও আর্থিক অবস্থা এক নজরে গ্রাফ ও চার্টের মাধ্যমে।'],
                ] as [$title, $desc])
                    <div class="rounded-xl border border-[var(--color-line)] bg-[var(--color-paper)] p-6">
                        <h3 class="font-serif-bn text-lg text-[var(--color-ink)]">{{ $title }}</h3>
                        <p class="mt-2 text-sm text-[var(--color-ink-muted)]">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- মূল্য --}}
    <section id="pricing" class="px-6 py-20">
        <div class="mx-auto max-w-5xl">
            <h2 class="font-serif-bn text-center text-3xl text-[var(--color-ink)]">সহজ, স্বচ্ছ মূল্য নির্ধারণ</h2>
            <div class="mt-10 grid gap-6 md:grid-cols-3">
                @foreach ([
                    ['basic', 'বেসিক', '৳১,৫০০', '৫০০ জনের কম শিক্ষার্থীর প্রতিষ্ঠানের জন্য', false],
                    ['standard', 'স্ট্যান্ডার্ড', '৳৩,৫০০', 'মাঝারি প্রতিষ্ঠানের জন্য সব ফিচার', true],
                    ['premium', 'প্রিমিয়াম', '৳৬,৫০০', 'বড় প্রতিষ্ঠান ও একাধিক শাখার জন্য', false],
                ] as [$key, $title, $price, $desc, $popular])
                    <div class="flex flex-col rounded-2xl border p-7 {{ $popular ? 'border-[var(--color-gold)] shadow-[0_20px_40px_-18px_rgba(201,162,39,.4)]' : 'border-[var(--color-line)]' }} bg-white">
                        @if ($popular)
                            <span class="mb-2 inline-block w-fit rounded-full bg-[var(--color-gold)]/15 px-3 py-1 text-xs font-semibold text-[var(--color-gold)]">জনপ্রিয়</span>
                        @endif
                        <h3 class="font-serif-bn text-lg text-[var(--color-ink)]">{{ $title }}</h3>
                        <p class="mb-4 mt-1 text-xs text-[var(--color-ink-muted)]">{{ $desc }}</p>
                        <div class="font-serif-bn mb-6 text-3xl text-[var(--color-maroon)]">{{ $price }}<span class="text-sm font-sans text-[var(--color-ink-muted)]">/মাস</span></div>
                        <a href="{{ route('register') }}?plan={{ $key }}"
                           class="mt-auto w-full rounded-lg py-2.5 text-center text-sm font-medium {{ $popular ? 'bg-[var(--color-maroon)] text-white' : 'border border-[var(--color-maroon)] text-[var(--color-maroon)]' }}">
                            এই প্ল্যানে শুরু করুন
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <footer class="border-t border-[var(--color-line)] px-6 py-10 text-center text-sm text-[var(--color-ink-muted)]">
        <p class="font-serif-bn text-lg text-[var(--color-maroon)]">EDUTION</p>
        <p class="mt-2">© {{ date('Y') }} EDUTION. সর্বস্বত্ব সংরক্ষিত।</p>
    </footer>
</body>
</html>
