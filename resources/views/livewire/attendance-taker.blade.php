<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">হাজিরা / শিক্ষার্থী হাজিরা</div>
            <h2>দৈনিক হাজিরা নিন</h2>
            <p>শ্রেণি ও তারিখ নির্বাচন করে প্রতিটি শিক্ষার্থীর উপস্থিতি চিহ্নিত করুন</p>
        </div>
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
            <label>তারিখ</label>
            <input type="date" wire:model.live="date">
        </div>
    </div>

    @if ($classId)
        @php
            $presentCount = collect($marks)->filter(fn ($s) => in_array($s, ['present', 'late']))->count();
            $absentCount = collect($marks)->filter(fn ($s) => $s === 'absent')->count();
            $leaveCount = collect($marks)->filter(fn ($s) => $s === 'leave')->count();
        @endphp

        <div class="stat-strip">
            <div class="stat-chip" style="--accent:var(--teacher)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="9" cy="8" r="3"/><path d="M2.5 20c.9-3.4 3.6-5 6.5-5s5.6 1.6 6.5 5"/></svg></div><div><div class="sv">{{ $students->count() }}</div><div class="sl">মোট শিক্ষার্থী</div></div></div>
            <div class="stat-chip" style="--accent:var(--good)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="m8.5 12 2.3 2.3L16 9.7"/></svg></div><div><div class="sv">{{ $presentCount }}</div><div class="sl">উপস্থিত</div></div></div>
            <div class="stat-chip" style="--accent:var(--bad)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg></div><div><div class="sv">{{ $absentCount }}</div><div class="sl">অনুপস্থিত</div></div></div>
            <div class="stat-chip" style="--accent:var(--admin)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/></svg></div><div><div class="sv">{{ $leaveCount }}</div><div class="sl">ছুটি</div></div></div>
        </div>

        <div class="legend-row">
            <span><i style="background:var(--good);color:#fff;"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg></i>উপস্থিত</span>
            <span><i style="background:var(--bad);color:#fff;"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg></i>অনুপস্থিত</span>
            <span><i style="background:var(--admin);color:#3E1120;"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="4" y="5" width="16" height="16" rx="2"/></svg></i>ছুটি</span>
            <span><i style="background:var(--teacher);color:#fff;"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></i>দেরিতে</span>
        </div>

        <div class="list-head">
            <h3>শিক্ষার্থী তালিকা</h3>
            <div class="bulk-actions">
                <button type="button" class="bulk-btn" wire:click="markAllPresent">সবাইকে উপস্থিত করুন</button>
            </div>
        </div>

        <div class="roll-card">
            @forelse ($students as $i => $student)
                @php $status = $marks[$student->id] ?? null; @endphp
                <div class="roll-row" wire:key="roll-{{ $student->id }}">
                    <div class="roll-num">{{ $i + 1 }}</div>
                    <div class="roll-ini">{{ mb_substr($student->name, 0, 1) }}</div>
                    <div class="roll-info">
                        <div class="nm">{{ $student->name }}</div>
                        <div class="id">{{ $student->student_id_no }}</div>
                    </div>
                    <div class="status-group">
                        <button type="button" class="status-btn {{ $status === 'present' ? 'active' : '' }}" data-status="present" title="উপস্থিত" wire:click="mark('{{ $student->id }}','present')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>
                        </button>
                        <button type="button" class="status-btn {{ $status === 'absent' ? 'active' : '' }}" data-status="absent" title="অনুপস্থিত" wire:click="mark('{{ $student->id }}','absent')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                        <button type="button" class="status-btn {{ $status === 'leave' ? 'active' : '' }}" data-status="leave" title="ছুটি" wire:click="mark('{{ $student->id }}','leave')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/></svg>
                        </button>
                        <button type="button" class="status-btn {{ $status === 'late' ? 'active' : '' }}" data-status="late" title="দেরিতে" wire:click="mark('{{ $student->id }}','late')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        </button>
                    </div>
                </div>
            @empty
                <div style="text-align:center;color:var(--ink-soft);padding:30px 0;">এই শ্রেণিতে কোনো সক্রিয় শিক্ষার্থী নেই</div>
            @endforelse
        </div>

        <div class="att-save-bar">
            <div class="info"><b>{{ $presentCount }}</b> জন উপস্থিত • <b>{{ $absentCount }}</b> জন অনুপস্থিত • <b>{{ $leaveCount }}</b> জন ছুটিতে — মোট <b>{{ $students->count() }}</b> জনের মধ্যে</div>
            <button type="button" class="btn-primary" wire:click="save">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>
                হাজিরা সংরক্ষণ করুন
            </button>
        </div>

        @if ($saved)
            <div style="position:fixed;bottom:90px;left:50%;transform:translateX(-50%);background:var(--ink);color:#fff;padding:10px 18px;border-radius:10px;font-size:13px;z-index:100;">হাজিরা সংরক্ষিত হয়েছে</div>
        @endif
    @else
        <div class="roll-card">
            <div style="text-align:center;color:var(--ink-soft);padding:40px 20px;">হাজিরা নেওয়া শুরু করতে একটা শ্রেণি নির্বাচন করুন</div>
        </div>
    @endif
</div>
