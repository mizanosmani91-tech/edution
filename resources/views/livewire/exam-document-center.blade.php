<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">পরীক্ষা ও ফলাফল</div>
            <h2 style="margin:0;">পরীক্ষার ডকুমেন্ট সেন্টার</h2>
            <p>পরীক্ষা ও শ্রেণি বেছে নিন — নিচে সব প্রিন্টযোগ্য ডকুমেন্টের লিংক দেখাবে</p>
        </div>
    </div>

    <div class="cert-form-card lifecycle-page" style="margin-bottom:20px;">
        <div class="info-grid" style="grid-template-columns:1fr 1fr;">
            <div class="field">
                <label>পরীক্ষা নির্বাচন করুন</label>
                <select wire:model.live="examId">
                    <option value="">নির্বাচন করুন</option>
                    @foreach ($exams as $e)
                        <option value="{{ $e->id }}">{{ $e->name }} ({{ $e->academic_year }})</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>শ্রেণি (ক্লাস-ভিত্তিক ডকুমেন্টের জন্য দরকার)</label>
                <select wire:model.live="classId">
                    <option value="">নির্বাচন করুন</option>
                    @foreach ($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->full_label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if ($examId)
        <h3 style="font-family:'Tiro Bangla',serif;font-size:16px;margin:0 0 12px;">হল-ভিত্তিক ডকুমেন্ট (শুধু পরীক্ষা লাগবে)</h3>
        <div class="info-grid" style="grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:24px;">
            <a class="cert-form-card lifecycle-page" style="text-decoration:none;color:inherit;padding:16px;" href="{{ route('exam-seat-plan.print', ['exam_id' => $examId]) }}" target="_blank">
                <b>সিট প্ল্যান</b>
                <p style="margin:4px 0 0;font-size:12.5px;color:var(--ink-soft);">রুম-ওয়াইজ সিট বিন্যাস</p>
            </a>
            <a class="cert-form-card lifecycle-page" style="text-decoration:none;color:inherit;padding:16px;" href="{{ route('exam-attendance.print', ['exam_id' => $examId]) }}" target="_blank">
                <b>উপস্থিতি/অনুপস্থিতি শীট</b>
                <p style="margin:4px 0 0;font-size:12.5px;color:var(--ink-soft);">স্বাক্ষর কলাম সহ</p>
            </a>
            <a class="cert-form-card lifecycle-page" style="text-decoration:none;color:inherit;padding:16px;" href="{{ route('exam-hall-duty.print', ['exam_id' => $examId]) }}" target="_blank">
                <b>হল ডিউটি তালিকা</b>
                <p style="margin:4px 0 0;font-size:12.5px;color:var(--ink-soft);">দায়িত্বরত শিক্ষকদের তালিকা</p>
            </a>
        </div>

        <p style="font-size:12.5px;color:var(--ink-soft);margin:-12px 0 20px;">সিট প্ল্যান তৈরি করতে বা হলের শিক্ষক নির্ধারণ করতে যান <a href="{{ route('exam-seat-plan.index') }}">সিট প্ল্যান ম্যানেজার</a>-এ।</p>

        @if ($classId)
            <h3 style="font-family:'Tiro Bangla',serif;font-size:16px;margin:0 0 12px;">শ্রেণি-ভিত্তিক ডকুমেন্ট</h3>
            <div class="info-grid" style="grid-template-columns:repeat(3,1fr);gap:12px;">
                <a class="cert-form-card lifecycle-page" style="text-decoration:none;color:inherit;padding:16px;" href="{{ route('admit-cards.class', ['exam_id' => $examId, 'class_id' => $classId]) }}" target="_blank">
                    <b>এডমিট কার্ড</b>
                    <p style="margin:4px 0 0;font-size:12.5px;color:var(--ink-soft);">A4-তে ২টা কার্ড প্রতি পাতায়</p>
                </a>
                <a class="cert-form-card lifecycle-page" style="text-decoration:none;color:inherit;padding:16px;" href="{{ route('marksheet.class', ['exam_id' => $examId, 'class_id' => $classId]) }}" target="_blank">
                    <b>মার্কশিট (ছাত্র-প্রতি)</b>
                    <p style="margin:4px 0 0;font-size:12.5px;color:var(--ink-soft);">গ্রেড ও পাস/ফেল সহ</p>
                </a>
                <a class="cert-form-card lifecycle-page" style="text-decoration:none;color:inherit;padding:16px;" href="{{ route('marksheet.class-tabulation', ['exam_id' => $examId, 'class_id' => $classId]) }}" target="_blank">
                    <b>ট্যাবুলেশন শীট</b>
                    <p style="margin:4px 0 0;font-size:12.5px;color:var(--ink-soft);">মেধাক্রম সহ প্রতিষ্ঠানের রেকর্ড</p>
                </a>
                <a class="cert-form-card lifecycle-page" style="text-decoration:none;color:inherit;padding:16px;" href="{{ route('answer-sheet-distribution.print', ['exam_id' => $examId, 'class_id' => $classId]) }}" target="_blank">
                    <b>উত্তরপত্র বন্টন</b>
                    <p style="margin:4px 0 0;font-size:12.5px;color:var(--ink-soft);">বিষয়-শিক্ষক-ছাত্র সংখ্যা সহ</p>
                </a>
            </div>
        @else
            <p style="color:var(--ink-soft);font-size:13px;">শ্রেণি-ভিত্তিক ডকুমেন্ট দেখতে উপরে থেকে একটা শ্রেণি নির্বাচন করুন।</p>
        @endif

        <h3 style="font-family:'Tiro Bangla',serif;font-size:16px;margin:24px 0 12px;">অন্যান্য</h3>
        <div class="info-grid" style="grid-template-columns:repeat(3,1fr);gap:12px;">
            <a class="cert-form-card lifecycle-page" style="text-decoration:none;color:inherit;padding:16px;" href="{{ route('question-papers.index') }}">
                <b>প্রশ্নপত্র</b>
                <p style="margin:4px 0 0;font-size:12.5px;color:var(--ink-soft);">লিখুন, রিভিউ করুন, প্রিন্ট করুন</p>
            </a>
        </div>
    @endif
</div>
