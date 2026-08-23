<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: notosansbengali, sans-serif; font-size: 10px; }
        .institution-name { text-align: center; font-size: 15px; font-weight: bold; margin-bottom: 2px; }
        h1 { font-size: 13px; text-align: center; margin: 4px 0 2px; }
        .subtitle { text-align: center; color: #555; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        th, td { border: 1px solid #999; padding: 3px 5px; text-align: center; }
        th { background: #eee; }
        td.name { text-align: left; }
        .fail { color: #dc2626; font-weight: bold; }
        .sign-row { margin-top: 50px; display: table; width: 100%; }
        .sign-box { display: table-cell; width: 33%; text-align: center; font-size: 11px; }
        .sign-line { border-top: 1px solid #333; margin: 0 20px; padding-top: 4px; }
    </style>
</head>
<body>
    <p class="institution-name">{{ $institution->name }}</p>
    <h1>ট্যাবুলেশন শীট (Tabulation Sheet)</h1>
    <p class="subtitle">{{ $exam->name }} ({{ $exam->academic_year }}) — {{ $class->full_label }}</p>

    <table>
        <thead>
            <tr>
                <th>ক্রমিক</th>
                <th>আইডি</th>
                <th>নাম</th>
                @foreach ($examSubjects as $es)
                    <th>{{ $es->subject->name }}<br>({{ $es->full_marks }})</th>
                @endforeach
                <th>মোট</th>
                <th>শতকরা</th>
                <th>গ্রেড</th>
                <th>মেধাক্রম</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $row['student']->student_id_no }}</td>
                    <td class="name">{{ $row['student']->name }}</td>
                    @foreach ($examSubjects as $es)
                        @php
                            $mark = $row['marks']->firstWhere('exam_subject_id', $es->id);
                        @endphp
                        <td class="{{ $mark && $mark->is_absent ? 'fail' : '' }}">
                            {{ $mark ? ($mark->is_absent ? 'অনু.' : $mark->marks_obtained) : '—' }}
                        </td>
                    @endforeach
                    <td><strong>{{ $row['total'] }}/{{ $row['totalMax'] }}</strong></td>
                    <td>{{ $row['percentage'] }}%</td>
                    <td class="{{ $row['is_pass'] ? '' : 'fail' }}">{{ $row['grade'] }}</td>
                    <td>{{ $row['position'] ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="sign-row">
        <div class="sign-box"><div class="sign-line">মার্কস এন্ট্রিকারী</div></div>
        <div class="sign-box"><div class="sign-line">পরীক্ষা নিয়ন্ত্রক</div></div>
        <div class="sign-box"><div class="sign-line">প্রধান শিক্ষক/অধ্যক্ষ</div></div>
    </div>
</body>
</html>
