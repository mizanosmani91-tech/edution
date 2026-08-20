<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        .institution-name { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 2px; }
        .exam-title { text-align: center; font-size: 13px; margin-bottom: 14px; }
        .room-page { page-break-after: always; }
        .room-page:last-child { page-break-after: auto; }
        .room-head { font-size: 15px; font-weight: bold; border-bottom: 2px solid #333; padding-bottom: 6px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 5px 7px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
    @foreach ($rooms as $room)
        <div class="room-page">
            <p class="institution-name">{{ $institution->name }}</p>
            <p class="exam-title">{{ $exam->name }} ({{ $exam->academic_year }}) — সিট প্ল্যান</p>
            <p class="room-head">রুম: {{ $room->room_name }} — মোট আসন: {{ $room->assignments->count() }}</p>

            <table>
                <thead>
                    <tr><th>সিট নং</th><th>ছাত্রের নাম</th><th>আইডি</th><th>শ্রেণি/শাখা</th></tr>
                </thead>
                <tbody>
                    @foreach ($room->assignments as $a)
                        <tr>
                            <td>{{ $a->seat_no }}</td>
                            <td>{{ $a->student->name }}</td>
                            <td>{{ $a->student->student_id_no }}</td>
                            <td>{{ $a->student->schoolClass->full_label ?? '' }} {{ $a->student->section->name ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</body>
</html>
