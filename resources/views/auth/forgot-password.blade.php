<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        @if ($institution?->logo_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($institution->logo_path) }}" alt="{{ $institution->name }}" class="h-full w-full object-cover">
        @else
            <svg viewBox="0 0 24 24" fill="none" stroke="var(--color-gold-light)" stroke-width="1.6" class="h-7 w-7">
                <path d="M12 9v4"/><circle cx="12" cy="16" r=".4" fill="var(--color-gold-light)"/>
                <path d="M10.3 4.5 2.6 18a1.8 1.8 0 0 0 1.6 2.7h15.6a1.8 1.8 0 0 0 1.6-2.7L13.7 4.5a1.8 1.8 0 0 0-3.4 0Z"/>
            </svg>
        @endif
    </div>

    <div id="formError" class="mb-4 hidden rounded-lg border border-[rgba(239,68,68,.3)] bg-[rgba(239,68,68,.08)] px-3.5 py-2.5 text-[13px] text-[var(--color-bad)]"></div>
    <div id="infoMessage" class="mb-4 hidden rounded-lg border border-[rgba(16,185,129,.3)] bg-[rgba(16,185,129,.08)] px-3.5 py-2.5 text-center text-[12.5px] text-[var(--color-good)]"></div>

    {{-- ধাপ ১: ইমেইল --}}
    <div id="stepEmail">
        <h1 class="text-center text-[21px] font-bold text-[var(--color-ink)]">পাসওয়ার্ড ভুলে গেছেন?</h1>
        <p class="mt-1.5 text-center text-[13.5px] text-[var(--color-ink-muted)]">আপনার লগইন ইমেইল দিন — সেই ঠিকানায় একটি যাচাই কোড পাঠানো হবে।</p>

        <div class="mt-6 space-y-4">
            <div>
                <label class="mb-1.5 block text-[12.5px] font-semibold text-[var(--color-ink)]">ইমেইল</label>
                <input type="email" id="emailInput" class="w-full rounded-lg border border-[var(--color-line)] px-3.5 py-2.5 text-[14px] outline-none focus:border-[var(--color-maroon)]" placeholder="you@example.com" autofocus>
            </div>
            <button type="button" id="sendCodeBtn" class="w-full rounded-lg py-3 text-[14.5px] font-bold text-[var(--color-ink)]" style="background:linear-gradient(90deg, var(--color-gold-light), var(--color-gold));">
                কোড পাঠান
            </button>
        </div>
    </div>

    {{-- ধাপ ২: OTP + নতুন পাসওয়ার্ড --}}
    <div id="stepOtp" class="hidden">
        <h1 class="text-center text-[21px] font-bold text-[var(--color-ink)]">কোড ও নতুন পাসওয়ার্ড দিন</h1>
        <p class="mt-1.5 text-center text-[13.5px] text-[var(--color-ink-muted)]">
            <span id="maskedEmailText"></span> ঠিকানায় একটি ৬ সংখ্যার কোড পাঠানো হয়েছে।
        </p>

        <div class="mt-6 space-y-4">
            <div>
                <label class="mb-1.5 block text-[12.5px] font-semibold text-[var(--color-ink)]">যাচাই কোড</label>
                <input type="text" inputmode="numeric" maxlength="6" id="codeInput" class="w-full rounded-lg border border-[var(--color-line)] px-3.5 py-2.5 text-center text-[18px] tracking-[0.3em] outline-none focus:border-[var(--color-maroon)]" placeholder="000000">
            </div>
            <div>
                <label class="mb-1.5 block text-[12.5px] font-semibold text-[var(--color-ink)]">নতুন পাসওয়ার্ড</label>
                <input type="password" id="passwordInput" class="w-full rounded-lg border border-[var(--color-line)] px-3.5 py-2.5 text-[14px] outline-none focus:border-[var(--color-maroon)]" placeholder="কমপক্ষে ৮ ক্যারেক্টার">
            </div>
            <div>
                <label class="mb-1.5 block text-[12.5px] font-semibold text-[var(--color-ink)]">পাসওয়ার্ড আবার লিখুন</label>
                <input type="password" id="passwordConfirmInput" class="w-full rounded-lg border border-[var(--color-line)] px-3.5 py-2.5 text-[14px] outline-none focus:border-[var(--color-maroon)]" placeholder="পাসওয়ার্ড নিশ্চিত করুন">
            </div>

            <button type="button" id="resetBtn" class="w-full rounded-lg py-3 text-[14.5px] font-bold text-[var(--color-ink)]" style="background:linear-gradient(90deg, var(--color-gold-light), var(--color-gold));">
                পাসওয়ার্ড রিসেট করুন
            </button>
            <button type="button" id="resendBtn" class="w-full text-center text-[12.5px] font-semibold text-[var(--color-ink-muted)] hover:text-[var(--color-maroon)]">
                ইমেইলে কোড আবার পাঠান
            </button>
            <button type="button" id="smsBackupBtn" class="hidden w-full text-center text-[12.5px] font-semibold text-[var(--color-maroon)] hover:underline">
                ইমেইলে না পেলে ফোনে SMS পাঠান
            </button>
        </div>
    </div>

    {{-- ধাপ ৩: সফল --}}
    <div id="stepDone" class="hidden text-center">
        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full" style="background:rgba(16,185,129,.12);">
            <svg viewBox="0 0 24 24" fill="none" stroke="var(--color-good)" stroke-width="1.8" class="h-6 w-6"><path d="M20 6 9 17l-5-5"/></svg>
        </div>
        <h1 class="text-[21px] font-bold text-[var(--color-ink)]">পাসওয়ার্ড পরিবর্তন হয়েছে</h1>
        <p class="mt-1.5 text-[13.5px] text-[var(--color-ink-muted)]">নতুন পাসওয়ার্ড দিয়ে এখন লগইন করুন।</p>
        <a href="{{ route('login') }}" class="mt-6 inline-block w-full rounded-lg py-3 text-[14.5px] font-bold text-[var(--color-ink)]" style="background:linear-gradient(90deg, var(--color-gold-light), var(--color-gold));">
            লগইন পেজে যান
        </a>
    </div>

    <div id="backLink" class="mt-5 text-center text-[12.5px]">
        <a href="{{ route('login') }}" class="text-[var(--color-ink-muted)] hover:text-[var(--color-maroon)]">← লগইন পেজে ফিরে যান</a>
    </div>
</div>

<script>
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
  let currentUserId = null;
  let smsAvailable = false;

  const els = {
    formError: document.getElementById('formError'),
    infoMessage: document.getElementById('infoMessage'),
    stepEmail: document.getElementById('stepEmail'),
    stepOtp: document.getElementById('stepOtp'),
    stepDone: document.getElementById('stepDone'),
    backLink: document.getElementById('backLink'),
    emailInput: document.getElementById('emailInput'),
    sendCodeBtn: document.getElementById('sendCodeBtn'),
    maskedEmailText: document.getElementById('maskedEmailText'),
    codeInput: document.getElementById('codeInput'),
    passwordInput: document.getElementById('passwordInput'),
    passwordConfirmInput: document.getElementById('passwordConfirmInput'),
    resetBtn: document.getElementById('resetBtn'),
    resendBtn: document.getElementById('resendBtn'),
    smsBackupBtn: document.getElementById('smsBackupBtn'),
  };

  function showError(msg) {
    els.infoMessage.classList.add('hidden');
    els.formError.textContent = msg;
    els.formError.classList.remove('hidden');
  }

  function showInfo(msg) {
    els.formError.classList.add('hidden');
    els.infoMessage.textContent = msg;
    els.infoMessage.classList.remove('hidden');
  }

  function clearMessages() {
    els.formError.classList.add('hidden');
    els.infoMessage.classList.add('hidden');
  }

  function goToOtpStep() {
    els.stepEmail.classList.add('hidden');
    els.stepOtp.classList.remove('hidden');
    els.smsBackupBtn.classList.toggle('hidden', !smsAvailable);
  }

  async function postJson(url, body) {
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
      body: JSON.stringify(body),
    });
    const data = await res.json().catch(() => ({}));
    return { ok: res.ok, data };
  }

  els.sendCodeBtn.addEventListener('click', async () => {
    clearMessages();
    const email = els.emailInput.value.trim();
    if (!email) { showError('ইমেইল লিখুন'); return; }

    els.sendCodeBtn.disabled = true;
    try {
      const { ok, data } = await postJson("{{ route('password.forgot.send') }}", { email });
      if (ok) {
        currentUserId = data.userId;
        smsAvailable = !!data.smsAvailable;
        els.maskedEmailText.textContent = data.maskedEmail || '';
        goToOtpStep();
      } else {
        showError(data.message || 'কোড পাঠানো যায়নি');
      }
    } catch (e) {
      showError('নেটওয়ার্ক সমস্যা, আবার চেষ্টা করুন');
    } finally {
      els.sendCodeBtn.disabled = false;
    }
  });

  els.resendBtn.addEventListener('click', async () => {
    clearMessages();
    els.resendBtn.disabled = true;
    try {
      const { ok, data } = await postJson("{{ route('password.forgot.send') }}", { email: els.emailInput.value.trim() });
      if (ok) {
        showInfo('আবার কোড পাঠানো হয়েছে।');
      } else {
        showError(data.message || 'পাঠানো যায়নি');
      }
    } finally {
      els.resendBtn.disabled = false;
    }
  });

  els.smsBackupBtn.addEventListener('click', async () => {
    clearMessages();
    if (!currentUserId) return;
    els.smsBackupBtn.disabled = true;
    try {
      const { ok, data } = await postJson("{{ route('password.forgot.sms') }}", { userId: currentUserId });
      showInfo(ok ? (data.message || 'SMS পাঠানো হয়েছে') : (data.message || 'পাঠানো যায়নি'));
    } finally {
      els.smsBackupBtn.disabled = false;
    }
  });

  els.resetBtn.addEventListener('click', async () => {
    clearMessages();
    const code = els.codeInput.value.trim();
    const password = els.passwordInput.value;
    const password_confirmation = els.passwordConfirmInput.value;

    if (!code || code.length !== 6) { showError('৬ সংখ্যার কোড দিন'); return; }
    if (!password || password.length < 8) { showError('পাসওয়ার্ড কমপক্ষে ৮ ক্যারেক্টার হতে হবে'); return; }
    if (password !== password_confirmation) { showError('দুটো পাসওয়ার্ড মিলছে না'); return; }

    els.resetBtn.disabled = true;
    try {
      const { ok, data } = await postJson("{{ route('password.forgot.reset') }}", { userId: currentUserId, code, password, password_confirmation });
      if (ok) {
        els.stepOtp.classList.add('hidden');
        els.backLink.classList.add('hidden');
        els.stepDone.classList.remove('hidden');
      } else {
        showError(data.message || 'রিসেট করা যায়নি');
      }
    } catch (e) {
      showError('নেটওয়ার্ক সমস্যা, আবার চেষ্টা করুন');
    } finally {
      els.resetBtn.disabled = false;
    }
  });
</script>
</body>
</html>
