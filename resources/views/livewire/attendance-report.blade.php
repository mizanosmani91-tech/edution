<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">হাজিরা / হাজিরা রিপোর্ট</div>
            <h2>হাজিরা রিপোর্ট</h2>
            <p>শ্রেণি ও তারিখ পরিসর নির্বাচন করে সার্বিক উপস্থিতির হার দেখুন</p>
        </div>
        @if ($classId)
            <a href="{{ route('export.attendance', ['from' => $from, 'to' => $to]) }}" class="btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0-4-4m4 4 4-4"/><path d="M4 19h16"/></svg>
                CSV এক্সপোর্ট
            </a>
        @endif
    </div>

    <div class="select-card">
        <div class="f-field">
            <label>শ্রেণি</label>
            <select wire:model.live="classId">
                <option value="">নির্বাচন করুন</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->full_label }}</option>
                @endforeach
            </select>
        </div>
        <div class="f-field">
            <label>শাখা</label>
            <select wire:model.live="sectionId" @if(!$classId) disabled @endif>
                <option value="">সব শাখা</option>
                @foreach ($sections as $section)
                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="f-field">
            <label>শুরুর তারিখ</label>
            <input type="date" wire:model.live="from">
        </div>
        <div class="f-field">
            <label>শেষ তারিখ</label>
            <input type="date" wire:model.live="to">
        </div>
    </div>

    @if ($classId)
        <div class="stat-strip">
            <div class="stat-chip" style="--accent:var(--good)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="m8.5 12 2.3 2.3L16 9.7"/></svg></div><div><div class="sv">{{ $classAvg }}%</div><div class="sl">গড় উপস্থিতি</div></div></div>
            <div class="stat-chip" style="--accent:var(--teacher)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="9" cy="8" r="3"/><path d="M2.5 20c.9-3.4 3.6-5 6.5-5s5.6 1.6 6.5 5"/></svg></div><div><div class="sv">{{ $students->count() }}</div><div class="sl">মোট শিক্ষার্থী</div></div></div>
        </div>

        <div class="table-card">
            <table>
                <thead><tr><th>শিক্ষার্থী</th><th>উপস্থিত</th><th>অনুপস্থিত</th><th>ছুটি</th><th>মোট মার্ক করা দিন</th><th>উপস্থিতির হার</th></tr></thead>
                <tbody>
                    @forelse ($students as $row)
                        <tr>
                            <td>
                                <div class="stud">
                                    <div class="ini">{{ mb_substr($row['student']->name, 0, 1) }}</div>
                                    <div><div class="name">{{ $row['student']->name }}</div><div class="id">{{ $row['student']->student_id_no }}</div></div>
                                </div>
                            </td>
                            <td>{{ $row['present'] }}</td>
                            <td>{{ $row['absent'] }}</td>
                            <td>{{ $row['leave'] }}</td>
                            <td>{{ $row['total'] }}</td>
                            <td>
                                <span class="pill {{ $row['pct'] >= 90 ? 'active' : ($row['pct'] >= 75 ? 'day' : 'due') }}">{{ $row['pct'] }}%</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো শিক্ষার্থী পাওয়া যায়নি</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div class="table-card">
            <div style="text-align:center;color:var(--ink-soft);padding:40px 20px;">রিপোর্ট দেখতে একটা শ্রেণি নির্বাচন করুন</div>
        </div>
    @endif
</div>
