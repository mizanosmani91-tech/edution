<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    {{--
        ⚠️ dompdf Tailwind সাপোর্ট করে না — plain inline CSS এখানে জরুরি,
        ওয়েব পেজের মতো utility class কাজ করবে না।
    --}}
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { font-size: 16px; text-align: center; margin-bottom: 2px; }
        .subtitle { text-align: center; color: #555; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 5px 8px; text-align: left; }
        th { background: #f3f4f6; }
        .fail { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>
    <h1>{{ $exam->name }} — মার্কশিট</h1>
    <p class="subtitle">{{ $class->full_label }}</p>

    <table>
        <thead>
            <tr>
                <th>ক্রমিক</th>
                <th>নাম</th>
                <th>আইডি</th>
                @foreach ($examSubjects as $es)
                    <th>{{ $es->subject->name }}</th>
                @endforeach
                <th>মোট</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($students as $i => $student)
                @php
                    $studentMarks = $marksByStudent->get($student->id, collect());
                    $total = $studentMarks->sum('marks_obtained');
                    $totalMax = $studentMarks->sum('full_marks');
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $student->name }}</td>
                    <td>{{ $student->student_id_no }}</td>
                    @foreach ($studentMarks as $mark)
                        <td class="{{ $mark->is_absent ? 'fail' : '' }}">
                            {{ $mark->is_absent ? 'অনুপস্থিত' : $mark->marks_obtained }}
                        </td>
                    @endforeach
                    <td><strong>{{ $total }}/{{ $totalMax }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
