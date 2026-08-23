<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; margin: 0; }
        .page { display: table; width: 100%; table-layout: fixed; }
        .copy { display: table-cell; width: 50%; vertical-align: top; padding: 10px 14px; }
        .copy + .copy { border-left: 1px dashed #999; }
        .institution-name { text-align: center; font-size: 15px; font-weight: bold; margin-bottom: 2px; }
        .exam-title { text-align: center; font-size: 12px; margin-bottom: 2px; }
        .class-line { text-align: center; font-size: 12px; font-weight: bold; margin-bottom: 2px; }
        .subject-line { text-align: center; font-size: 12.5px; font-weight: bold; margin-bottom: 8px; }
        .meta-row { display: table; width: 100%; font-size: 11px; margin-bottom: 10px; }
        .meta-row span { display: table-cell; }
        .meta-row span:last-child { text-align: right; }
        .q-block { margin-bottom: 10px; }
        .q-head { font-weight: bold; }
        .q-marks { float: right; }
        .q-content { white-space: pre-wrap; margin-top: 4px; line-height: 1.7; }
    </style>
</head>
<body>
    <div class="page">
        @for ($copy = 0; $copy < 2; $copy++)
            <div class="copy">
                <p class="institution-name">{{ $institution->name }}</p>
                <p class="exam-title">{{ $paper->exam->name ?? '' }} ({{ $paper->exam->academic_year ?? '' }})</p>
                <p class="class-line">{{ $paper->schoolClass->full_label ?? '' }}</p>
                <p class="subject-line">বিষয়ঃ {{ $paper->subject->name ?? '' }}</p>
                <div class="meta-row">
                    <span>সময়ঃ {{ $paper->duration_text }}</span>
                    <span>পূর্ণমানঃ {{ (int) $paper->full_marks }}</span>
                </div>

                @foreach ($paper->items as $idx => $item)
                    <div class="q-block">
                        <span class="q-marks">{{ rtrim(rtrim(number_format($item->marks, 2), '0'), '.') }}</span>
                        <span class="q-head">{{ $idx + 1 }}) {{ $item->heading }}</span>
                        <div class="q-content">{{ $item->content }}</div>
                    </div>
                @endforeach
            </div>
        @endfor
    </div>
</body>
</html>
