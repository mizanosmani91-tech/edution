<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <title>ফি রশিদ — বিদ্যাপঞ্জি</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{ --maroon:#7a1f2b; --gold:#c8a13a; --ink:#28211f; --ink-soft:#7a716c; --line:#e7ded4; }
        *{box-sizing:border-box;}
        body{font-family:'Hind Siliguri',sans-serif;color:var(--ink);background:#f3efe9;margin:0;padding:24px;}
        .sheet{max-width:720px;margin:0 auto;background:#fff;border-radius:14px;box-shadow:0 6px 24px rgba(122,31,43,.08);overflow:hidden;}
        .head{background:linear-gradient(135deg,var(--maroon),#5a1520);color:#fff;padding:26px 28px;display:flex;justify-content:space-between;align-items:center;}
        .head h1{margin:0;font-size:19px;}
        .head p{margin:4px 0 0;font-size:12.5px;opacity:.85;}
        .badge{background:#2c8a4a;color:#fff;font-size:12px;font-weight:600;padding:6px 14px;border-radius:999px;}
        .body{padding:26px 28px;}
        .meta-row{display:flex;justify-content:space-between;font-size:13px;color:var(--ink-soft);margin-bottom:18px;border-bottom:1px dashed var(--line);padding-bottom:14px;}
        .grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px 24px;margin-bottom:20px;}
        .field .k{font-size:11.5px;color:var(--ink-soft);margin-bottom:2px;}
        .field .v{font-size:14.5px;font-weight:600;}
        table{width:100%;border-collapse:collapse;margin-bottom:16px;}
        th,td{padding:10px 6px;text-align:left;font-size:13.5px;}
        thead th{border-bottom:2px solid var(--maroon);color:var(--maroon);font-size:12px;text-transform:uppercase;}
        tbody tr{border-bottom:1px solid var(--line);}
        tfoot td{border-top:2px solid var(--ink);font-weight:700;font-size:15px;padding-top:12px;}
        .amt{text-align:right;}
        .words{background:#faf6ee;border:1px dashed var(--gold);border-radius:8px;padding:10px 14px;font-size:13px;margin-bottom:22px;}
        .sign-row{display:flex;justify-content:space-between;margin-top:50px;font-size:12.5px;color:var(--ink-soft);}
        .sign-row div{width:44%;text-align:center;border-top:1px solid var(--ink);padding-top:6px;}
        .actions{max-width:720px;margin:16px auto 0;display:flex;justify-content:flex-end;gap:10px;}
        .actions button{background:var(--maroon);color:#fff;border:none;border-radius:8px;padding:9px 18px;font-size:13.5px;cursor:pointer;}
        @media print{ body{background:#fff;padding:0;} .actions{display:none;} .sheet{box-shadow:none;border-radius:0;} }
    </style>
</head>
<body>
    <div class="actions">
        <button onclick="window.print()">প্রিন্ট করুন</button>
    </div>
    <div class="sheet">
        <div class="head">
            <div>
                <h1>{{ $fee->institution->name ?? 'বিদ্যাপঞ্জি' }}</h1>
                <p>পরিচালিত হয় বিদ্যাপঞ্জি এডুকেশন ম্যানেজমেন্ট সিস্টেমের মাধ্যমে</p>
            </div>
            <span class="badge">{{ $fee->status === 'paid' ? 'পরিশোধিত' : ($fee->status === 'partial' ? 'আংশিক পরিশোধিত' : 'বকেয়া') }}</span>
        </div>
        <div class="body">
            <div class="meta-row">
                <div>রশিদ নম্বর: <strong>{{ $receiptNo }}</strong></div>
                <div>তারিখ: {{ ($fee->paid_at ?? $fee->created_at)?->format('d F, Y') }}</div>
            </div>

            <div class="grid2">
                <div class="field"><div class="k">শিক্ষার্থীর নাম</div><div class="v">{{ $fee->student->name }}</div></div>
                <div class="field"><div class="k">স্টুডেন্ট আইডি</div><div class="v">{{ $fee->student->student_id_no ?? '—' }}</div></div>
                <div class="field"><div class="k">শ্রেণি / শাখা</div><div class="v">{{ $fee->student->schoolClass->full_label ?? '—' }}@if($fee->student->section), {{ $fee->student->section->name }}@endif</div></div>
                <div class="field"><div class="k">অভিভাবকের নাম</div><div class="v">{{ $fee->student->guardians->first()->name ?? '—' }}</div></div>
                <div class="field"><div class="k">পেমেন্ট মাধ্যম</div><div class="v">{{ match($fee->payment_method) { 'bkash' => 'বিকাশ', 'nagad' => 'নগদ (মোবাইল)', 'bank_transfer' => 'ব্যাংক ট্রান্সফার', default => 'নগদ' } }}</div></div>
                <div class="field"><div class="k">গ্রহণকারী</div><div class="v">{{ $collector->name ?? 'অফিস' }}</div></div>
            </div>

            <table>
                <thead><tr><th>বিবরণ</th><th class="amt">পরিমাণ</th></tr></thead>
                <tbody>
                    <tr><td>{{ $fee->due_month }} — {{ $fee->fee_type }}</td><td class="amt">৳{{ number_format($fee->amount_due, 2) }}</td></tr>
                    @if ($fee->fine_amount > 0)
                        <tr><td>জরিমানা @if($fee->fine_reason)({{ $fee->fine_reason }})@endif</td><td class="amt">৳{{ number_format($fee->fine_amount, 2) }}</td></tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr><td>মোট পরিশোধিত</td><td class="amt">৳{{ number_format($fee->amount_paid, 2) }}</td></tr>
                </tfoot>
            </table>

            <div class="words">কথায়: {{ $amountInWords }}</div>

            @if ($fee->transaction_ref)
                <div style="font-size:12.5px;color:var(--ink-soft);margin-bottom:10px;">লেনদেন আইডি: {{ $fee->transaction_ref }}</div>
            @endif

            <div class="sign-row">
                <div>অভিভাবকের স্বাক্ষর</div>
                <div>অফিস কর্তৃপক্ষের স্বাক্ষর ও সিল</div>
            </div>
        </div>
    </div>
</body>
</html>
