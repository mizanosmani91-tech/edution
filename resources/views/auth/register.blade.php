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
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>প্রতিষ্ঠান রেজিস্ট্রেশন — EDUTION</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tiro+Bangla:ital@0;1&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{
    --cover-maroon:#5C1A2B; --cover-maroon-deep:#3E1120;
    --gold:#C9A227; --gold-light:#E7C767;
    --paper:#F7F2E5; --paper-deep:#EFE7D3; --card:#FFFFFF;
    --ink:#2A2320; --ink-muted:#7A7061; --ink-soft:#AFA593;
    --line:rgba(42,35,32,.10);
    --good:#2F6E52; --bad:#A6412E;
  }
  *{box-sizing:border-box;}
  html,body{margin:0;padding:0;}
  body{ font-family:'Hind Siliguri',sans-serif; background:var(--paper); color:var(--ink); min-height:100vh; }
  a{color:inherit; text-decoration:none;} button{font-family:inherit;}
  ::-webkit-scrollbar{width:8px;} ::-webkit-scrollbar-thumb{background:rgba(0,0,0,.15);border-radius:8px;}

  .topbar{ display:flex; align-items:center; justify-content:space-between; padding:18px 30px; border-bottom:1px solid var(--line); background:rgba(247,242,229,.9); backdrop-filter:blur(6px); position:sticky; top:0; z-index:10; }
  .brand{ display:flex; align-items:center; gap:10px; }
  .brand-emblem{ width:34px;height:34px;border-radius:50%; background:var(--cover-maroon); display:flex;align-items:center;justify-content:center; flex-shrink:0; }
  .brand-emblem svg{ width:17px;height:17px; }
  .brand .name{ font-family:'Tiro Bangla',serif; font-size:18px; color:var(--cover-maroon); }
  .topbar-link{ font-size:13px; color:var(--ink-muted); }
  .topbar-link b{ color:var(--cover-maroon); }

  .page-wrap{ max-width:980px; margin:0 auto; padding:34px 20px 80px; }

  .page-head{ text-align:center; margin-bottom:30px; }
  .page-head h1{ font-family:'Tiro Bangla',serif; font-size:27px; margin:0 0 8px; }
  .page-head p{ font-size:13.5px; color:var(--ink-muted); margin:0; }

  .steps-bar{ display:flex; align-items:center; justify-content:center; gap:6px; margin-bottom:34px; flex-wrap:wrap; }
  .step-pill{ display:flex; align-items:center; gap:8px; }
  .step-circle{ width:30px;height:30px;border-radius:50%; background:#fff; border:1.5px solid var(--line); display:flex;align-items:center;justify-content:center; font-size:13px; font-weight:700; color:var(--ink-soft); flex-shrink:0; }
  .step-pill.active .step-circle{ background:var(--gold); border-color:var(--gold); color:#3E1120; }
  .step-pill.done .step-circle{ background:var(--good); border-color:var(--good); color:#fff; }
  .step-pill .lbl{ font-size:12.5px; font-weight:700; color:var(--ink-soft); }
  .step-pill.active .lbl, .step-pill.done .lbl{ color:var(--ink); }
  .step-line{ width:34px;height:2px; background:var(--line); }
  .step-line.done{ background:var(--good); }

  .form-card{ background:var(--card); border:1px solid var(--line); border-radius:20px; padding:34px 38px; }
  .step-pane{ display:none; }
  .step-pane.active{ display:block; animation:fadein .25s ease; }
  @keyframes fadein{ from{opacity:0; transform:translateY(8px);} to{opacity:1; transform:translateY(0);} }

  .pane-head{ margin-bottom:24px; }
  .pane-head h2{ font-family:'Tiro Bangla',serif; font-size:21px; margin:0 0 5px; }
  .pane-head p{ margin:0; font-size:13px; color:var(--ink-muted); }

  .type-grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:24px; }
  .type-card{ border:1.6px solid var(--line); border-radius:14px; padding:18px 14px; text-align:center; cursor:pointer; transition:all .15s ease; }
  .type-card:hover{ border-color:var(--ink-soft); }
  .type-card.active{ border-color:var(--gold); background:rgba(201,162,39,.08); }
  .type-ic{ width:44px;height:44px;border-radius:12px; margin:0 auto 10px; display:flex;align-items:center;justify-content:center; background:var(--paper-deep); }
  .type-card.active .type-ic{ background:rgba(201,162,39,.2); }
  .type-ic svg{ width:22px;height:22px; color:var(--cover-maroon); }
  .type-card .t{ font-size:13.5px; font-weight:700; }

  .field{ margin-bottom:16px; }
  .field.full{ grid-column:1/-1; }
  .field label{ display:block; font-size:12.5px; font-weight:700; color:var(--ink); margin-bottom:6px; }
  .field label .req{ color:var(--bad); }
  .field label .opt{ font-weight:500; color:var(--ink-soft); }
  .field input, .field select, .field textarea{
    width:100%; border:1.5px solid var(--line); border-radius:10px; padding:11px 13px;
    font-size:13.5px; font-family:inherit; color:var(--ink); background:#fff; outline:0;
    transition:border-color .15s ease, box-shadow .15s ease;
  }
  .field input:focus, .field select:focus, .field textarea:focus{ border-color:var(--gold); box-shadow:0 0 0 3px rgba(201,162,39,.15); }
  .field .err{ font-size:11px; color:var(--bad); margin-top:5px; }
  .hint{ font-size:11px; color:var(--ink-soft); margin-top:5px; }
  .grid2{ display:grid; grid-template-columns:1fr 1fr; gap:16px; }
  .grid3{ display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; }

  .billing-toggle{ display:inline-flex; background:var(--paper-deep); border-radius:12px; padding:4px; margin-bottom:20px; }
  .billing-toggle button{ border:0; background:none; padding:8px 18px; border-radius:9px; font-size:12.5px; font-weight:700; color:var(--ink-muted); cursor:pointer; }
  .billing-toggle button.active{ background:#fff; color:var(--ink); box-shadow:0 2px 6px rgba(0,0,0,.08); }
  .billing-toggle .save{ font-size:10px; color:var(--good); font-weight:700; margin-right:4px; }

  .plan-grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:10px; }
  .plan-card{ border:1.6px solid var(--line); border-radius:16px; padding:20px 18px; cursor:pointer; position:relative; }
  .plan-card.active{ border-color:var(--gold); background:rgba(201,162,39,.06); box-shadow:0 10px 24px -14px rgba(201,162,39,.5); }
  .plan-badge{ position:absolute; top:-11px; right:16px; background:var(--gold); color:#3E1120; font-size:10px; font-weight:700; padding:4px 12px; border-radius:20px; }
  .plan-card h3{ font-family:'Tiro Bangla',serif; font-size:16.5px; margin:0 0 4px; }
  .plan-card .desc{ font-size:11.5px; color:var(--ink-muted); margin-bottom:12px; }
  .plan-price{ font-family:'Tiro Bangla',serif; font-size:24px; color:var(--cover-maroon); margin-bottom:10px; }
  .plan-price span{ font-size:11px; color:var(--ink-muted); font-family:'Hind Siliguri',sans-serif; }
  .plan-feats{ list-style:none; margin:0; padding:0; font-size:11.5px; color:var(--ink-muted); display:flex; flex-direction:column; gap:6px; }
  .plan-feats li{ display:flex; gap:6px; align-items:flex-start; }
  .plan-feats svg{ width:13px;height:13px; color:var(--good); flex-shrink:0; margin-top:2px; }
  .plan-radio{ position:absolute; top:16px; left:16px; width:18px;height:18px; border-radius:50%; border:1.6px solid var(--line); }
  .plan-card.active .plan-radio{ border-color:var(--gold); background:var(--gold); }
  .plan-card.active .plan-radio::after{ content:""; display:block; width:7px;height:7px; border-radius:50%; background:#fff; margin:4.5px auto; }

  .subdomain-shell{ display:flex; align-items:center; border:1.5px solid var(--line); border-radius:10px; overflow:hidden; }
  .subdomain-shell input{ border:0; flex:1; padding:11px 13px; font-size:13.5px; font-family:inherit; outline:0; text-align:left; direction:ltr; }
  .subdomain-shell .suffix{ background:var(--paper-deep); padding:11px 13px; font-size:13px; color:var(--ink-muted); white-space:nowrap; }
  .avail-msg{ display:flex; align-items:center; gap:6px; font-size:12px; margin-top:8px; color:var(--ink-muted); }
  .avail-msg.ok{ color:var(--good); }
  .avail-msg.bad{ color:var(--bad); }
  .avail-msg svg{ width:14px;height:14px; }

  .secret-box{ display:flex; gap:10px; background:rgba(201,162,39,.09); border:1px solid rgba(201,162,39,.3); border-radius:12px; padding:14px 16px; font-size:12.5px; color:#7A5E10; margin-bottom:20px; }
  .secret-box svg{ width:18px;height:18px; flex-shrink:0; margin-top:1px; }

  .terms-row{ display:flex; align-items:flex-start; gap:9px; font-size:12.5px; color:var(--ink-muted); margin:18px 0; }
  .terms-row input{ width:16px;height:16px; margin-top:2px; accent-color:var(--gold); flex-shrink:0; }
  .terms-row a{ color:var(--cover-maroon); font-weight:700; }

  .pane-foot{ display:flex; justify-content:space-between; margin-top:26px; padding-top:22px; border-top:1px dashed var(--line); }
  .btn-ghost, .btn-primary{ border-radius:11px; padding:12px 22px; font-size:14px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:8px; border:0; }
  .btn-ghost{ background:#fff; border:1.5px solid var(--line); color:var(--ink-muted); }
  .btn-ghost:hover{ border-color:var(--ink-soft); color:var(--ink); }
  .btn-primary{ background:linear-gradient(180deg, var(--gold-light), var(--gold)); color:#3E1120; box-shadow:0 8px 18px -8px rgba(201,162,39,.55); }
  .btn-primary:hover{ filter:brightness(1.03); }
  .btn-primary svg, .btn-ghost svg{ width:16px;height:16px; }

  .success-wrap{ text-align:center; padding:20px 10px 10px; }
  .success-ic{ width:78px;height:78px;border-radius:50%; background:rgba(47,110,82,.12); display:flex;align-items:center;justify-content:center; margin:0 auto 22px; }
  .success-ic svg{ width:38px;height:38px; color:var(--good); }
  .success-wrap h2{ font-family:'Tiro Bangla',serif; font-size:24px; margin:0 0 8px; }
  .success-wrap p{ font-size:13.5px; color:var(--ink-muted); max-width:460px; margin:0 auto 26px; line-height:1.7; }

  .success-box{ background:var(--paper-deep); border-radius:14px; padding:20px 24px; max-width:440px; margin:0 auto 26px; text-align:right; }
  .success-box .row{ display:flex; justify-content:space-between; align-items:center; padding:9px 0; border-bottom:1px dashed var(--line); font-size:13px; }
  .success-box .row:last-child{ border-bottom:0; }
  .success-box .row .k{ color:var(--ink-muted); }
  .success-box .row .v{ font-weight:700; color:var(--ink); font-family:'Tiro Bangla',serif; direction:ltr; }

  .success-ctas{ display:flex; gap:10px; justify-content:center; flex-wrap:wrap; }

  @media (max-width:760px){
    .form-card{ padding:24px 18px; }
    .type-grid, .plan-grid, .grid2, .grid3{ grid-template-columns:1fr; }
    .steps-bar .lbl{ display:none; }
    .step-line{ width:16px; }
  }
</style>
</head>
<body>

<div class="topbar">
  <div class="brand">
    <div class="brand-emblem"><svg viewBox="0 0 24 24" fill="none" stroke="#E7C767" stroke-width="1.6"><path d="M4 6.5c2.8-1.4 5.6-1.4 8 0v11c-2.4-1.4-5.2-1.4-8 0v-11Z"/><path d="M20 6.5c-2.8-1.4-5.6-1.4-8 0v11c2.4-1.4 5.2-1.4 8 0v-11Z"/></svg></div>
    <div class="name">EDUTION</div>
  </div>
  <div class="topbar-link">ইতিমধ্যে অ্যাকাউন্ট আছে? <b><a href="{{ route('login') }}">লগইন করুন</a></b></div>
</div>

<div class="page-wrap">

@if (session('success'))
    <div class="success-wrap">
        <div class="success-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="12" cy="12" r="9"/><polyline points="8 12 11 15 16 9"/></svg></div>
        <h2>ধন্যবাদ! আপনার আবেদন জমা হয়েছে</h2>
        <p>আমাদের টিম আপনার আবেদন যাচাই করবে। অনুমোদনের পর একটি সিক্রেট কোড আপনার ইমেইল/মোবাইলে পাঠানো হবে — সেটি ও নিচের ইমেইল দিয়ে আপনি আপনার নিজস্ব ঠিকানায় লগইন করতে পারবেন।</p>

        <div class="success-box">
            <div class="row"><span class="k">প্রতিষ্ঠানের ঠিকানা (সম্ভাব্য)</span><span class="v">{{ session('successSlug') }}.edution.xyz</span></div>
            <div class="row"><span class="k">এডমিন ইমেইল</span><span class="v">{{ session('successEmail') }}</span></div>
            <div class="row"><span class="k">বিলিং ধরন</span><span class="v">{{ ['postpaid' => 'পোস্টপেইড', 'prepaid' => 'প্রিপেইড'][session('successBillingType')] ?? session('successBillingType') }}</span></div>
            <div class="row"><span class="k">অবস্থা</span><span class="v">যাচাইয়ের অপেক্ষায়</span></div>
        </div>

        <div class="success-ctas">
            <a href="{{ route('register') }}" class="btn-ghost">আরেকটি প্রতিষ্ঠান রেজিস্ট্রেশন করুন</a>
            <a href="{{ route('login') }}" class="btn-primary">লগইন পেজে যান</a>
        </div>
    </div>
@else
    <div id="wizardView">
        <div class="page-head">
            <h1>আপনার প্রতিষ্ঠান নিবন্ধন করুন</h1>
            <p>মাত্র ৪টি ধাপে আপনার প্রতিষ্ঠানের জন্য EDUTION-এ আবেদন করুন — যাচাইয়ের পর সিক্রেট কোড দিয়ে লগইন করবেন</p>
        </div>

        <div class="steps-bar" id="stepsBar">
            <div class="step-pill active" data-step="1"><div class="step-circle">১</div><div class="lbl">প্রতিষ্ঠান</div></div>
            <div class="step-line"></div>
            <div class="step-pill" data-step="2"><div class="step-circle">২</div><div class="lbl">এডমিন তথ্য</div></div>
            <div class="step-line"></div>
            <div class="step-pill" data-step="3"><div class="step-circle">৩</div><div class="lbl">প্ল্যান</div></div>
            <div class="step-line"></div>
            <div class="step-pill" data-step="4"><div class="step-circle">৪</div><div class="lbl">চূড়ান্ত করুন</div></div>
        </div>

        <form class="form-card" id="regForm" method="POST" action="{{ route('register.store') }}">
            @csrf

            {{-- STEP 1: প্রতিষ্ঠান --}}
            <div class="step-pane active" data-pane="1">
                <div class="pane-head"><h2>প্রতিষ্ঠানের তথ্য</h2><p>আপনার প্রতিষ্ঠান সম্পর্কে কিছু মৌলিক তথ্য দিন</p></div>

                <div class="field">
                    <label>প্রতিষ্ঠানের ধরন <span class="req">*</span></label>
                    <input type="hidden" name="institution_type" id="institutionTypeInput" value="{{ old('institution_type', 'school') }}">
                    <div class="type-grid">
                        <div class="type-card {{ old('institution_type', 'school') === 'school' ? 'active' : '' }}" data-type="school">
                            <div class="type-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5.5A2 2 0 0 1 6 4h13v14H6a2 2 0 0 0-2 2V5.5Z"/></svg></div>
                            <div class="t">স্কুল / কলেজ</div>
                        </div>
                        <div class="type-card {{ old('institution_type') === 'madrasa' ? 'active' : '' }}" data-type="madrasa">
                            <div class="type-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 6.5c2.8-1.4 5.6-1.4 8 0v11c-2.4-1.4-5.2-1.4-8 0v-11Z"/><path d="M20 6.5c-2.8-1.4-5.6-1.4-8 0v11c2.4-1.4 5.2-1.4 8 0v-11Z"/></svg></div>
                            <div class="t">মাদরাসা</div>
                        </div>
                        <div class="type-card {{ old('institution_type') === 'kindergarten' ? 'active' : '' }}" data-type="kindergarten">
                            <div class="type-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c1.4-4 4.2-6 7-6s5.6 2 7 6"/></svg></div>
                            <div class="t">কিন্ডারগার্টেন</div>
                        </div>
                    </div>
                </div>

                <div class="grid2">
                    <div class="field"><label>প্রতিষ্ঠানের নাম <span class="req">*</span></label><input type="text" name="name" placeholder="যেমন: গ্রিনভিউ স্কুল অ্যান্ড কলেজ" value="{{ old('name') }}" required>@error('name')<div class="err">{{ $message }}</div>@enderror</div>
                    <div class="field"><label>EIIN / রেজিস্ট্রেশন নম্বর <span class="opt">(ঐচ্ছিক)</span></label><input type="text" name="eiin" placeholder="যেমন: ১৩৫৭৯২" value="{{ old('eiin') }}"></div>
                </div>

                <div class="grid3">
                    <div class="field"><label>বিভাগ <span class="req">*</span></label>
                        <select name="division" required>
                            <option value="">নির্বাচন করুন</option>
                            @foreach (['ঢাকা', 'চট্টগ্রাম', 'রাজশাহী', 'খুলনা', 'বরিশাল', 'সিলেট', 'রংপুর', 'ময়মনসিংহ'] as $div)
                                <option value="{{ $div }}" {{ old('division') === $div ? 'selected' : '' }}>{{ $div }}</option>
                            @endforeach
                        </select>
                        @error('division')<div class="err">{{ $message }}</div>@enderror
                    </div>
                    <div class="field"><label>জেলা <span class="req">*</span></label><input type="text" name="district" placeholder="জেলার নাম" value="{{ old('district') }}" required>@error('district')<div class="err">{{ $message }}</div>@enderror</div>
                    <div class="field"><label>প্রতিষ্ঠিত সাল</label><input type="number" name="founding_year" placeholder="যেমন: ২০১০" min="1900" max="2026" value="{{ old('founding_year') }}"></div>
                </div>

                <div class="field full"><label>পূর্ণ ঠিকানা <span class="req">*</span></label><textarea name="address" placeholder="গ্রাম/মহল্লা, ডাকঘর, উপজেলা" required>{{ old('address') }}</textarea>@error('address')<div class="err">{{ $message }}</div>@enderror</div>

                <div class="field"><label>আনুমানিক শিক্ষার্থী সংখ্যা</label>
                    <select name="student_count_estimate">
                        <option value="100 এর কম" {{ old('student_count_estimate') === '100 এর কম' ? 'selected' : '' }}>১০০ এর কম</option>
                        <option value="100-500" {{ old('student_count_estimate') === '100-500' ? 'selected' : '' }}>১০০ – ৫০০</option>
                        <option value="500-1000" {{ old('student_count_estimate', '500-1000') === '500-1000' ? 'selected' : '' }}>৫০০ – ১,০০০</option>
                        <option value="1000-2000" {{ old('student_count_estimate') === '1000-2000' ? 'selected' : '' }}>১,০০০ – ২,০০০</option>
                        <option value="2000+" {{ old('student_count_estimate') === '2000+' ? 'selected' : '' }}>২,০০০ এর বেশি</option>
                    </select>
                    <div class="hint">এই তথ্যের ভিত্তিতে আমরা আপনার জন্য উপযুক্ত প্ল্যান সাজেস্ট করব</div>
                </div>
            </div>

            {{-- STEP 2: এডমিন তথ্য --}}
            <div class="step-pane" data-pane="2">
                <div class="pane-head"><h2>এডমিনের তথ্য</h2><p>এই তথ্য দিয়ে আপনি সিস্টেমে প্রধান এডমিন হিসেবে লগইন করবেন</p></div>

                <div class="grid2">
                    <div class="field"><label>পূর্ণ নাম <span class="req">*</span></label><input type="text" name="admin_name" placeholder="আপনার নাম" value="{{ old('admin_name') }}" required>@error('admin_name')<div class="err">{{ $message }}</div>@enderror</div>
                    <div class="field"><label>পদবি <span class="req">*</span></label><input type="text" name="admin_designation" placeholder="যেমন: প্রিন্সিপাল, পরিচালক" value="{{ old('admin_designation') }}" required>@error('admin_designation')<div class="err">{{ $message }}</div>@enderror</div>
                </div>
                <div class="grid2">
                    <div class="field">
                        <label>মোবাইল নম্বর <span class="req">*</span></label>
                        <div style="display:flex; gap:8px;">
                            <input type="tel" name="phone" id="phoneInput" placeholder="০১৭XXXXXXXX" value="{{ old('phone') }}" required style="flex:1;">
                            <button type="button" id="sendOtpBtn" class="btn-ghost" style="white-space:nowrap; padding:11px 16px; font-size:12.5px;">OTP পাঠান</button>
                        </div>
                        @error('phone')<div class="err">{{ $message }}</div>@enderror
                        <div id="otpBox" style="display:none; margin-top:10px;">
                            <div style="display:flex; gap:8px; align-items:center;">
                                <input type="text" id="otpCodeInput" maxlength="6" inputmode="numeric" placeholder="৬ সংখ্যার কোড" style="flex:1;">
                                <button type="button" id="verifyOtpBtn" class="btn-primary" style="white-space:nowrap; padding:11px 16px; font-size:12.5px;">যাচাই করুন</button>
                            </div>
                            <div id="otpStatus" class="hint"></div>
                        </div>
                        <input type="hidden" id="phoneVerifiedInput" value="0">
                    </div>
                    <div class="field"><label>ইমেইল <span class="req">*</span></label><input type="email" name="email" placeholder="you@example.com" value="{{ old('email') }}" required>@error('email')<div class="err">{{ $message }}</div>@enderror
                        <div class="hint">এই ইমেইল দিয়েই পরে লগইন করবেন</div>
                    </div>
                </div>
            </div>

            {{-- STEP 3: বিলিং ধরন --}}
            <div class="step-pane" data-pane="3">
                <div class="pane-head"><h2>বিলিং ধরন বেছে নিন</h2><p>যেকোনো সময় বদলানো যাবে। অনুমোদনের পর ১৪ দিনের ট্রায়ালে সব ফিচার উন্মুক্ত থাকবে, কোনো পেমেন্ট ছাড়াই।</p></div>

                <input type="hidden" name="billing_type" id="billingTypeInput" value="{{ old('billing_type', $selectedBillingType ?? 'postpaid') }}">

                <div class="plan-grid" id="planGrid">
                    @foreach ([
                        'postpaid' => ['পোস্টপেইড', 'মাসিক, ছাত্রসংখ্যা অনুযায়ী টায়ার', '৳৪৯৯ থেকে শুরু', ['১-২০০ ছাত্র = ৳৪৯৯/মাস', '২০১-৫০০ ছাত্র = ৳৯৯৯/মাস', '৫০১-১,০০০ ছাত্র = ৳১,৯৯৯/মাস', '১৫ দিন গ্রেস পিরিয়ড'], 'জনপ্রিয়'],
                        'prepaid' => ['প্রিপেইড', 'আগে ব্যালেন্স লোড, ছাত্র প্রতি হিসাব', '৳৫/শিক্ষার্থী/মাস', ['যত ছাত্র তত বিল, কোনো কমিটমেন্ট নেই', 'ছোট মক্তব/মাদ্রাসার জন্য সাশ্রয়ী', 'ব্যালেন্স কম হলে সতর্কবার্তা'], null],
                    ] as $key => [$title, $desc, $price, $feats, $badge])
                        <div class="plan-card {{ old('billing_type', $selectedBillingType ?? 'postpaid') === $key ? 'active' : '' }}" data-plan="{{ $key }}">
                            @if ($badge)<div class="plan-badge">{{ $badge }}</div>@endif
                            <div class="plan-radio"></div>
                            <h3>{{ $title }}</h3>
                            <div class="desc">{{ $desc }}</div>
                            <div class="plan-price">{{ $price }}</div>
                            <ul class="plan-feats">
                                @foreach ($feats as $f)
                                    <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>{{ $f }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
                <div class="hint">অনুমোদনের পর ১৪ দিনের ফ্রি ট্রায়াল শেষ হলে নির্বাচিত ধরন অনুযায়ী বিলিং শুরু হবে — এখনই কোনো পেমেন্ট লাগবে না। ৫০০+ ছাত্রের জন্য কাস্টম প্রাইসিং লাগলে অনুমোদনের পর আমাদের সাথে যোগাযোগ করতে পারবেন।</div>
            </div>

            {{-- STEP 4: চূড়ান্ত করুন --}}
            <div class="step-pane" data-pane="4">
                <div class="pane-head"><h2>সাবডোমেইন ও চূড়ান্তকরণ</h2><p>আপনার প্রতিষ্ঠানের জন্য একটি পছন্দের ঠিকানা বেছে নিন</p></div>

                <div class="field">
                    <label>পছন্দের সাবডোমেইন <span class="opt">(ঐচ্ছিক — খালি রাখলে প্রতিষ্ঠানের নাম থেকে স্বয়ংক্রিয়ভাবে তৈরি হবে)</span></label>
                    <div class="subdomain-shell">
                        <input type="text" name="preferred_subdomain" id="subdomainInput" placeholder="greenview" value="{{ old('preferred_subdomain') }}">
                        <div class="suffix">.edution.xyz</div>
                    </div>
                    <div class="avail-msg" id="availMsg"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="12" cy="12" r="9"/></svg>এটাই আপনার প্রতিষ্ঠানের ঠিকানা হবে — যাচাইয়ের পর একই নামে চালু হবে</div>
                    @error('preferred_subdomain')<div class="err">{{ $message }}</div>@enderror
                </div>

                <div class="secret-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="5" y="10.5" width="14" height="9" rx="2"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3"/></svg>
                    <span>এখন কোনো পাসওয়ার্ড সেট করতে হবে না। আমাদের টিম আবেদন যাচাই করে আপনার ইমেইল/মোবাইলে একটি <b>সিক্রেট কোড</b> পাঠাবে — সেটি দিয়েই আপনি প্রথমবার লগইন করবেন।</span>
                </div>

                <label class="terms-row">
                    <input type="checkbox" name="terms" value="1" {{ old('terms') ? 'checked' : '' }}>
                    <span>আমি EDUTION-এর <a href="#">শর্তাবলী</a> এবং <a href="#">গোপনীয়তা নীতি</a> মেনে নিচ্ছি এবং নিশ্চিত করছি যে উপরের তথ্য সঠিক।</span>
                </label>
                @error('terms')<div class="err">{{ $message }}</div>@enderror
            </div>

            <div class="pane-foot">
                <button type="button" class="btn-ghost" id="prevBtn" style="visibility:hidden;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 5l-7 7 7 7"/></svg>পূর্ববর্তী
                </button>
                <button type="button" class="btn-primary" id="nextBtn">
                    পরবর্তী ধাপ<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </form>
    </div>
@endif

</div>

<script>
  const totalSteps = 4;
  let current = {{ $errors->has('institution_type') || $errors->has('name') || $errors->has('division') || $errors->has('district') || $errors->has('address') ? 1 : ($errors->has('admin_name') || $errors->has('admin_designation') || $errors->has('phone') || $errors->has('email') ? 2 : ($errors->has('billing_type') ? 3 : ($errors->has('preferred_subdomain') || $errors->has('terms') ? 4 : 1))) }};
  const stepPills = document.querySelectorAll('.step-pill');
  const stepLines = document.querySelectorAll('.step-line');
  const panes = document.querySelectorAll('.step-pane');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const regForm = document.getElementById('regForm');

  function goToStep(n){
    if(!panes.length || n < 1 || n > totalSteps) return;
    current = n;
    panes.forEach(p => p.classList.toggle('active', +p.dataset.pane === n));
    stepPills.forEach(s=>{
      const sn = +s.dataset.step;
      s.classList.toggle('active', sn === n);
      s.classList.toggle('done', sn < n);
      s.querySelector('.step-circle').textContent = sn < n ? '✓' : toBnDigit(sn);
    });
    stepLines.forEach((l,i)=> l.classList.toggle('done', i < n-1));
    prevBtn.style.visibility = n === 1 ? 'hidden' : 'visible';
    nextBtn.innerHTML = n === totalSteps
      ? 'আবেদন জমা দিন <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>'
      : 'পরবর্তী ধাপ <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 5l7 7-7 7"/></svg>';
    document.querySelector('.form-card')?.scrollIntoView({behavior:'smooth', block:'start'});
  }
  const bnDigits = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
  function toBnDigit(n){ return bnDigits[n]; }

  // ── মোবাইল নম্বর OTP যাচাই ──
  const phoneInput = document.getElementById('phoneInput');
  const sendOtpBtn = document.getElementById('sendOtpBtn');
  const otpBox = document.getElementById('otpBox');
  const otpCodeInput = document.getElementById('otpCodeInput');
  const verifyOtpBtn = document.getElementById('verifyOtpBtn');
  const otpStatus = document.getElementById('otpStatus');
  const phoneVerifiedInput = document.getElementById('phoneVerifiedInput');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
  let cooldownTimer = null;

  // ── সাবডোমেইন এভেইলেবিলিটি চেক (debounced) ──
  const subdomainInput = document.getElementById('subdomainInput');
  const availMsg = document.getElementById('availMsg');
  const originalAvailMsg = availMsg ? availMsg.innerHTML : '';
  let slugCheckTimer = null;
  let slugCheckSeq = 0;

  function setAvailMsg(state, text) {
    if (!availMsg) return;
    availMsg.classList.remove('ok', 'bad');
    if (state) availMsg.classList.add(state);
    availMsg.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="12" cy="12" r="9"/></svg>' + text;
  }

  if (subdomainInput) {
    subdomainInput.addEventListener('input', () => {
      const value = subdomainInput.value.trim();
      clearTimeout(slugCheckTimer);

      if (!value) {
        availMsg.innerHTML = originalAvailMsg;
        availMsg.classList.remove('ok', 'bad');
        return;
      }

      setAvailMsg(null, 'চেক করা হচ্ছে...');
      const seq = ++slugCheckSeq;

      slugCheckTimer = setTimeout(async () => {
        try {
          const res = await fetch("{{ route('register.check-slug') }}?slug=" + encodeURIComponent(value), {
            headers: { 'Accept': 'application/json' },
          });
          const data = await res.json();
          if (seq !== slugCheckSeq) return; // পুরনো রেসপন্স, বাতিল
          setAvailMsg(data.available ? 'ok' : 'bad', data.message || (data.available ? 'এই সাবডোমেইনটি খালি আছে।' : 'এই সাবডোমেইনটি ইতিমধ্যে ব্যবহৃত হচ্ছে।'));
        } catch (e) {
          if (seq !== slugCheckSeq) return;
          setAvailMsg(null, 'যাচাই করা যায়নি, আবার চেষ্টা করুন।');
        }
      }, 450);
    });
  }

  function setOtpStatus(msg, ok){
    if (!otpStatus) return;
    otpStatus.textContent = msg;
    otpStatus.style.color = ok ? 'var(--good)' : 'var(--bad)';
  }

  function startCooldown(sec){
    let remaining = sec;
    sendOtpBtn.disabled = true;
    sendOtpBtn.textContent = `আবার পাঠান (${remaining})`;
    clearInterval(cooldownTimer);
    cooldownTimer = setInterval(()=>{
      remaining--;
      if (remaining <= 0) {
        clearInterval(cooldownTimer);
        sendOtpBtn.textContent = 'আবার পাঠান';
        sendOtpBtn.disabled = false;
      } else {
        sendOtpBtn.textContent = `আবার পাঠান (${remaining})`;
      }
    }, 1000);
  }

  if (sendOtpBtn) {
    sendOtpBtn.addEventListener('click', async ()=>{
      if (!phoneInput.value.trim()) { setOtpStatus('আগে মোবাইল নম্বর লিখুন', false); return; }
      sendOtpBtn.disabled = true;
      try {
        const res = await fetch("{{ route('register.otp.send') }}", {
          method: 'POST',
          headers: {'Content-Type':'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept':'application/json'},
          body: JSON.stringify({ phone: phoneInput.value.trim() })
        });
        const data = await res.json();
        if (res.ok) {
          otpBox.style.display = 'block';
          setOtpStatus(data.message, true);
          startCooldown(60);
        } else {
          setOtpStatus(data.message || 'কোড পাঠানো যায়নি', false);
          sendOtpBtn.disabled = false;
        }
      } catch (e) {
        setOtpStatus('নেটওয়ার্ক সমস্যা, আবার চেষ্টা করুন', false);
        sendOtpBtn.disabled = false;
      }
    });
  }

  if (verifyOtpBtn) {
    verifyOtpBtn.addEventListener('click', async ()=>{
      if (!otpCodeInput.value.trim()) { setOtpStatus('কোড লিখুন', false); return; }
      verifyOtpBtn.disabled = true;
      try {
        const res = await fetch("{{ route('register.otp.verify') }}", {
          method: 'POST',
          headers: {'Content-Type':'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept':'application/json'},
          body: JSON.stringify({ phone: phoneInput.value.trim(), code: otpCodeInput.value.trim() })
        });
        const data = await res.json();
        if (res.ok && data.verified) {
          setOtpStatus('✓ ' + data.message, true);
          phoneVerifiedInput.value = '1';
          phoneInput.readOnly = true;
          otpCodeInput.disabled = true;
          verifyOtpBtn.disabled = true;
          sendOtpBtn.style.display = 'none';
          clearInterval(cooldownTimer);
        } else {
          setOtpStatus(data.message || 'ভুল কোড', false);
          verifyOtpBtn.disabled = false;
        }
      } catch (e) {
        setOtpStatus('নেটওয়ার্ক সমস্যা, আবার চেষ্টা করুন', false);
        verifyOtpBtn.disabled = false;
      }
    });
  }

  if (phoneInput) {
    phoneInput.addEventListener('input', ()=>{
      if (phoneVerifiedInput.value === '1') {
        phoneVerifiedInput.value = '0';
        phoneInput.readOnly = false;
        otpBox.style.display = 'none';
        otpCodeInput.value = '';
        sendOtpBtn.style.display = '';
      }
    });
  }

  if (nextBtn) {
    nextBtn.addEventListener('click', ()=>{
      if (current === 2 && phoneVerifiedInput && phoneVerifiedInput.value !== '1') {
        setOtpStatus('পরবর্তী ধাপে যাওয়ার আগে মোবাইল নম্বর OTP দিয়ে যাচাই করুন', false);
        return;
      }
      if(current === totalSteps){
        regForm.submit();
        return;
      }
      goToStep(current + 1);
    });
    prevBtn.addEventListener('click', ()=> goToStep(current - 1));
    stepPills.forEach(s => s.addEventListener('click', ()=> goToStep(+s.dataset.step)));

    document.querySelectorAll('.type-card').forEach(card=>{
      card.addEventListener('click', ()=>{
        document.querySelectorAll('.type-card').forEach(c=>c.classList.remove('active'));
        card.classList.add('active');
        document.getElementById('institutionTypeInput').value = card.dataset.type;
      });
    });

    document.querySelectorAll('.plan-card').forEach(card=>{
      card.addEventListener('click', ()=>{
        document.querySelectorAll('.plan-card').forEach(c=>c.classList.remove('active'));
        card.classList.add('active');
        document.getElementById('billingTypeInput').value = card.dataset.plan;
      });
    });

    const subInput = document.getElementById('subdomainInput');
    if (subInput) {
      subInput.addEventListener('input', ()=>{
        subInput.value = subInput.value.trim().toLowerCase().replace(/[^a-z0-9-]/g,'');
      });
    }

    goToStep(current);
  }
</script>
</body>
</html>
