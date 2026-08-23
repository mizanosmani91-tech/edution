<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: notosansbengali, sans-serif; font-size: 12px; }
        .institution-name { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 2px; }
        .sub-line { text-align: center; font-size: 11px; margin-bottom: 4px; }
        .exam-title { text-align: center; font-size: 13px; font-weight: bold; margin-bottom: 4px; }
        .head-title { text-align: center; font-size: 14px; font-weight: bold; margin: 12px 0 4px; }
        .meta { text-align: right; font-size: 11px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #999; padding: 8px 10px; text-align: left; }
        th { background: #eee; }
        .sig-col { width: 150px; }
    </style>
</head>
<body>
    <p class="institution-name">{{ $institution->name }}</p>
    <p class="exam-title">{{ $exam->name }} ({{ $exam->academic_year }})</p>
    <p class="head-title">পরীক্ষার উত্তর পত্র বন্টন</p>
    <p class="meta">শ্রেণিঃ {{ $class->full_label ?? $class->name }} &nbsp;&nbsp;&nbsp; মোট ছাত্রঃ {{ $studentCount }} &nbsp;&nbsp;&nbsp; তারিখঃ .....................................</p>

    <table>
        <thead>
            <tr><th>বিষয়</th><th>বিষয় শিক্ষকের নাম</th><th>মোট ছাত্র</th><th>মোট উত্তরপত্র</th><th class="sig-col">স্বাক্ষর</th></tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row->subject->name ?? '—' }}</td>
                    <td>{{ $row->teacher->name ?? '—' }}</td>
                    <td>{{ $studentCount }}</td>
                    <td>&nbsp;</td>
                    <td class="sig-col">&nbsp;</td>
                </tr>
            @empty
                @for ($i = 0; $i < 10; $i++)
                    <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td class="sig-col">&nbsp;</td></tr>
                @endfor
            @endforelse
        </tbody>
    </table>
</body>
</html>
