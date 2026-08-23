<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: notosansbengali, sans-serif; font-size: 12px; }
        .card {
            border: 1.5px solid #333;
            padding: 16px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        .card h2 { font-size: 14px; margin: 0 0 4px; }
        .card p { margin: 2px 0; }
        .institution-name { text-align: center; font-size: 15px; font-weight: bold; }
    </style>
</head>
<body>
    @foreach ($students as $student)
        <div class="card">
            <p class="institution-name">{{ $institution->name }}</p>
            <h2>প্রবেশপত্র — {{ $exam->name }}</h2>
            <p>নাম: {{ $student->name }}</p>
            <p>আইডি: {{ $student->student_id_no }}</p>
            <p>শ্রেণি: {{ $class->full_label }}</p>
        </div>
    @endforeach
</body>
</html>
