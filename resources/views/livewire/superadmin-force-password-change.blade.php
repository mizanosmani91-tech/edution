<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>পাসওয়ার্ড পরিবর্তন — EDUTION সুপার এডমিন</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tiro+Bangla:ital@0;1&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
@livewireStyles
<style>
  :root{
    --ink-bg:#17151A; --ink-bg-deep:#0E0D10;
    --gold:#F59E0B; --gold-light:#FBBF24;
    --panel:#221F26; --panel-line:rgba(251,191,36,.14);
    --text:#EDE7DA; --text-muted:#9A93A0; --text-soft:#6B6572;
    --bad:#D2604A;
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
  .field .err{ margin-top:6px; font-size:12px; color:var(--bad); }
  .btn-primary{
    width:100%; border:0; border-radius:11px; padding:13px 16px; margin-top:6px;
    background:linear-gradient(180deg, var(--gold-light), var(--gold)); color:#241C08;
    font-family:'Hind Siliguri',sans-serif; font-weight:700; font-size:15px; cursor:pointer;
  }
</style>
</head>
<body>
<div class="card">
  <div class="badge-row">
    <div class="shield">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="5" y="10.5" width="14" height="9" rx="2"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3"/></svg>
    </div>
  </div>
  <h1>নতুন পাসওয়ার্ড সেট করুন</h1>
  <p class="sub">নিরাপত্তার জন্য প্রথমবার লগইনের সময় নতুন পাসওয়ার্ড দিতে হবে।</p>

  <form wire:submit="save">
    <div class="field">
      <label>নতুন পাসওয়ার্ড</label>
      <input type="password" wire:model="password" placeholder="কমপক্ষে ৮ ক্যারেক্টার" autofocus>
      @error('password') <p class="err">{{ $message }}</p> @enderror
    </div>
    <div class="field">
      <label>পাসওয়ার্ড আবার লিখুন</label>
      <input type="password" wire:model="password_confirmation" placeholder="পাসওয়ার্ড নিশ্চিত করুন">
    </div>
    <button type="submit" class="btn-primary">সংরক্ষণ করে চালিয়ে যান</button>
  </form>
</div>
@livewireScripts
</body>
</html>
