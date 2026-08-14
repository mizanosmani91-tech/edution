<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Superadmin — EDUTION</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-[radial-gradient(120%_140%_at_8%_0%,#6E2136_0%,var(--color-maroon)_45%,var(--color-maroon-deep)_100%)] p-4">
    <div class="w-full max-w-sm rounded-2xl bg-[var(--color-paper)] p-8 shadow-[0_30px_60px_-20px_rgba(0,0,0,.5)]">
        <div class="mb-6 text-center">
            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full border border-[var(--color-maroon)]">
                <svg viewBox="0 0 24 24" fill="none" stroke="var(--color-maroon)" stroke-width="1.6" class="h-6 w-6"><path d="M4 6.5c2.8-1.4 5.6-1.4 8 0v11c-2.4-1.4-5.2-1.4-8 0v-11Z"/><path d="M20 6.5c-2.8-1.4-5.6-1.4-8 0v11c2.4-1.4 5.2-1.4 8 0v-11Z"/></svg>
            </div>
            <p class="font-serif-bn text-xl text-[var(--color-maroon)]">EDUTION</p>
            <p class="text-xs uppercase tracking-widest text-[var(--color-ink-muted)]">Superadmin Panel</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-[var(--color-bad)]/30 bg-[var(--color-bad)]/10 px-4 py-3 text-sm text-[var(--color-bad)]">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('superadmin.login.store') }}">
            @csrf
            <div class="mb-4">
                <label class="mb-1.5 block text-sm font-medium text-[var(--color-ink)]">ইমেইল</label>
                <input type="email" name="email" required
                    class="w-full rounded-lg border border-[var(--color-line)] bg-white px-4 py-2.5 text-[15px] outline-none focus:border-[var(--color-gold)] focus:ring-2 focus:ring-[var(--color-gold)]/20">
            </div>
            <div class="mb-5">
                <label class="mb-1.5 block text-sm font-medium text-[var(--color-ink)]">পাসওয়ার্ড</label>
                <input type="password" name="password" required
                    class="w-full rounded-lg border border-[var(--color-line)] bg-white px-4 py-2.5 text-[15px] outline-none focus:border-[var(--color-gold)] focus:ring-2 focus:ring-[var(--color-gold)]/20">
            </div>
            <button type="submit"
                class="w-full rounded-lg bg-[var(--color-maroon)] py-3 font-medium text-white hover:bg-[var(--color-maroon-deep)]">
                প্রবেশ করুন
            </button>
        </form>
    </div>
</body>
</html>
