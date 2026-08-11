<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>বিদ্যাপঞ্জি — স্কুল ব্যবস্থাপনা সিস্টেম</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-[var(--color-paper)]">
    <header class="flex items-center justify-between px-6 py-5">
        <p class="font-serif-bn text-2xl text-[var(--color-maroon)]">বিদ্যাপঞ্জি</p>
        <div class="flex gap-3">
            <a href="{{ route('login') }}" class="rounded-lg border border-[var(--color-maroon)] px-5 py-2 text-sm font-medium text-[var(--color-maroon)]">লগইন</a>
            <a href="{{ route('register') }}" class="rounded-lg bg-[var(--color-maroon)] px-5 py-2 text-sm font-medium text-white">রেজিস্ট্রেশন করুন</a>
        </div>
    </header>

    <main class="mx-auto max-w-3xl px-6 py-24 text-center">
        <h1 class="font-serif-bn text-4xl text-[var(--color-ink)] md:text-5xl">
            স্কুল, মাদরাসা ও কিন্ডারগার্টেনের জন্য<br>সম্পূর্ণ ডিজিটাল ব্যবস্থাপনা
        </h1>
        <p class="mx-auto mt-6 max-w-xl text-lg text-[var(--color-ink-muted)]">
            ভর্তি, হাজিরা, ফলাফল, ফি — সবকিছু এক জায়গায়। ৫০০+ প্রতিষ্ঠান প্রতিদিন বিদ্যাপঞ্জি ব্যবহার করছে।
        </p>
        <a href="{{ route('register') }}"
           class="mt-10 inline-block rounded-lg bg-[var(--color-maroon)] px-8 py-3.5 font-medium text-white hover:bg-[var(--color-maroon-deep)]">
            বিনামূল্যে শুরু করুন →
        </a>
    </main>
</body>
</html>
