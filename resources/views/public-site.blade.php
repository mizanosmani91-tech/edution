<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="manifest" href="/manifest.webmanifest">
<meta name="theme-color" content="#6C5CE7">
<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
    }
</script>
<title>{{ $institution->name }}</title>
<meta name="description" content="{{ $institution->name }} — {{ $institution->address ?? 'EDUTION দিয়ে পরিচালিত একটি শিক্ষা প্রতিষ্ঠান' }}">
@if ($institution->favicon_path)
    <link rel="icon" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($institution->favicon_path) }}">
@endif
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tiro+Bangla:ital@0;1&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
@php
    $__settings = $institution->settings;
    $__primary = $__settings?->theme_primary_color ?? '#6C5CE7';
    $__accent = $__settings?->theme_accent_color ?? '#F59E0B';
@endphp
<style>
  :root{
    --maroon: {{ $__primary }}; --maroon-deep:#4B3FC4;
    --gold: {{ $__accent }}; --gold-light:#FBBF24;
    --paper:#F7F8FC; --paper-deep:#EEF1FA; --card:#FFFFFF;
    --ink:#1F2432; --ink-muted:#6B7280; --ink-soft:#9CA3AF;
    --line:rgba(31,36,50,.10);
  }
  *{box-sizing:border-box;}
  html,body{margin:0;padding:0;}
  body{ font-family:'Hind Siliguri',sans-serif; background:var(--paper); color:var(--ink); }
  a{color:inherit;text-decoration:none;}
  img,svg{display:block;}
  .wrap{max-width:900px;margin:0 auto;padding:0 20px;}

  .hero{ background:linear-gradient(180deg, var(--maroon), var(--maroon-deep)); color:#F7F8FC; padding:48px 0 60px; }
  .hero-inner{ display:flex; align-items:center; gap:18px; flex-wrap:wrap; }
  .logo-box{ width:76px;height:76px;border-radius:18px;overflow:hidden;background:#fff;flex-shrink:0;display:flex;align-items:center;justify-content:center; }
  .logo-box img{ width:100%;height:100%;object-fit:cover; }
  .hero h1{ font-family:'Tiro Bangla',serif; font-size:28px; margin:0 0 6px; }
  .hero p{ margin:0; color:#E7DEC5; font-size:14px; }
  .hero .badges{ margin-top:14px; display:flex; gap:8px; flex-wrap:wrap; }
  .badge{ font-size:12px; background:rgba(255,255,255,.12); padding:5px 12px; border-radius:20px; }
  .login-btn{ margin-top:20px; display:inline-flex; align-items:center; gap:8px; background:linear-gradient(180deg, var(--gold-light), var(--gold)); color:#4B3FC4; font-weight:700; padding:12px 22px; border-radius:12px; font-size:14px; }

  .card{ background:var(--card); border:1px solid var(--line); border-radius:16px; padding:22px; margin-top:-30px; position:relative; z-index:2; box-shadow:0 20px 40px -25px rgba(0,0,0,.2); }
  .info-row{ display:flex; gap:10px; padding:10px 0; border-bottom:1px dashed var(--line); font-size:13.5px; }
  .info-row:last-child{ border-bottom:0; }
  .info-row .lbl{ color:var(--ink-soft); min-width:110px; }

  .sec{ margin-top:30px; }
  .sec h2{ font-family:'Tiro Bangla',serif; font-size:20px; margin:0 0 14px; }
  .notice{ background:var(--card); border:1px solid var(--line); border-radius:12px; padding:14px 16px; margin-bottom:10px; }
  .notice .title{ font-weight:700; font-size:14px; }
  .notice .date{ font-size:11.5px; color:var(--ink-soft); margin-top:3px; }
  .notice .body{ font-size:13px; color:var(--ink-muted); margin-top:6px; line-height:1.6; }
  .empty{ text-align:center; color:var(--ink-soft); padding:30px 0; font-size:13.5px; }

  .footer{ text-align:center; padding:40px 0 30px; font-size:12px; color:var(--ink-soft); }
  .footer a{ color:var(--maroon); font-weight:700; }
</style>
</head>
<body>

<div class="hero">
    <div class="wrap hero-inner">
        @if ($institution->logo_path)
            <div class="logo-box"><img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($institution->logo_path) }}" alt="{{ $institution->name }}"></div>
        @endif
        <div>
            <h1>{{ $institution->name }}</h1>
            @if ($institution->address)
                <p>{{ $institution->address }}</p>
            @endif
            <div class="badges">
                @if ($institution->institution_type)<span class="badge">{{ $institution->institution_type }}</span>@endif
                @if ($institution->founding_year)<span class="badge">প্রতিষ্ঠাকাল: {{ $institution->founding_year }}</span>@endif
                @if ($institution->eiin)<span class="badge">EIIN: {{ $institution->eiin }}</span>@endif
            </div>
        </div>
    </div>
</div>

<div class="wrap">
    <div class="card">
        <div class="info-row"><span class="lbl">প্রতিষ্ঠান</span><span>{{ $institution->name }}</span></div>
        @if ($institution->phone)<div class="info-row"><span class="lbl">ফোন</span><span>{{ $institution->phone }}</span></div>@endif
        @if ($institution->division || $institution->district)<div class="info-row"><span class="lbl">অবস্থান</span><span>{{ collect([$institution->district, $institution->division])->filter()->implode(', ') }}</span></div>@endif
        @if ($institution->address)<div class="info-row"><span class="lbl">ঠিকানা</span><span>{{ $institution->address }}</span></div>@endif
    </div>

    <div style="text-align:center;">
        <a href="{{ route('login') }}" class="login-btn">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/></svg>
            পোর্টালে লগইন করুন
        </a>
    </div>

    <div class="sec">
        <h2>সাম্প্রতিক নোটিশ</h2>
        @forelse ($notices as $notice)
            <div class="notice">
                <div class="title">{{ $notice->is_urgent ? '🔴 ' : '' }}{{ $notice->title }}</div>
                <div class="date">{{ $notice->publish_at?->format('d M, Y') }}</div>
                @if ($notice->body)
                    <div class="body">{{ \Illuminate\Support\Str::limit(strip_tags($notice->body), 180) }}</div>
                @endif
            </div>
        @empty
            <div class="empty">এখনো কোনো পাবলিক নোটিশ প্রকাশ করা হয়নি।</div>
        @endforelse
    </div>

    <div class="footer">
        এই ওয়েবসাইট পরিচালিত হচ্ছে <a href="https://edution.xyz" target="_blank">EDUTION</a> দিয়ে
    </div>
</div>

</body>
</html>
