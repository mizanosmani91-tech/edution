<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 16px; text-align: center; margin-bottom: 4px; }
        .info { text-align: center; margin-bottom: 20px; color: #444; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f3f4f6; }
        .fail { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>
    <h1>{{ $exam->name }} — মার্কশিট</h1>
    <p class="info">{{ $student->name }} · আইডি: {{ $student->student_id_no }}</p>

    <table>
        <thead>
            <tr><th>বিষয়</th><th>প্রাপ্ত নম্বর</th><th>পূর্ণমান</th><th>ফলাফল</th></tr>
        </thead>
        <tbody>
            @foreach ($results as $result)
                <tr>
                    <td>{{ $result['subject'] }}</td>
                    <td>{{ $result['is_absent'] ? 'অনুপস্থিত' : $result['final_marks'] }}</td>
                    <td>{{ $result['final_max_marks'] }}</td>
                    <td class="{{ $result['is_pass'] ? '' : 'fail' }}">
                        {{ $result['is_pass'] ? 'উত্তীর্ণ' : 'অনুত্তীর্ণ' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
