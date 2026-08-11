<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>প্রতিষ্ঠান রেজিস্ট্রেশন — বিদ্যাপঞ্জি</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-[radial-gradient(1200px_700px_at_15%_10%,#EFE7D3_0%,transparent_60%),radial-gradient(1000px_600px_at_90%_90%,#E7DEC5_0%,transparent_55%),#E5DCC5] p-4">
    <div class="w-full max-w-md rounded-[22px] bg-white p-8 shadow-[0_30px_60px_-20px_rgba(60,30,20,.35)]">
        <div class="mb-6 text-center">
            <p class="font-serif-bn text-2xl text-[var(--color-maroon)]">বিদ্যাপঞ্জি</p>
            <h1 class="mt-3 text-xl text-[var(--color-ink)]">প্রতিষ্ঠান রেজিস্ট্রেশন</h1>
            <p class="mt-1 text-sm text-[var(--color-ink-muted)]">আবেদন যাচাই করে আমরা লগইন তথ্য ইমেইলে পাঠাব।</p>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-[var(--color-good)]/30 bg-[var(--color-good)]/10 px-4 py-3 text-sm text-[var(--color-good)]">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-[var(--color-bad)]/30 bg-[var(--color-bad)]/10 px-4 py-3 text-sm text-[var(--color-bad)]">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}">
            @csrf
            <div class="mb-4">
                <label class="mb-1.5 block text-sm font-medium text-[var(--color-ink)]">প্রতিষ্ঠানের নাম</label>
                <input type="text" name="name" required value="{{ old('name') }}"
                    class="w-full rounded-lg border border-[var(--color-line)] px-4 py-2.5 text-[15px] outline-none focus:border-[var(--color-gold)] focus:ring-2 focus:ring-[var(--color-gold)]/20">
            </div>
            <div class="mb-4">
                <label class="mb-1.5 block text-sm font-medium text-[var(--color-ink)]">ইমেইল</label>
                <input type="email" name="email" required value="{{ old('email') }}"
                    class="w-full rounded-lg border border-[var(--color-line)] px-4 py-2.5 text-[15px] outline-none focus:border-[var(--color-gold)] focus:ring-2 focus:ring-[var(--color-gold)]/20">
            </div>
            <div class="mb-4">
                <label class="mb-1.5 block text-sm font-medium text-[var(--color-ink)]">মোবাইল নম্বর</label>
                <input type="text" name="phone" required value="{{ old('phone') }}"
                    class="w-full rounded-lg border border-[var(--color-line)] px-4 py-2.5 text-[15px] outline-none focus:border-[var(--color-gold)] focus:ring-2 focus:ring-[var(--color-gold)]/20">
            </div>
            <div class="mb-5">
                <label class="mb-1.5 block text-sm font-medium text-[var(--color-ink)]">ঠিকানা (ঐচ্ছিক)</label>
                <textarea name="address" rows="2"
                    class="w-full rounded-lg border border-[var(--color-line)] px-4 py-2.5 text-[15px] outline-none focus:border-[var(--color-gold)] focus:ring-2 focus:ring-[var(--color-gold)]/20">{{ old('address') }}</textarea>
            </div>
            <button type="submit"
                class="w-full rounded-lg bg-[var(--color-maroon)] py-3 font-medium text-white hover:bg-[var(--color-maroon-deep)]">
                আবেদন জমা দিন
            </button>
        </form>

        <div class="mt-5 text-center text-sm text-[var(--color-ink-muted)]">
            আগে থেকেই অ্যাকাউন্ট আছে? <a href="{{ route('login') }}" class="font-bold text-[var(--color-maroon)] hover:underline">লগইন করুন</a>
        </div>
    </div>
</body>
</html>
