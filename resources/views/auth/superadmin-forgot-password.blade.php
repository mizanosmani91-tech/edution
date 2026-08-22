<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>পাসওয়ার্ড ভুলে গেছেন — EDUTION সুপার এডমিন</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tiro+Bangla:ital@0;1&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{
    --ink-bg:#17151A; --ink-bg-deep:#0E0D10;
    --gold:#F59E0B; --gold-light:#FBBF24;
    --panel:#221F26; --panel-line:rgba(251,191,36,.14);
    --text:#EDE7DA; --text-muted:#9A93A0; --text-soft:#6B6572;
    --good:#3E9C74; --bad:#D2604A;
  }
  *{box-sizing:border-box;}
  html,body{margin:0;padding:0;}
  body{
    min-height:100vh; font-family:'Hind Siliguri',sans-serif; color:var(--text);
    background:
      radial-gradient(900px 500px at 15% 0%, rgba(245,158,11,.08), transparent 60%),
      radial-gradient(700px 500px at 90% 100%, rgba(245,158,11,.05), transparent 55%),
      var(--ink-bg-deep);
    display:flex; align-items:center; justify-content:center; padding:28px 16px;
  }
  .card{
    width:100%; max-width:420px;
    background:var(--panel); border:1px solid var(--panel-line); border-radius:20px;
    padding:38px 34px 32px; box-shadow:0 30px 70px -20px rgba(0,0,0,.6);
  }
  .badge-row{ display:flex; justify-content:center; margin-bottom:20px; }
  .shield{
    width:60px;height:60px;border-radius:16px;
    background:linear-gradient(150deg, rgba(245,158,11,.18), rgba(245,158,11,.04));
    border:1.5px solid rgba(245,158,11,.35);
    display:flex; align-items:center; justify-content:center;
  }
  .shield svg{ width:28px;height:28px; color:var(--gold-light); }
  h1{ font-family:'Tiro Bangla',serif; font-size:22px; text-align:center; margin:0 0 6px; color:var(--text); }
  .sub{ text-align:center; font-size:12.5px; color:var(--text-muted); margin:0 0 26px; line-height:1.7; }
  .field{ margin-bottom:16px; }
  .field label{ display:block; font-size:12px; font-weight:700; color:var(--text-muted); margin-bottom:7px; }
  .field input{
    width:100%; background:rgba(255,255,255,.03); border:1.5px solid rgba(255,255,255,.08); border-radius:11px;
    padding:12px 13px; outline:0; font-family:'Hind Siliguri',sans-serif; font-size:14px; color:var(--text);
  }
  .field input:focus{ border-color:var(--gold); box-shadow:0 0 0 3px rgba(245,158,11,.12); }
  .btn-primary{
    width:100%; border:0; border-radius:11px; padding:13px 16px; margin-top:6px;
    background:linear-gradient(180deg, var(--gold-light), var(--gold)); color:#241C08;
    font-family:'Hind Siliguri',sans-serif; font-weight:700; font-size:15px; cursor:pointer;
  }
  .btn-secondary{
    width:100%; border:0; background:none; margin-top:10px; text-align:center;
    font-size:12.5px; font-weight:600; color:var(--text-muted); cursor:pointer;
  }
  .btn-secondary:hover{ color:var(--gold-light); }
  .error-note{
    margin-bottom:16px; padding:12px 14px;
    background:rgba(210,96,74,.1); border:1px solid rgba(210,96,74,.3); border-radius:11px;
    font-size:12.5px; color:#E0A797; line-height:1.7;
  }
  .info-note{
    margin-bottom:16px; padding:12px 14px;
    background:rgba(62,156,116,.1); border:1px solid rgba(62,156,116,.3); border-radius:11px;
    font-size:12.5px; color:var(--good); line-height:1.7; text-align:center;
  }
  .hidden{ display:none; }
  .back-link{ text-align:center; margin-top:22px; font-size:12.5px; }
  .back-link a{ color:var(--text-muted); text-decoration:none; }
  .back-link a:hover{ color:var(--text); }
</style>
</head>
<body>
<div class="card">
  <div class="badge-row">
    <div class="shield">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="5" y="10.5" width="14" height="9" rx="2"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3"/></svg>
    </div>
  </div>

  <div id="formError" class="error-note hidden"></div>
  <div id="infoMessage" class="info-note hidden"></div>

  <div id="stepEmail">
    <h1>পাসওয়ার্ড ভুলে গেছেন?</h1>
    <p class="sub">আপনার সুপার এডমিন ইমেইল দিন — নিবন্ধিত মোবাইল নম্বরে একটি কোড পাঠানো হবে।</p>
    <div class="field"><label>ইমেইল</label><input type="email" id="emailInput" placeholder="admin@edution.xyz" autofocus></div>
    <button type="button" id="sendCodeBtn" class="btn-primary">কোড পাঠান</button>
  </div>

  <div id="stepOtp" class="hidden">
    <h1>কোড ও নতুন পাসওয়ার্ড দিন</h1>
    <p class="sub"><span id="maskedPhoneText"></span> নম্বরে ৬ সংখ্যার কোড পাঠানো হয়েছে।</p>
    <div class="field"><label>যাচাই কোড</label><input type="text" inputmode="numeric" maxlength="6" id="codeInput" placeholder="000000" style="text-align:center;letter-spacing:.3em;font-size:18px;"></div>
    <div class="field"><label>নতুন পাসওয়ার্ড</label><input type="password" id="passwordInput" placeholder="কমপক্ষে ৮ ক্যারেক্টার"></div>
    <div class="field"><label>পাসওয়ার্ড আবার লিখুন</label><input type="password" id="passwordConfirmInput" placeholder="পাসওয়ার্ড নিশ্চিত করুন"></div>
    <button type="button" id="resetBtn" class="btn-primary">পাসওয়ার্ড রিসেট করুন</button>
    <button type="button" id="resendBtn" class="btn-secondary">কোড আবার পাঠান</button>
  </div>

  <div id="stepDone" class="hidden" style="text-align:center;">
    <h1>পাসওয়ার্ড পরিবর্তন হয়েছে</h1>
    <p class="sub">নতুন পাসওয়ার্ড দিয়ে এখন লগইন করুন।</p>
    <a href="{{ route('superadmin.login') }}" class="btn-primary" style="display:block;text-decoration:none;text-align:center;">লগইন পেজে যান</a>
  </div>

  <div id="backLink" class="back-link">
    <a href="{{ route('superadmin.login') }}">← লগইন পেজে ফিরে যান</a>
  </div>
</div>

<script>
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
  let currentUserId = null;

  const els = {
    formError: document.getElementById('formError'),
    infoMessage: document.getElementById('infoMessage'),
    stepEmail: document.getElementById('stepEmail'),
    stepOtp: document.getElementById('stepOtp'),
    stepDone: document.getElementById('stepDone'),
    backLink: document.getElementById('backLink'),
    emailInput: document.getElementById('emailInput'),
    sendCodeBtn: document.getElementById('sendCodeBtn'),
    maskedPhoneText: document.getElementById('maskedPhoneText'),
    codeInput: document.getElementById('codeInput'),
    passwordInput: document.getElementById('passwordInput'),
    passwordConfirmInput: document.getElementById('passwordConfirmInput'),
    resetBtn: document.getElementById('resetBtn'),
    resendBtn: document.getElementById('resendBtn'),
  };

  function showError(msg){ els.infoMessage.classList.add('hidden'); els.formError.textContent = msg; els.formError.classList.remove('hidden'); }
  function showInfo(msg){ els.formError.classList.add('hidden'); els.infoMessage.textContent = msg; els.infoMessage.classList.remove('hidden'); }
  function clearMessages(){ els.formError.classList.add('hidden'); els.infoMessage.classList.add('hidden'); }

  async function postJson(url, body){
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
      const { ok, data } = await postJson("{{ route('superadmin.password.forgot.send') }}", { email });
      if (ok) {
        currentUserId = data.userId;
        els.maskedPhoneText.textContent = data.maskedPhone || '';
        els.stepEmail.classList.add('hidden');
        els.stepOtp.classList.remove('hidden');
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
      const { ok, data } = await postJson("{{ route('superadmin.password.forgot.send') }}", { email: els.emailInput.value.trim() });
      showInfo(ok ? 'আবার কোড পাঠানো হয়েছে।' : (data.message || 'পাঠানো যায়নি'));
    } finally {
      els.resendBtn.disabled = false;
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
      const { ok, data } = await postJson("{{ route('superadmin.password.forgot.reset') }}", { userId: currentUserId, code, password, password_confirmation });
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
