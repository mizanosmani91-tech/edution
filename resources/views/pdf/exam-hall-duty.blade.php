<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: notosansbengali, sans-serif; font-size: 12px; }
        .institution-name { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 2px; }
        .exam-title { text-align: center; font-size: 13px; margin-bottom: 4px; }
        .head-title { text-align: center; font-size: 14px; font-weight: bold; margin: 14px 0 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 8px 10px; text-align: left; }
        th { background: #eee; }
        .sig-col { width: 160px; }
    </style>
</head>
<body>
    <p class="institution-name">{{ $institution->name }}</p>
    <p class="exam-title">{{ $exam->name }} ({{ $exam->academic_year }})</p>
    <p class="head-title">হলে দায়িত্বরত শিক্ষকদের তালিকা</p>

    <table>
        <thead>
            <tr><th>হল/রুম নং</th><th>মোট আসন</th><th>দায়িত্বরত শিক্ষক</th><th class="sig-col">স্বাক্ষর</th></tr>
        </thead>
        <tbody>
            @foreach ($rooms as $room)
                <tr>
                    <td>{{ $room->room_name }}</td>
                    <td>{{ $room->assignments_count }}</td>
                    <td>{{ $room->assignedTeacher->name ?? '—' }}</td>
                    <td class="sig-col">&nbsp;</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top:40px;">.....................................<br>অধ্যক্ষ / পরীক্ষা নিয়ন্ত্রক</p>
</body>
</html>
