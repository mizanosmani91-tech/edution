<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">পরীক্ষা ও ফলাফল / {{ $mode === 'qawmi' ? 'কওমি গ্রেডিং' : 'GPA ও Merit List' }}</div>
            <h2>{{ $mode === 'qawmi' ? 'কওমি গ্রেডিং তালিকা' : 'GPA / গ্রেড ও Merit List' }}</h2>
            @if ($mode === 'qawmi')
                <p style="font-size:12px;color:var(--ink-soft);">মুমতাজ (৮০+) · জাইয়িদ জিদ্দান (৭০-৭৯) · জাইয়িদ (৬০-৬৯) · মাকবুল (৫০-৫৯) · রাসিব (৫০ এর নিচে)</p>
            @endif
        </div>
    </div>

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
                <label>শ্রেণি</label>
                <select wire:model.live="classId">
                    <option value="">নির্বাচন করুন</option>
                    @foreach ($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->full_label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="table-card">
        <table>
            <thead><tr><th>মেধাক্রম</th><th>নাম</th><th>আইডি নং</th><th>প্রাপ্ত/পূর্ণমান</th><th>শতকরা</th><th>{{ $mode === 'qawmi' ? 'বিভাগ' : 'গ্রেড' }}</th><th>{{ $mode === 'qawmi' ? 'পয়েন্ট' : 'GPA' }}</th><th>স্ট্যাটাস</th></tr></thead>
            <tbody>
                @forelse ($results as $r)
                    <tr wire:key="ml-{{ $r['student']->id }}">
                        <td>{{ $r['rank'] ?? '—' }}</td>
                        <td>{{ $r['student']->name }}</td>
                        <td>{{ $r['student']->student_id_no }}</td>
                        <td>{{ number_format($r['obtained'], 1) }} / {{ number_format($r['full'], 1) }}</td>
                        <td>{{ $r['percentage'] }}%</td>
                        <td>{{ $r['grade'] }}</td>
                        <td>{{ $r['gpa'] }}</td>
                        <td>
                            @if ($r['is_absent'])
                                <span class="pill due">অনুপস্থিত</span>
                            @elseif ($r['is_pass'])
                                <span class="pill active">পাশ</span>
                            @else
                                <span class="pill due">ফেল</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align:center;color:var(--ink-soft);padding:30px 0;">পরীক্ষা ও শ্রেণি নির্বাচন করলে ফলাফল দেখাবে</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
