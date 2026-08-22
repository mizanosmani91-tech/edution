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
<title>EDUTION — স্কুল, মাদরাসা ও কিন্ডারগার্টেন ম্যানেজমেন্ট সিস্টেম</title>
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
    --guardian:#2F6E52; --teacher:#35528F; --student:#A65A2E; --admin:#C9A227;
    --good:#2F6E52;
  }
  *{box-sizing:border-box;}
  html{scroll-behavior:smooth;}
  html,body{margin:0;padding:0;}
  body{ font-family:'Hind Siliguri',sans-serif; background:var(--paper); color:var(--ink); }
  a{color:inherit; text-decoration:none;} button{font-family:inherit;}
  img,svg{display:block;}
  ::-webkit-scrollbar{width:8px;} ::-webkit-scrollbar-thumb{background:rgba(0,0,0,.15);border-radius:8px;}

  .wrap{ max-width:1160px; margin:0 auto; padding:0 24px; }
  section{ padding:76px 0; }
  .eyebrow{ display:inline-flex; align-items:center; gap:7px; font-size:12px; font-weight:700; letter-spacing:.06em; color:var(--cover-maroon); background:rgba(92,26,43,.07); padding:6px 14px; border-radius:20px; margin-bottom:14px; }
  h2.sec-title{ font-family:'Tiro Bangla',serif; font-size:32px; margin:0 0 10px; }
  p.sec-sub{ font-size:15px; color:var(--ink-muted); max-width:560px; margin:0 auto 40px; line-height:1.7; }
  .center{ text-align:center; }

  .btn-primary{ display:inline-flex; align-items:center; gap:9px; border:0; border-radius:12px; padding:14px 24px; background:linear-gradient(180deg, var(--gold-light), var(--gold)); color:#3E1120; font-weight:700; font-size:14.5px; cursor:pointer; box-shadow:0 10px 22px -10px rgba(201,162,39,.65); white-space:nowrap; }
  .btn-primary:hover{ filter:brightness(1.03); }
  .btn-ghost{ display:inline-flex; align-items:center; gap:9px; border:1.6px solid var(--line); border-radius:12px; padding:13px 22px; background:#fff; color:var(--ink); font-weight:700; font-size:14.5px; cursor:pointer; white-space:nowrap; }
  .btn-ghost:hover{ border-color:var(--ink-soft); }
  .btn-primary svg,.btn-ghost svg{ width:16px;height:16px; }

  .nav{ position:sticky; top:0; z-index:50; background:rgba(247,242,229,.9); backdrop-filter:blur(8px); border-bottom:1px solid var(--line); }
  .nav-inner{ max-width:1160px; margin:0 auto; padding:14px 24px; display:flex; align-items:center; justify-content:space-between; gap:20px; }
  .brand{ display:flex; align-items:center; gap:10px; }
  .brand-emblem{ width:36px;height:36px;border-radius:50%; background:var(--cover-maroon); display:flex;align-items:center;justify-content:center; flex-shrink:0; }
  .brand-emblem svg{ width:18px;height:18px; }
  .brand .name{ font-family:'Tiro Bangla',serif; font-size:19px; color:var(--cover-maroon); }
  .nav-links{ display:flex; gap:30px; font-size:14px; font-weight:600; color:var(--ink-muted); }
  .nav-links a:hover{ color:var(--ink); }
  .nav-actions{ display:flex; gap:10px; align-items:center; }
  .nav-actions .btn-ghost, .nav-actions .btn-primary{ padding:10px 16px; font-size:13px; }
  .nav-toggle{ display:none; background:none; border:0; cursor:pointer; }

  .hero{ padding:70px 0 40px; position:relative; overflow:hidden; }
  .hero-grid{ display:grid; grid-template-columns:1fr 1fr; gap:50px; align-items:center; }
  .hero-badge{ display:inline-flex; align-items:center; gap:8px; background:#fff; border:1px solid var(--line); border-radius:20px; padding:7px 14px; font-size:12.5px; font-weight:600; color:var(--ink-muted); margin-bottom:20px; }
  .hero-badge b{ color:var(--cover-maroon); }
  .hero h1{ font-family:'Tiro Bangla',serif; font-size:44px; line-height:1.25; margin:0 0 18px; color:var(--ink); }
  .hero h1 span{ color:var(--cover-maroon); }
  .hero p{ font-size:15.5px; color:var(--ink-muted); line-height:1.8; max-width:480px; margin:0 0 28px; }
  .hero-ctas{ display:flex; gap:12px; flex-wrap:wrap; margin-bottom:30px; }
  .hero-trust{ display:flex; align-items:center; gap:14px; }
  .hero-trust .avatars{ display:flex; }
  .hero-trust .avatars span{ width:32px;height:32px;border-radius:50%; border:2px solid var(--paper); margin-right:-10px; display:flex;align-items:center;justify-content:center; font-size:11px; font-weight:700; color:#fff; }
  .hero-trust .txt{ font-size:12.5px; color:var(--ink-muted); }
  .hero-trust .txt b{ color:var(--ink); }

  .hero-visual{ position:relative; }
  .mock-frame{
    background:var(--card); border-radius:18px; border:1px solid var(--line);
    box-shadow:0 30px 60px -20px rgba(60,30,20,.3); overflow:hidden;
    transform:rotate(1.2deg);
  }
  .mock-topbar{ display:flex; gap:6px; padding:12px 14px; border-bottom:1px solid var(--line); }
  .mock-topbar span{ width:9px;height:9px;border-radius:50%; background:var(--line); }
  .mock-body{ display:grid; grid-template-columns:70px 1fr; }
  .mock-side{ background:linear-gradient(160deg,var(--cover-maroon),var(--cover-maroon-deep)); padding:16px 10px; display:flex; flex-direction:column; gap:14px; align-items:center; }
  .mock-side span{ width:22px;height:22px;border-radius:6px; background:rgba(231,199,103,.25); }
  .mock-side span.active{ background:var(--gold); }
  .mock-main{ padding:16px; }
  .mock-kpis{ display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:14px; }
  .mock-kpi{ background:var(--paper-deep); border-radius:9px; padding:10px; }
  .mock-kpi .bar{ height:5px; border-radius:4px; background:var(--gold); width:60%; margin-bottom:8px; }
  .mock-kpi .num{ height:12px; width:50%; background:var(--ink); border-radius:3px; opacity:.15; }
  .mock-chart{ background:var(--paper-deep); border-radius:9px; height:90px; padding:10px; display:flex; align-items:flex-end; gap:6px; }
  .mock-chart span{ flex:1; background:linear-gradient(180deg,var(--gold-light),var(--gold)); border-radius:3px 3px 0 0; }
  .float-card{
    position:absolute; background:#fff; border-radius:12px; padding:10px 14px; box-shadow:0 14px 30px -12px rgba(60,30,20,.35);
    display:flex; align-items:center; gap:10px; font-size:12px; font-weight:700;
  }
  .float-card.f1{ top:-14px; right:-18px; }
  .float-card.f2{ bottom:-16px; left:-22px; }
  .float-card .fic{ width:30px;height:30px;border-radius:8px; display:flex;align-items:center;justify-content:center; }
  .float-card svg{ width:15px;height:15px; }

  .trust-strip{ padding:30px 0; border-top:1px solid var(--line); border-bottom:1px solid var(--line); }
  .trust-strip .row{ display:flex; justify-content:space-around; flex-wrap:wrap; gap:20px; text-align:center; }
  .trust-strip .item .n{ font-family:'Tiro Bangla',serif; font-size:26px; color:var(--cover-maroon); }
  .trust-strip .item .l{ font-size:12px; color:var(--ink-muted); margin-top:2px; }

  .feat-grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:18px; }
  .feat-card{ background:var(--card); border:1px solid var(--line); border-radius:16px; padding:24px; }
  .feat-ic{ width:44px;height:44px;border-radius:12px; display:flex;align-items:center;justify-content:center; margin-bottom:16px; background:color-mix(in srgb, var(--accent) 14%, white); }
  .feat-ic svg{ width:22px;height:22px; color:var(--accent); }
  .feat-card h3{ font-family:'Tiro Bangla',serif; font-size:17px; margin:0 0 8px; }
  .feat-card p{ font-size:13.3px; color:var(--ink-muted); line-height:1.7; margin:0; }

  .whom-grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:18px; }
  .whom-card{ background:var(--card); border:1px solid var(--line); border-radius:18px; padding:26px; text-align:right; position:relative; overflow:hidden; }
  .whom-card::before{ content:""; position:absolute; top:0; left:0; right:0; height:4px; background:var(--accent); }
  .whom-ic{ width:50px;height:50px;border-radius:14px; display:flex;align-items:center;justify-content:center; background:color-mix(in srgb, var(--accent) 14%, white); margin-bottom:16px; }
  .whom-ic svg{ width:24px;height:24px; color:var(--accent); }
  .whom-card h3{ font-family:'Tiro Bangla',serif; font-size:19px; margin:0 0 10px; }
  .whom-card ul{ list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:9px; }
  .whom-card li{ font-size:13px; color:var(--ink-muted); display:flex; align-items:flex-start; gap:8px; justify-content:flex-end; text-align:right; }
  .whom-card li svg{ width:15px;height:15px; color:var(--accent); flex-shrink:0; margin-top:2px; }

  .roles-band{ background:linear-gradient(135deg,#6E2136,var(--cover-maroon-deep)); border-radius:24px; padding:44px 40px; color:#F3E9D2; }
  .roles-band h2{ color:var(--gold-light); }
  .roles-band .sec-sub{ color:rgba(243,233,210,.75); }
  .roles-grid{ display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-top:10px; }
  .role-card{ background:rgba(255,255,255,.06); border:1px solid rgba(231,199,103,.2); border-radius:14px; padding:20px; text-align:center; }
  .role-ic{ width:46px;height:46px;border-radius:50%; background:rgba(231,199,103,.15); display:flex;align-items:center;justify-content:center; margin:0 auto 12px; }
  .role-ic svg{ width:22px;height:22px; color:var(--gold-light); }
  .role-card .t{ font-family:'Tiro Bangla',serif; font-size:15.5px; margin-bottom:4px; }
  .role-card .d{ font-size:12px; color:rgba(243,233,210,.65); line-height:1.6; }

  .billing-toggle{ display:inline-flex; background:var(--paper-deep); border-radius:12px; padding:4px; margin-bottom:36px; }
  .billing-toggle button{ border:0; background:none; padding:9px 20px; border-radius:9px; font-size:13px; font-weight:700; color:var(--ink-muted); cursor:pointer; }
  .billing-toggle button.active{ background:#fff; color:var(--ink); box-shadow:0 2px 6px rgba(0,0,0,.08); }
  .billing-toggle .save{ font-size:10px; color:var(--good); font-weight:700; margin-right:4px; }

  .price-grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:18px; align-items:stretch; }
  .price-card{ background:var(--card); border:1.5px solid var(--line); border-radius:18px; padding:28px 24px; display:flex; flex-direction:column; }
  .price-card.popular{ border-color:var(--gold); box-shadow:0 20px 40px -18px rgba(201,162,39,.4); position:relative; }
  .popular-badge{ position:absolute; top:-13px; right:24px; background:var(--gold); color:#3E1120; font-size:11px; font-weight:700; padding:5px 14px; border-radius:20px; }
  .price-card h3{ font-family:'Tiro Bangla',serif; font-size:19px; margin:0 0 6px; }
  .price-card .desc{ font-size:12.5px; color:var(--ink-muted); margin-bottom:18px; }
  .price-amt{ font-family:'Tiro Bangla',serif; font-size:34px; color:var(--cover-maroon); margin-bottom:2px; }
  .price-amt span{ font-size:13px; color:var(--ink-muted); font-family:'Hind Siliguri',sans-serif; font-weight:500; }
  .price-old{ font-size:12px; color:var(--ink-soft); text-decoration:line-through; margin-bottom:18px; }
  .price-feats{ list-style:none; margin:0 0 24px; padding:0; display:flex; flex-direction:column; gap:11px; flex:1; }
  .price-feats li{ font-size:13px; color:var(--ink-muted); display:flex; align-items:flex-start; gap:8px; justify-content:flex-end; text-align:right; }
  .price-feats li svg{ width:15px;height:15px; color:var(--good); flex-shrink:0; margin-top:2px; }
  .price-card .btn-primary, .price-card .btn-ghost{ width:100%; justify-content:center; }

  .test-grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:18px; }
  .test-card{ background:var(--card); border:1px solid var(--line); border-radius:16px; padding:24px; }
  .test-quote{ font-size:13.3px; color:var(--ink); line-height:1.8; margin-bottom:18px; }
  .test-who{ display:flex; align-items:center; gap:10px; }
  .test-avatar{ width:38px;height:38px;border-radius:50%; display:flex;align-items:center;justify-content:center; font-weight:700; font-size:13px; color:#fff; flex-shrink:0; }
  .test-who .nm{ font-size:13px; font-weight:700; }
  .test-who .role{ font-size:11.5px; color:var(--ink-soft); }
  .stars{ display:flex; gap:2px; margin-bottom:10px; }
  .stars svg{ width:14px;height:14px; color:var(--gold); }

  .faq-list{ max-width:720px; margin:0 auto; }
  .faq-item{ background:var(--card); border:1px solid var(--line); border-radius:13px; margin-bottom:10px; overflow:hidden; }
  .faq-q{ display:flex; align-items:center; justify-content:space-between; padding:17px 20px; cursor:pointer; font-size:14px; font-weight:700; }
  .faq-q svg{ width:16px;height:16px; color:var(--ink-soft); transition:transform .2s ease; flex-shrink:0; }
  .faq-item.open .faq-q svg{ transform:rotate(180deg); }
  .faq-a-wrap{ display:grid; grid-template-rows:0fr; transition:grid-template-rows .22s ease; }
  .faq-item.open .faq-a-wrap{ grid-template-rows:1fr; }
  .faq-a-inner{ overflow:hidden; }
  .faq-a{ padding:0 20px 18px; font-size:13px; color:var(--ink-muted); line-height:1.8; }

  .final-cta{ background:linear-gradient(135deg,#6E2136,var(--cover-maroon-deep)); border-radius:24px; padding:56px 40px; text-align:center; color:#F3E9D2; position:relative; overflow:hidden; }
  .final-cta h2{ font-family:'Tiro Bangla',serif; font-size:30px; color:var(--gold-light); margin:0 0 12px; }
  .final-cta p{ font-size:14.5px; color:rgba(243,233,210,.8); max-width:480px; margin:0 auto 26px; line-height:1.7; }
  .final-ctas{ display:flex; gap:12px; justify-content:center; flex-wrap:wrap; }

  footer{ padding:50px 0 30px; border-top:1px solid var(--line); }
  .foot-grid{ display:grid; grid-template-columns:1.4fr 1fr 1fr 1fr; gap:30px; margin-bottom:34px; }
  .foot-brand .name{ font-family:'Tiro Bangla',serif; font-size:19px; color:var(--cover-maroon); margin-bottom:8px; }
  .foot-brand p{ font-size:12.5px; color:var(--ink-muted); line-height:1.8; max-width:260px; }
  .foot-col h4{ font-size:13px; margin:0 0 14px; }
  .foot-col ul{ list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:10px; }
  .foot-col a{ font-size:13px; color:var(--ink-muted); }
  .foot-col a:hover{ color:var(--ink); }
  .foot-bottom{ display:flex; justify-content:space-between; align-items:center; padding-top:24px; border-top:1px solid var(--line); font-size:12px; color:var(--ink-soft); flex-wrap:wrap; gap:10px; }
  .foot-social{ display:flex; gap:10px; }
  .foot-social a{ width:34px;height:34px;border-radius:50%; background:var(--paper-deep); display:flex;align-items:center;justify-content:center; }
  .foot-social svg{ width:15px;height:15px; }

  @media (max-width:980px){
    .hero-grid{ grid-template-columns:1fr; }
    .feat-grid, .whom-grid, .price-grid, .test-grid{ grid-template-columns:repeat(2,1fr); }
    .roles-grid{ grid-template-columns:repeat(2,1fr); }
    .foot-grid{ grid-template-columns:1fr 1fr; }
  }
  @media (max-width:720px){
    .nav-links{ display:none; }
    .nav-toggle{ display:block; }
    .hero h1{ font-size:32px; }
    .feat-grid, .whom-grid, .price-grid, .test-grid, .roles-grid{ grid-template-columns:1fr; }
    .mock-kpis{ grid-template-columns:1fr; }
    section{ padding:54px 0; }
    .roles-band, .final-cta{ padding:32px 22px; }
    .foot-grid{ grid-template-columns:1fr; }
  }
</style>
</head>
<body>

<nav class="nav">
  <div class="nav-inner">
    <div class="brand">
      <div class="brand-emblem"><svg viewBox="0 0 24 24" fill="none" stroke="#E7C767" stroke-width="1.6"><path d="M4 6.5c2.8-1.4 5.6-1.4 8 0v11c-2.4-1.4-5.2-1.4-8 0v-11Z"/><path d="M20 6.5c-2.8-1.4-5.6-1.4-8 0v11c2.4-1.4 5.2-1.4 8 0v-11Z"/></svg></div>
      <div class="name">EDUTION</div>
    </div>
    <div class="nav-links">
      <a href="#features">ফিচার</a>
      <a href="#whom">কার জন্য</a>
      <a href="#pricing">মূল্য</a>
      <a href="#faq">প্রশ্নোত্তর</a>
    </div>
    <div class="nav-actions">
      <a href="{{ route('login') }}" class="btn-ghost">লগইন</a>
      <a href="{{ route('register') }}" class="btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
        ফ্রি ট্রায়াল শুরু করুন
      </a>
    </div>
  </div>
</nav>

<header class="hero">
  <div class="wrap hero-grid">
    <div>
      <div class="hero-badge">🎉 <b>নতুন:</b> মাদরাসার জন্য বিশেষ মডিউল এখন উপলব্ধ</div>
      <h1>স্কুল, মাদরাসা ও কিন্ডারগার্টেন <span>পরিচালনার সহজ সমাধান</span></h1>
      <p>ভর্তি, হাজিরা, ফলাফল, ফি আদায়, অভিভাবক যোগাযোগ — সবকিছু এক প্ল্যাটফর্মে। কাগজের ঝামেলা ছেড়ে দিন, EDUTION দিয়ে আপনার প্রতিষ্ঠান পরিচালনা করুন ডিজিটালি।</p>
      <div class="hero-ctas">
        <a href="{{ route('register') }}" class="btn-primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
          ফ্রি ট্রায়াল শুরু করুন
        </a>
        <a href="#demo" class="btn-ghost">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m10 8 6 4-6 4V8Z"/></svg>
          ডেমো দেখুন
        </a>
      </div>
      <div class="hero-trust">
        <div class="avatars">
          <span style="background:var(--admin)">এ</span>
          <span style="background:var(--guardian)">অ</span>
          <span style="background:var(--teacher)">শ</span>
          <span style="background:var(--student)">ছ</span>
        </div>
        <div class="txt"><b>৫০০+ প্রতিষ্ঠান</b> ইতিমধ্যে EDUTION ব্যবহার করছে</div>
      </div>
    </div>

    <div class="hero-visual">
      <div class="mock-frame">
        <div class="mock-topbar"><span></span><span></span><span></span></div>
        <div class="mock-body">
          <div class="mock-side"><span class="active"></span><span></span><span></span><span></span><span></span></div>
          <div class="mock-main">
            <div class="mock-kpis">
              <div class="mock-kpi"><div class="bar"></div><div class="num"></div></div>
              <div class="mock-kpi"><div class="bar" style="background:var(--teacher)"></div><div class="num"></div></div>
              <div class="mock-kpi"><div class="bar" style="background:var(--guardian)"></div><div class="num"></div></div>
            </div>
            <div class="mock-chart">
              <span style="height:40%"></span><span style="height:65%"></span><span style="height:50%"></span>
              <span style="height:80%"></span><span style="height:60%"></span><span style="height:90%"></span>
              <span style="height:70%"></span>
            </div>
          </div>
        </div>
      </div>
      <div class="float-card f1"><div class="fic" style="background:rgba(47,110,82,.14);color:var(--good);"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="20 6 9 17 4 12"/></svg></div>ফি আদায় সম্পন্ন</div>
      <div class="float-card f2"><div class="fic" style="background:rgba(201,162,39,.16);color:#8a6c17;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="m8.5 12 2.3 2.3L16 9.7"/></svg></div>হাজিরা ৯৬.২%</div>
    </div>
  </div>
</header>

<div class="trust-strip">
  <div class="wrap row">
    <div class="item"><div class="n">৫০০+</div><div class="l">প্রতিষ্ঠান</div></div>
    <div class="item"><div class="n">২.৫ লক্ষ+</div><div class="l">শিক্ষার্থী</div></div>
    <div class="item"><div class="n">১৮,০০০+</div><div class="l">শিক্ষক/স্টাফ</div></div>
    <div class="item"><div class="n">৯৯.৯%</div><div class="l">আপটাইম</div></div>
  </div>
</div>

<section id="demo">
  <div class="wrap center">
    <div class="eyebrow">সাইনআপ ছাড়াই ঘুরে দেখুন</div>
    <h2 class="sec-title">লাইভ ডেমো ড্যাশবোর্ড</h2>
    <p class="sec-sub">সাইনআপ ছাড়াই এক ক্লিকে EDUTION-এর সবকিছু নিজে হাতে দেখুন — লগইন পেজে ডেমো আইডি-পাস দেওয়া আছে।</p>
    <a href="{{ route('login') }}" class="btn-primary" style="display:inline-flex;">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m10 8 6 4-6 4V8Z"/></svg>
      ডেমোতে প্রবেশ করুন
    </a>
  </div>
</section>

<section id="features">
  <div class="wrap center">
    <div class="eyebrow">মডিউলসমূহ</div>
    <h2 class="sec-title">প্রতিষ্ঠান পরিচালনার জন্য যা যা দরকার</h2>
    <p class="sec-sub">প্রতিটি মডিউল আলাদাভাবে ডিজাইন করা হয়েছে বাংলাদেশের স্কুল, মাদরাসা ও কিন্ডারগার্টেনের বাস্তব প্রয়োজন মাথায় রেখে।</p>
  </div>
  <div class="wrap feat-grid">
    <div class="feat-card"><div class="feat-ic" style="--accent:var(--guardian)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5.5A2 2 0 0 1 6 4h13v14H6a2 2 0 0 0-2 2V5.5Z"/></svg></div><h3>একাডেমিক ব্যবস্থাপনা</h3><p>ক্লাস, শাখা, রুটিন, পরীক্ষা ও ফলাফল — সবকিছু একটি সিস্টেমে সাজিয়ে রাখুন।</p></div>
    <div class="feat-card"><div class="feat-ic" style="--accent:var(--admin)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="m8.5 12 2.3 2.3L16 9.7"/></svg></div><h3>ডিজিটাল হাজিরা</h3><p>শিক্ষার্থী ও স্টাফের হাজিরা এক ক্লিকে নিন, চেক ইন/আউট ট্র্যাক করুন।</p></div>
    <div class="feat-card"><div class="feat-ic" style="--accent:var(--student)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="9" cy="8" r="3.3"/><path d="M3 20c1-3.6 3.4-5.4 6-5.4s5 1.8 6 5.4"/><path d="M17 8h4M19 6v4"/></svg></div><h3>অনলাইন ভর্তি</h3><p>ভর্তি ফরম, ইউনিক আইডি ও পোর্টাল অ্যাক্সেস — সব স্বয়ংক্রিয়ভাবে তৈরি হয়।</p></div>
    <div class="feat-card"><div class="feat-ic" style="--accent:var(--cover-maroon)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3.5" y="7" width="17" height="12" rx="2.5"/><path d="M3.5 11h17"/></svg></div><h3>ফি ব্যবস্থাপনা</h3><p>ফি সংগ্রহ, ইনভয়েস, বকেয়া ট্র্যাকিং ও রশিদ — সবকিছু স্বচ্ছভাবে।</p></div>
    <div class="feat-card"><div class="feat-ic" style="--accent:var(--teacher)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5h13l3 4-3 4H4z"/><path d="M6 13v6"/></svg></div><h3>অভিভাবক যোগাযোগ</h3><p>নোটিশ, এসএমএস ও পোর্টালের মাধ্যমে অভিভাবকদের সাথে সরাসরি সংযোগ।</p></div>
    <div class="feat-card"><div class="feat-ic" style="--accent:var(--good)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 19V10l8-6 8 6v9"/><path d="M9 19v-6h6v6"/></svg></div><h3>রিপোর্ট ও এনালিটিক্স</h3><p>একাডেমিক ও আর্থিক অবস্থা এক নজরে দেখুন গ্রাফ ও চার্টের মাধ্যমে।</p></div>
  </div>
</section>

<section id="whom" style="background:var(--paper-deep);">
  <div class="wrap center">
    <div class="eyebrow">কার জন্য</div>
    <h2 class="sec-title">যেকোনো ধরনের প্রতিষ্ঠানের জন্য উপযোগী</h2>
    <p class="sec-sub">আপনার প্রতিষ্ঠান স্কুল হোক, মাদরাসা হোক বা কিন্ডারগার্টেন — EDUTION প্রতিটির জন্য আলাদাভাবে সাজানো।</p>
  </div>
  <div class="wrap whom-grid">
    <div class="whom-card" style="--accent:var(--guardian)">
      <div class="whom-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5.5A2 2 0 0 1 6 4h13v14H6a2 2 0 0 0-2 2V5.5Z"/></svg></div>
      <h3>স্কুল ও কলেজ</h3>
      <ul>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>একাধিক শাখা ও শিফট ব্যবস্থাপনা</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>পরীক্ষা ও ফলাফল প্রসেসিং</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>SSC/HSC বোর্ড ফরম্যাট সাপোর্ট</li>
      </ul>
    </div>
    <div class="whom-card" style="--accent:var(--cover-maroon)">
      <div class="whom-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 6.5c2.8-1.4 5.6-1.4 8 0v11c-2.4-1.4-5.2-1.4-8 0v-11Z"/><path d="M20 6.5c-2.8-1.4-5.6-1.4-8 0v11c2.4-1.4 5.2-1.4 8 0v-11Z"/></svg></div>
      <h3>মাদরাসা</h3>
      <ul>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>হিফজ ও নাজেরা অগ্রগতি ট্র্যাকিং</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>বোর্ডিং/আবাসিক ব্যবস্থাপনা</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>দাখিল/আলিম সিলেবাস সমর্থিত</li>
      </ul>
    </div>
    <div class="whom-card" style="--accent:var(--student)">
      <div class="whom-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c1.4-4 4.2-6 7-6s5.6 2 7 6"/></svg></div>
      <h3>কিন্ডারগার্টেন</h3>
      <ul>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>সহজ, ছবি-ভিত্তিক প্রোফাইল</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>পিক-আপ/ড্রপ-অফ নোটিফিকেশন</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>খাবার ও ঘুমের রুটিন ট্র্যাকিং</li>
      </ul>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="roles-band">
      <div class="center">
        <div class="eyebrow" style="background:rgba(231,199,103,.14); color:var(--gold-light);">একক লগইন</div>
        <h2 class="sec-title">সবার জন্য একটাই প্ল্যাটফর্ম</h2>
        <p class="sec-sub">এডমিন, শিক্ষক, অভিভাবক ও শিক্ষার্থী — প্রত্যেকের নিজস্ব ড্যাশবোর্ড, একই সিস্টেমে।</p>
      </div>
      <div class="roles-grid">
        <div class="role-card"><div class="role-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="3.2"/><path d="M19.4 13.5a7.7 7.7 0 0 0 0-3l1.9-1.4-2-3.4-2.2.8a7.6 7.6 0 0 0-2.6-1.5L14 2.5h-4l-.5 2.5a7.6 7.6 0 0 0-2.6 1.5l-2.2-.8-2 3.4L4.6 10.5a7.7 7.7 0 0 0 0 3L2.7 14.9l2 3.4 2.2-.8c.77.66 1.65 1.17 2.6 1.5l.5 2.5h4l.5-2.5a7.6 7.6 0 0 0 2.6-1.5l2.2.8 2-3.4-1.9-1.4Z"/></svg></div><div class="t">এডমিন</div><div class="d">সার্বিক নিয়ন্ত্রণ ও পরিচালনা</div></div>
        <div class="role-card"><div class="role-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="6" width="16" height="13" rx="2"/><path d="M9 6V5a3 3 0 0 1 6 0v1"/></svg></div><div class="t">শিক্ষক/স্টাফ</div><div class="d">হাজিরা, ফলাফল ও ক্লাস ব্যবস্থাপনা</div></div>
        <div class="role-card"><div class="role-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 20v-2a5 5 0 0 1 5-5h1"/><circle cx="9.5" cy="7" r="3.2"/><path d="M16 12v4M18 14h-4"/></svg></div><div class="t">অভিভাবক</div><div class="d">সন্তানের অগ্রগতি এক নজরে</div></div>
        <div class="role-card"><div class="role-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="3.2"/><path d="M5 20c1.2-3.6 4-5.4 7-5.4s5.8 1.8 7 5.4"/></svg></div><div class="t">শিক্ষার্থী</div><div class="d">রুটিন, ফলাফল ও নোটিশ</div></div>
      </div>
    </div>
  </div>
</section>

<section id="pricing">
  <div class="wrap center">
    <div class="eyebrow">মূল্য পরিকল্পনা</div>
    <h2 class="sec-title">সহজ, স্বচ্ছ মূল্য নির্ধারণ</h2>
    <p class="sec-sub">প্রতিষ্ঠানের ছাত্রসংখ্যা ও চাহিদা অনুযায়ী পোস্টপেইড বা প্রিপেইড — যেকোনো একটা বেছে নিন। যেকোনো সময় বদলানো যাবে।</p>
    <div class="billing-toggle">
      <button class="active" data-mode="postpaid">পোস্টপেইড (মাসিক)</button>
      <button data-mode="prepaid">প্রিপেইড (ব্যালেন্স)</button>
    </div>
  </div>

  <div class="wrap price-grid" data-panel="postpaid">
    <div class="price-card">
      <h3>ছোট প্রতিষ্ঠান</h3>
      <div class="desc">১–২০০ জন শিক্ষার্থী পর্যন্ত</div>
      <div class="price-amt">৳৪৯৯<span>/মাস</span></div>
      <ul class="price-feats">
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>২০০ জন পর্যন্ত শিক্ষার্থী</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>সকল মডিউল অন্তর্ভুক্ত</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>SMS ও ইমেইল নোটিফিকেশন</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>১৫ দিন গ্রেস পিরিয়ড</li>
      </ul>
      <a href="{{ route('register') }}?billing_type=postpaid" class="btn-ghost">শুরু করুন</a>
    </div>

    <div class="price-card popular">
      <div class="popular-badge">সর্বাধিক জনপ্রিয়</div>
      <h3>মাঝারি প্রতিষ্ঠান</h3>
      <div class="desc">২০১–৫০০ জন শিক্ষার্থী পর্যন্ত</div>
      <div class="price-amt">৳৯৯৯<span>/মাস</span></div>
      <ul class="price-feats">
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>৫০০ জন পর্যন্ত শিক্ষার্থী</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>সকল মডিউল সহ আবাসন ও পরিবহন</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>অভিভাবক ও শিক্ষক পোর্টাল</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>প্রায়োরিটি সাপোর্ট</li>
      </ul>
      <a href="{{ route('register') }}?billing_type=postpaid" class="btn-primary">শুরু করুন</a>
    </div>

    <div class="price-card">
      <h3>বড় প্রতিষ্ঠান</h3>
      <div class="desc">৫০১–১,০০০ জন শিক্ষার্থী পর্যন্ত</div>
      <div class="price-amt">৳১,৯৯৯<span>/মাস</span></div>
      <ul class="price-feats">
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>১,০০০ জন পর্যন্ত শিক্ষার্থী</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>একাধিক শাখা ব্যবস্থাপনা</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>API অ্যাক্সেস</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>ডেডিকেটেড অ্যাকাউন্ট ম্যানেজার</li>
      </ul>
      <a href="{{ route('register') }}?billing_type=postpaid" class="btn-ghost">শুরু করুন</a>
    </div>
  </div>

  <div class="wrap price-grid" data-panel="prepaid" style="display:none;">
    <div class="price-card" style="grid-column:1/-1;max-width:460px;margin:0 auto;">
      <h3>প্রিপেইড ওয়ালেট</h3>
      <div class="desc">যেকোনো আকারের প্রতিষ্ঠানের জন্য — যত ছাত্র, তত খরচ</div>
      <div class="price-amt">৳৫<span>/শিক্ষার্থী/মাস</span></div>
      <ul class="price-feats">
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>আগে ব্যালেন্স লোড করুন, প্রতি মাসে স্বয়ংক্রিয় কর্তন</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>কোনো মাসিক কমিটমেন্ট নেই — যত ছাত্র তত বিল</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>ছোট মক্তব/নূরানী মাদ্রাসার জন্য সবচেয়ে সাশ্রয়ী</li>
        <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>ব্যালেন্স কম হলে SMS/ইমেইল সতর্কবার্তা</li>
      </ul>
      <a href="{{ route('register') }}?billing_type=prepaid" class="btn-primary">শুরু করুন</a>
    </div>
  </div>
</section>

<section style="background:var(--paper-deep);">
  <div class="wrap center">
    <div class="eyebrow">প্রতিক্রিয়া</div>
    <h2 class="sec-title">প্রতিষ্ঠান প্রধানরা যা বলছেন</h2>
    <p class="sec-sub">সারা দেশের শত শত প্রতিষ্ঠান EDUTION-এর উপর আস্থা রেখেছে।</p>
  </div>
  <div class="wrap test-grid">
    <div class="test-card">
      <div class="stars"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 9 8l-6 1 4.5 4.4L6.5 20 12 17l5.5 3-1-6.6L21 9l-6-1Z"/></svg><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 9 8l-6 1 4.5 4.4L6.5 20 12 17l5.5 3-1-6.6L21 9l-6-1Z"/></svg><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 9 8l-6 1 4.5 4.4L6.5 20 12 17l5.5 3-1-6.6L21 9l-6-1Z"/></svg><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 9 8l-6 1 4.5 4.4L6.5 20 12 17l5.5 3-1-6.6L21 9l-6-1Z"/></svg><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 9 8l-6 1 4.5 4.4L6.5 20 12 17l5.5 3-1-6.6L21 9l-6-1Z"/></svg></div>
      <div class="test-quote">"হাজিরা নেওয়া থেকে শুরু করে ফি আদায় পর্যন্ত সবকিছু এখন কয়েক মিনিটে হয়ে যায়। আগে যা এক সপ্তাহ লাগত।"</div>
      <div class="test-who"><div class="test-avatar" style="background:var(--cover-maroon)">র</div><div><div class="nm">রফিকুল হক</div><div class="role">প্রিন্সিপাল, গ্রিনভিউ স্কুল অ্যান্ড কলেজ</div></div></div>
    </div>
    <div class="test-card">
      <div class="stars"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 9 8l-6 1 4.5 4.4L6.5 20 12 17l5.5 3-1-6.6L21 9l-6-1Z"/></svg><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 9 8l-6 1 4.5 4.4L6.5 20 12 17l5.5 3-1-6.6L21 9l-6-1Z"/></svg><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 9 8l-6 1 4.5 4.4L6.5 20 12 17l5.5 3-1-6.6L21 9l-6-1Z"/></svg><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 9 8l-6 1 4.5 4.4L6.5 20 12 17l5.5 3-1-6.6L21 9l-6-1Z"/></svg><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 9 8l-6 1 4.5 4.4L6.5 20 12 17l5.5 3-1-6.6L21 9l-6-1Z"/></svg></div>
      <div class="test-quote">"মাদরাসার জন্য আলাদা মডিউল থাকায় হিফজ শিক্ষার্থীদের অগ্রগতি ট্র্যাক করা এখন অনেক সহজ হয়ে গেছে।"</div>
      <div class="test-who"><div class="test-avatar" style="background:var(--teacher)">ম</div><div><div class="nm">মাওলানা ইউসুফ আলী</div><div class="role">মুহতামিম, দারুল উলুম মাদরাসা</div></div></div>
    </div>
    <div class="test-card">
      <div class="stars"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 9 8l-6 1 4.5 4.4L6.5 20 12 17l5.5 3-1-6.6L21 9l-6-1Z"/></svg><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 9 8l-6 1 4.5 4.4L6.5 20 12 17l5.5 3-1-6.6L21 9l-6-1Z"/></svg><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 9 8l-6 1 4.5 4.4L6.5 20 12 17l5.5 3-1-6.6L21 9l-6-1Z"/></svg><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 9 8l-6 1 4.5 4.4L6.5 20 12 17l5.5 3-1-6.6L21 9l-6-1Z"/></svg><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2 9 8l-6 1 4.5 4.4L6.5 20 12 17l5.5 3-1-6.6L21 9l-6-1Z"/></svg></div>
      <div class="test-quote">"অভিভাবকরা এখন সরাসরি অ্যাপ থেকে সন্তানের হাজিরা ও ফলাফল দেখতে পারেন। যোগাযোগ অনেক সহজ হয়েছে।"</div>
      <div class="test-who"><div class="test-avatar" style="background:var(--student)">ন</div><div><div class="nm">নাসরিন আক্তার</div><div class="role">পরিচালক, লিটল স্টারস কিন্ডারগার্টেন</div></div></div>
    </div>
  </div>
</section>

<section id="faq">
  <div class="wrap center">
    <div class="eyebrow">সচরাচর জিজ্ঞাসা</div>
    <h2 class="sec-title">আপনার প্রশ্নের উত্তর</h2>
    <p class="sec-sub">আরও প্রশ্ন থাকলে আমাদের সাথে সরাসরি যোগাযোগ করুন।</p>
  </div>
  <div class="wrap faq-list">
    <div class="faq-item open">
      <div class="faq-q">ফ্রি ট্রায়াল কতদিন চলে এবং কার্ড লাগবে কি?<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg></div>
      <div class="faq-a-wrap"><div class="faq-a-inner"><div class="faq-a">১৪ দিনের ফ্রি ট্রায়ালে সকল ফিচার উন্মুক্ত থাকবে। ট্রায়াল শুরু করতে কোনো কার্ড বা পেমেন্ট তথ্যের প্রয়োজন নেই।</div></div></div>
    </div>
    <div class="faq-item">
      <div class="faq-q">আমার প্রতিষ্ঠানের পুরনো তথ্য কি স্থানান্তর করা যাবে?<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg></div>
      <div class="faq-a-wrap"><div class="faq-a-inner"><div class="faq-a">হ্যাঁ। এক্সেল/সিএসভি ফাইলের মাধ্যমে শিক্ষার্থী, শিক্ষক ও ফি সংক্রান্ত তথ্য বাল্ক আপলোড করা যায়। প্রয়োজনে আমাদের টিম সরাসরি সহায়তা করে।</div></div></div>
    </div>
    <div class="faq-item">
      <div class="faq-q">মাদরাসা ও কিন্ডারগার্টেনের জন্য কি আলাদা সেটআপ লাগবে?<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg></div>
      <div class="faq-a-wrap"><div class="faq-a-inner"><div class="faq-a">না। রেজিস্ট্রেশনের সময় প্রতিষ্ঠানের ধরন বেছে নিলেই সিস্টেম সেই অনুযায়ী প্রাসঙ্গিক মডিউল ও ফিল্ড স্বয়ংক্রিয়ভাবে সাজিয়ে দেয়।</div></div></div>
    </div>
    <div class="faq-item">
      <div class="faq-q">প্ল্যান পরিবর্তন বা বাতিল করা যাবে কি?<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg></div>
      <div class="faq-a-wrap"><div class="faq-a-inner"><div class="faq-a">যেকোনো সময় প্ল্যান আপগ্রেড, ডাউনগ্রেড বা বাতিল করা যাবে। কোনো দীর্ঘমেয়াদী চুক্তির বাধ্যবাধকতা নেই।</div></div></div>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="final-cta">
      <h2>আজই আপনার প্রতিষ্ঠান ডিজিটাল করুন</h2>
      <p>৫০০+ প্রতিষ্ঠানের সাথে যুক্ত হন। রেজিস্ট্রেশন করতে মাত্র ৫ মিনিট লাগবে।</p>
      <div class="final-ctas">
        <a href="{{ route('register') }}" class="btn-primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
          ফ্রি ট্রায়াল শুরু করুন
        </a>
        <a href="#" class="btn-ghost" style="background:transparent; border-color:rgba(231,199,103,.4); color:#F3E9D2;">সেলস টিমের সাথে কথা বলুন</a>
      </div>
    </div>
  </div>
</section>

<footer>
  <div class="wrap">
    <div class="foot-grid">
      <div class="foot-brand">
        <div class="name">EDUTION</div>
        <p>বাংলাদেশের স্কুল, মাদরাসা ও কিন্ডারগার্টেনের জন্য সম্পূর্ণ ডিজিটাল ম্যানেজমেন্ট সিস্টেম। ভর্তি থেকে ফলাফল, সবকিছু এক জায়গায়।</p>
      </div>
      <div class="foot-col"><h4>প্রোডাক্ট</h4><ul><li><a href="#features">ফিচার</a></li><li><a href="#pricing">মূল্য</a></li><li><a href="#demo">ডেমো</a></li><li><a href="#">আপডেট</a></li></ul></div>
      <div class="foot-col"><h4>প্রতিষ্ঠান</h4><ul><li><a href="#">আমাদের সম্পর্কে</a></li><li><a href="#">ক্যারিয়ার</a></li><li><a href="#">ব্লগ</a></li><li><a href="#">যোগাযোগ</a></li></ul></div>
      <div class="foot-col"><h4>সহায়তা</h4><ul><li><a href="#faq">সচরাচর জিজ্ঞাসা</a></li><li><a href="#">গোপনীয়তা নীতি</a></li><li><a href="#">শর্তাবলী</a></li><li><a href="#">সাপোর্ট</a></li></ul></div>
    </div>
    <div class="foot-bottom">
      <div>© {{ date('Y') }} EDUTION। সর্বস্বত্ব সংরক্ষিত।</div>
      <div class="foot-social">
        <a href="#"><svg viewBox="0 0 24 24" fill="none" stroke="var(--ink-muted)" stroke-width="1.8"><path d="M17 2h-3a5 5 0 0 0-5 5v3H6v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a>
        <a href="#"><svg viewBox="0 0 24 24" fill="none" stroke="var(--ink-muted)" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/></svg></a>
        <a href="#"><svg viewBox="0 0 24 24" fill="none" stroke="var(--ink-muted)" stroke-width="1.8"><path d="M22 5.8c-.7.3-1.5.6-2.4.7.8-.5 1.5-1.3 1.8-2.3-.8.5-1.7.8-2.6 1a4.1 4.1 0 0 0-7 3.7A11.7 11.7 0 0 1 3.2 4.6a4.1 4.1 0 0 0 1.3 5.5c-.7 0-1.3-.2-1.9-.5v.1c0 2 1.4 3.6 3.3 4a4.1 4.1 0 0 1-1.9.1 4.1 4.1 0 0 0 3.8 2.8A8.2 8.2 0 0 1 2 18.4a11.6 11.6 0 0 0 6.3 1.8c7.5 0 11.7-6.3 11.7-11.7v-.5c.8-.6 1.5-1.3 2-2.2Z"/></svg></a>
      </div>
    </div>
  </div>
</footer>

<script>
  document.querySelectorAll('.billing-toggle button').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      document.querySelectorAll('.billing-toggle button').forEach(b=>b.classList.remove('active'));
      btn.classList.add('active');
      const mode = btn.dataset.mode;
      document.querySelectorAll('.price-grid[data-panel]').forEach(el=>{
        el.style.display = el.dataset.panel === mode ? '' : 'none';
      });
    });
  });

  document.querySelectorAll('.faq-q').forEach(q=>{
    q.addEventListener('click', ()=>{
      const item = q.closest('.faq-item');
      const isOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item.open').forEach(i=>{ if(i!==item) i.classList.remove('open'); });
      item.classList.toggle('open', !isOpen);
    });
  });
</script>
</body>
</html>
