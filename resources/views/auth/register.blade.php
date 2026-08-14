<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>প্রতিষ্ঠান রেজিস্ট্রেশন — EDUTION</title>
    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
</head>
<body class="flex min-h-screen items-center justify-center bg-[radial-gradient(1200px_700px_at_15%_10%,#EFE7D3_0%,transparent_60%),radial-gradient(1000px_600px_at_90%_90%,#E7DEC5_0%,transparent_55%),#E5DCC5] p-4"
      x-data="{ type: 'school', plan: '{{ old('plan', $selectedPlan ?? 'standard') }}' }">
    <div class="w-full max-w-lg rounded-[22px] bg-white p-8 shadow-[0_30px_60px_-20px_rgba(60,30,20,.35)]">
        <div class="mb-6 text-center">
            <p class="font-serif-bn text-2xl text-[var(--color-maroon)]">EDUTION</p>
            <h1 class="mt-3 text-xl text-[var(--color-ink)]">প্রতিষ্ঠান রেজিস্ট্রেশন</h1>
            <p class="mt-1 text-sm text-[var(--color-ink-muted)]">আবেদন যাচাই করে সুপার এডমিন আপনাকে একটি সিক্রেট কোড দেবেন — সেটি দিয়ে আপনার নিজস্ব সাবডোমেইনে লগইন করবেন।</p>
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
                <label class="mb-1.5 block text-sm font-medium text-[var(--color-ink)]">প্রতিষ্ঠানের ধরন</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach (['school' => 'স্কুল/কলেজ', 'madrasa' => 'মাদরাসা', 'kindergarten' => 'কিন্ডারগার্টেন'] as $val => $label)
                        <label class="cursor-pointer rounded-lg border px-2 py-2.5 text-center text-xs font-medium"
                               :class="type === '{{ $val }}' ? 'border-[var(--color-maroon)] bg-[var(--color-maroon)]/5 text-[var(--color-maroon)]' : 'border-[var(--color-line)] text-[var(--color-ink-muted)]'">
                            <input type="radio" name="institution_type" value="{{ $val }}" x-model="type" class="hidden" {{ old('institution_type') === $val ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="mb-4">
                <label class="mb-1.5 block text-sm font-medium text-[var(--color-ink)]">প্রতিষ্ঠানের নাম</label>
                <input type="text" name="name" required value="{{ old('name') }}"
                    class="w-full rounded-lg border border-[var(--color-line)] px-4 py-2.5 text-[15px] outline-none focus:border-[var(--color-gold)] focus:ring-2 focus:ring-[var(--color-gold)]/20">
            </div>
            <div class="mb-4">
                <label class="mb-1.5 block text-sm font-medium text-[var(--color-ink)]">এডমিনের ইমেইল <span class="text-[var(--color-ink-muted)] font-normal">(এই ইমেইল দিয়েই পরে লগইন করবেন)</span></label>
                <input type="email" name="email" required value="{{ old('email') }}"
                    class="w-full rounded-lg border border-[var(--color-line)] px-4 py-2.5 text-[15px] outline-none focus:border-[var(--color-gold)] focus:ring-2 focus:ring-[var(--color-gold)]/20">
            </div>
            <div class="mb-4">
                <label class="mb-1.5 block text-sm font-medium text-[var(--color-ink)]">মোবাইল নম্বর</label>
                <input type="text" name="phone" required value="{{ old('phone') }}"
                    class="w-full rounded-lg border border-[var(--color-line)] px-4 py-2.5 text-[15px] outline-none focus:border-[var(--color-gold)] focus:ring-2 focus:ring-[var(--color-gold)]/20">
            </div>
            <div class="mb-4">
                <label class="mb-1.5 block text-sm font-medium text-[var(--color-ink)]">আনুমানিক শিক্ষার্থী সংখ্যা (ঐচ্ছিক)</label>
                <select name="student_count_estimate"
                    class="w-full rounded-lg border border-[var(--color-line)] px-4 py-2.5 text-[15px] outline-none focus:border-[var(--color-gold)] focus:ring-2 focus:ring-[var(--color-gold)]/20">
                    <option value="">নির্বাচন করুন</option>
                    <option value="1-100" {{ old('student_count_estimate') === '1-100' ? 'selected' : '' }}>১–১০০</option>
                    <option value="101-500" {{ old('student_count_estimate') === '101-500' ? 'selected' : '' }}>১০১–৫০০</option>
                    <option value="500+" {{ old('student_count_estimate') === '500+' ? 'selected' : '' }}>৫০০+</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="mb-1.5 block text-sm font-medium text-[var(--color-ink)]">ঠিকানা (ঐচ্ছিক)</label>
                <textarea name="address" rows="2"
                    class="w-full rounded-lg border border-[var(--color-line)] px-4 py-2.5 text-[15px] outline-none focus:border-[var(--color-gold)] focus:ring-2 focus:ring-[var(--color-gold)]/20">{{ old('address') }}</textarea>
            </div>

            <div class="mb-5">
                <label class="mb-1.5 block text-sm font-medium text-[var(--color-ink)]">প্ল্যান</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach (['basic' => ['বেসিক', '৳১,৫০০'], 'standard' => ['স্ট্যান্ডার্ড', '৳৩,৫০০'], 'premium' => ['প্রিমিয়াম', '৳৬,৫০০']] as $val => [$label, $price])
                        <label class="cursor-pointer rounded-lg border px-2 py-2.5 text-center"
                               :class="plan === '{{ $val }}' ? 'border-[var(--color-gold)] bg-[var(--color-gold)]/10' : 'border-[var(--color-line)]'">
                            <input type="radio" name="plan" value="{{ $val }}" x-model="plan" class="hidden">
                            <div class="text-xs font-medium text-[var(--color-ink)]">{{ $label }}</div>
                            <div class="text-[11px] text-[var(--color-ink-muted)]">{{ $price }}/মাস</div>
                        </label>
                    @endforeach
                </div>
            </div>

            <button type="submit"
                class="w-full rounded-lg bg-[var(--color-maroon)] py-3 font-medium text-white hover:bg-[var(--color-maroon-deep)]">
                আবেদন জমা দিন
            </button>
        </form>

        <div class="mt-5 text-center text-sm text-[var(--color-ink-muted)]">
            আগে থেকেই অ্যাকাউন্ট আছে? <a href="{{ route('login') }}" class="font-bold text-[var(--color-maroon)] hover:underline">লগইন করুন</a>
        </div>
        <div class="mt-2 text-center text-xs text-[var(--color-ink-muted)]">
            শুধু ঘুরে দেখতে চান? <a href="#" onclick="event.preventDefault(); document.getElementById('demo-info').classList.toggle('hidden')" class="font-medium text-[var(--color-maroon)] hover:underline">ডেমো ক্রেডেনশিয়াল দেখুন</a>
        </div>
        <div id="demo-info" class="mt-3 hidden rounded-lg bg-[var(--color-paper)] p-3 text-center text-xs text-[var(--color-ink)]">
            ইমেইল: {{ \Database\Seeders\DemoSeeder::EMAIL }} · পাসওয়ার্ড: {{ \Database\Seeders\DemoSeeder::PASSWORD }}
        </div>
    </div>
</body>
</html>
