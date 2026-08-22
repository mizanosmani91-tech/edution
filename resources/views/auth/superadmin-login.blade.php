<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="manifest" href="/manifest.webmanifest">
<meta name="theme-color" content="#5C1A2B">
<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
    }
</script>
<title>সুপার এডমিন লগইন — EDUTION</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tiro+Bangla:ital@0;1&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
<style>
  :root{
    --ink-bg:#17151A; --ink-bg-deep:#0E0D10;
    --gold:#C9A227; --gold-light:#E7C767;
    --panel:#221F26; --panel-line:rgba(231,199,103,.14);
    --text:#EDE7DA; --text-muted:#9A93A0; --text-soft:#6B6572;
    --good:#3E9C74; --bad:#D2604A;
  }
  *{box-sizing:border-box;}
  html,body{margin:0;padding:0;}
  body{
    min-height:100vh; font-family:'Hind Siliguri',sans-serif; color:var(--text);
    background:
      radial-gradient(900px 500px at 15% 0%, rgba(201,162,39,.08), transparent 60%),
      radial-gradient(700px 500px at 90% 100%, rgba(201,162,39,.05), transparent 55%),
      var(--ink-bg-deep);
    display:flex; align-items:center; justify-content:center; padding:28px 16px;
    position:relative; overflow:hidden;
  }
  body::before{
    content:""; position:absolute; inset:0;
    background-image:
      linear-gradient(rgba(255,255,255,.02) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,.02) 1px, transparent 1px);
    background-size:36px 36px; pointer-events:none;
  }

  .card{
    position:relative; z-index:1; width:100%; max-width:420px;
    background:var(--panel); border:1px solid var(--panel-line); border-radius:20px;
    padding:38px 34px 32px; box-shadow:0 30px 70px -20px rgba(0,0,0,.6);
  }

  .badge-row{ display:flex; justify-content:center; margin-bottom:20px; }
  .shield{
    width:60px;height:60px;border-radius:16px;
    background:linear-gradient(150deg, rgba(201,162,39,.18), rgba(201,162,39,.04));
    border:1.5px solid rgba(201,162,39,.35);
    display:flex; align-items:center; justify-content:center;
  }
  .shield svg{ width:28px;height:28px; color:var(--gold-light); }

  h1{ font-family:'Tiro Bangla',serif; font-size:23px; text-align:center; margin:0 0 6px; color:var(--text); }
  .sub{ text-align:center; font-size:12.5px; color:var(--text-muted); margin:0 0 28px; line-height:1.7; }
  .sub b{ color:var(--gold-light); }

  .field{ margin-bottom:16px; }
  .field label{ display:block; font-size:12px; font-weight:700; color:var(--text-muted); margin-bottom:7px; }
  .input-shell{
    display:flex; align-items:center; gap:9px;
    background:rgba(255,255,255,.03); border:1.5px solid rgba(255,255,255,.08); border-radius:11px;
    padding:0 13px; transition:border-color .15s ease, box-shadow .15s ease;
  }
  .input-shell:focus-within{ border-color:var(--gold); box-shadow:0 0 0 3px rgba(201,162,39,.12); }
  .input-shell svg{ width:16px;height:16px; color:var(--text-soft); flex-shrink:0; }
  .input-shell input{
    border:0; outline:0; background:transparent; width:100%;
    font-family:'Hind Siliguri',sans-serif; font-size:14px; color:var(--text); padding:12px 2px;
  }
  .input-shell input::placeholder{ color:var(--text-soft); }
  .toggle-eye{ background:none; border:0; cursor:pointer; color:var(--text-soft); display:flex; padding:4px; }
  .toggle-eye:hover{ color:var(--text); }

  .row-between{ display:flex; align-items:center; justify-content:space-between; margin:2px 0 22px; font-size:12.5px; }
  .remember{ display:flex; align-items:center; gap:7px; color:var(--text-muted); }
  .remember input{ accent-color:var(--gold); width:15px;height:15px; }
  .forgot{ color:var(--gold-light); font-weight:600; text-decoration:none; }
  .forgot:hover{ text-decoration:underline; }

  .btn-primary{
    width:100%; border:0; border-radius:11px; padding:13px 16px;
    background:linear-gradient(180deg, var(--gold-light), var(--gold)); color:#241C08;
    font-family:'Hind Siliguri',sans-serif; font-weight:700; font-size:15px; cursor:pointer;
    box-shadow:0 10px 22px -10px rgba(201,162,39,.5);
    display:flex; align-items:center; justify-content:center; gap:8px;
    transition:transform .12s ease;
  }
  .btn-primary:hover{ transform:translateY(-1px); }

  .error-note{
    display:flex; gap:10px; margin-bottom:18px; padding:12px 14px;
    background:rgba(210,96,74,.1); border:1px solid rgba(210,96,74,.3); border-radius:11px;
    font-size:12.5px; color:#E0A797; line-height:1.7;
  }
  .error-note svg{ width:16px;height:16px; flex-shrink:0; margin-top:1px; }

  .security-note{
    display:flex; gap:10px; margin-top:22px; padding:12px 14px;
    background:rgba(210,96,74,.08); border:1px solid rgba(210,96,74,.25); border-radius:11px;
    font-size:11.5px; color:#E0A797; line-height:1.7;
  }
  .security-note svg{ width:16px;height:16px; flex-shrink:0; margin-top:1px; }

  .back-link{ text-align:center; margin-top:22px; font-size:12.5px; }
  .back-link a{ color:var(--text-muted); text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
  .back-link a:hover{ color:var(--text); }
  .back-link svg{ width:14px;height:14px; }
</style>
</head>
<body>

<div class="card">

  <div class="badge-row">
    <div class="shield">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 2 4 5.5V11c0 5.2 3.4 9.6 8 11 4.6-1.4 8-5.8 8-11V5.5L12 2Z"/><path d="m9 12 2 2 4-4.5"/></svg>
    </div>
  </div>

  <h1>সুপার এডমিন লগইন</h1>
  <p class="sub">এই এলাকাটি শুধুমাত্র <b>EDUTION</b> প্ল্যাটফর্ম নিয়ন্ত্রণকারী টিমের জন্য সংরক্ষিত</p>

  @if ($errors->any())
    <div class="error-note">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 9v4"/><circle cx="12" cy="16" r=".4" fill="currentColor"/><path d="M10.3 4.5 2.6 18a1.8 1.8 0 0 0 1.6 2.7h15.6a1.8 1.8 0 0 0 1.6-2.7L13.7 4.5a1.8 1.8 0 0 0-3.4 0Z"/></svg>
      {{ $errors->first() }}
    </div>
  @endif

  <form method="POST" action="{{ route('superadmin.login.store') }}">
    @csrf
    <div class="field">
      <label>ইমেইল</label>
      <div class="input-shell">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg>
        <input type="email" name="email" value="{{ old('email') }}" placeholder="admin@edution.xyz" required autofocus>
      </div>
    </div>

    <div class="field" x-data="{ show:false }">
      <label>পাসওয়ার্ড</label>
      <div class="input-shell">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="5" y="10.5" width="14" height="9" rx="2"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3"/></svg>
        <input :type="show ? 'text' : 'password'" name="password" placeholder="••••••••" required>
        <button type="button" class="toggle-eye" @click="show = !show">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" width="17" height="17"><path d="M1.5 12S5 5 12 5s10.5 7 10.5 7-3.5 7-10.5 7S1.5 12 1.5 12Z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>
    </div>

    <div class="row-between">
      <label class="remember"><input type="checkbox" name="remember" value="1">এই ডিভাইস মনে রাখুন</label>
      <a href="{{ route('superadmin.password.forgot') }}" class="forgot">পাসওয়ার্ড ভুলে গেছেন?</a>
    </div>

    <button type="submit" class="btn-primary">
      নিরাপদে প্রবেশ করুন
      <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
    </button>
  </form>

  <div class="security-note">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 9v4"/><circle cx="12" cy="16" r=".4" fill="currentColor"/><path d="M10.3 4.5 2.6 18a1.8 1.8 0 0 0 1.6 2.7h15.6a1.8 1.8 0 0 0 1.6-2.7L13.7 4.5a1.8 1.8 0 0 0-3.4 0Z"/></svg>
    সকল লগইন কার্যক্রম নিরীক্ষণ ও লগ করা হয়। অননুমোদিত প্রবেশের চেষ্টা রিপোর্ট করা হবে।
  </div>

  <div class="back-link">
    <a href="https://edution.xyz"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7"/></svg>মূল সাইটে ফিরে যান</a>
  </div>

</div>

</body>
</html>
