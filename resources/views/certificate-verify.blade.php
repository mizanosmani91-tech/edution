<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>সার্টিফিকেট ভেরিফিকেশন — EDUTION</title>
    @vite(['resources/css/app.css'])
</head>
<body style="background:var(--paper,#F7F8FC); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; font-family:'Hind Siliguri',sans-serif;">
    <div style="max-width:480px; width:100%; background:#fff; border-radius:16px; box-shadow:0 8px 32px rgba(0,0,0,.08); padding:32px 28px; text-align:center;">
        <div style="width:52px;height:52px;border-radius:50%;background:var(--cover-maroon,#6C5CE7);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="#fff" stroke-width="2"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>
        </div>

        @if ($certificate)
            <h2 style="margin:0 0 4px; color:#1a9d5c;">✓ সার্টিফিকেট বৈধ (Verified)</h2>
            <p style="color:var(--ink-soft,#6B7280); font-size:13px; margin:0 0 24px;">এই সার্টিফিকেটটি EDUTION প্ল্যাটফর্মের রেকর্ডের সাথে মিলে গেছে।</p>

            <div style="text-align:left; background:var(--paper-deep,#EEF1FA); border-radius:12px; padding:18px 20px; font-size:14px; line-height:2;">
                <div><strong>শিক্ষার্থীর নাম:</strong> {{ $certificate->student->name ?? '—' }}</div>
                <div><strong>শ্রেণি:</strong> {{ $certificate->student->schoolClass->name ?? '—' }} @if($certificate->student?->section) — {{ $certificate->student->section->name }} @endif</div>
                <div><strong>সার্টিফিকেটের ধরন:</strong> {{ $certificate->type_label }}</div>
                <div><strong>সার্টিফিকেট নম্বর:</strong> {{ $certificate->certificate_no }}</div>
                <div><strong>ইস্যুর তারিখ:</strong> {{ $certificate->issue_date?->format('d M, Y') }}</div>
                <div><strong>প্রতিষ্ঠান:</strong> {{ $certificate->institution->name ?? '—' }}</div>
            </div>
        @else
            <h2 style="margin:0 0 4px; color:#D9534F;">✗ সার্টিফিকেট পাওয়া যায়নি</h2>
            <p style="color:var(--ink-soft,#6B7280); font-size:13px; margin:0;">এই QR কোডের সাথে মিলে এমন কোনো বৈধ সার্টিফিকেট রেকর্ড পাওয়া যায়নি। এটা জাল হতে পারে অথবা লিংকটি ভুল।</p>
        @endif

        <div style="margin-top:28px; font-size:11px; color:var(--ink-muted,#9CA3AF);">Powered by EDUTION</div>
    </div>
</body>
</html>
