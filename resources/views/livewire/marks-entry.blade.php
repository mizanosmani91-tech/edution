<div class="lifecycle-page">
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">পরীক্ষা ও ফলাফল / মার্কস এন্ট্রি</div>
            <h2>মার্কস এন্ট্রি</h2>
        </div>
    </div>

    @if ($savedMessage)
        <div class="alert-note" style="margin-bottom:16px;">{{ $savedMessage }}</div>
    @endif

    <div class="select-card" style="margin-bottom:16px;">
        <div class="info-grid" style="grid-template-columns:1fr 1fr;">
            <div class="field" style="margin:0;">
                <label>পরীক্ষা</label>
                <select wire:model.live="examId">
                    <option value="">নির্বাচন করুন</option>
                    @foreach ($exams as $e)
                        <option value="{{ $e->id }}">{{ $e->name }} ({{ $e->academic_year }})</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="margin:0;">
                <label>বিষয় ও শ্রেণি</label>
                <select wire:model.live="examSubjectId" @if(!$examId) disabled @endif>
                    <option value="">নির্বাচন করুন</option>
                    @foreach ($examSubjects as $es)
                        <option value="{{ $es->id }}">{{ $es->subject->name ?? '—' }} — {{ $es->schoolClass->full_label ?? '—' }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if ($this->examSubject)
        <div class="table-card">
            <div class="list-head">
                <h3>{{ $this->examSubject->subject->name ?? '' }} — {{ $this->examSubject->schoolClass->full_label ?? '' }}</h3>
                <span style="font-size:12.5px;color:var(--ink-soft);">পূর্ণমান: {{ $this->examSubject->full_marks }} | পাশ মার্ক: {{ $this->examSubject->pass_marks }}</span>
            </div>
            <table>
                <thead><tr><th>নাম</th><th>আইডি নং</th><th style="width:140px;">প্রাপ্ত নম্বর</th><th style="width:100px;">অনুপস্থিত</th></tr></thead>
                <tbody>
                    @forelse ($students as $s)
                        <tr wire:key="me-{{ $s->id }}">
                            <td>{{ $s->name }}</td>
                            <td>{{ $s->student_id_no }}</td>
                            <td><input type="number" step="0.01" min="0" max="{{ $this->examSubject->full_marks }}" wire:model="marks.{{ $s->id }}" @if($absent[$s->id] ?? false) disabled @endif style="width:100px;"></td>
                            <td style="text-align:center;"><input type="checkbox" wire:model="absent.{{ $s->id }}"></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align:center;color:var(--ink-soft);padding:30px 0;">এই শ্রেণিতে কোনো সক্রিয় শিক্ষার্থী নেই</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="att-save-bar">
            <span style="font-size:12.5px;color:var(--ink-soft);">সব শিক্ষার্থীর মার্কস দিয়ে সংরক্ষণ করুন</span>
            <button class="btn-primary" wire:click="save" type="button">সংরক্ষণ করুন</button>
        </div>
    @else
        <div class="picker-card" style="text-align:center;color:var(--ink-soft);padding:40px 20px;">মার্কস দিতে পরীক্ষা ও বিষয় নির্বাচন করুন</div>
    @endif
</div>
