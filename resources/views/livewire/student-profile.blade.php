<div>
    <div class="page-head" style="margin-bottom:14px;">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">শিক্ষার্থী / প্রোফাইল</div>
            <h2 style="margin:0;">শিক্ষার্থী প্রোফাইল</h2>
        </div>
        <a href="{{ route('students.index') }}" class="btn-ghost">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><path d="M11 5 4 12l7 7M4 12h16"/></svg>
            তালিকায় ফিরুন
        </a>
    </div>

    {{-- PROFILE HEADER --}}
    <div class="profile-header">
        <div class="ph-photo">
            @if ($student->photo_path)
                <img src="{{ asset('storage/'.$student->photo_path) }}" alt="{{ $student->name }}">
            @else
                {{ mb_substr($student->name, 0, 1) }}
            @endif
        </div>
        <div class="ph-info">
            <h2>{{ $student->name }}</h2>
            <div class="ph-meta">
                <span class="status {{ $student->status === 'active' ? '' : 'inactive' }}">
                    ● {{ $student->status === 'active' ? 'অধ্যয়নরত' : 'নিষ্ক্রিয়' }}
                </span>
                @if ($student->student_id_no)
                    <span>{{ $student->student_id_no }}</span>
                @endif
                <span>{{ $student->schoolClass?->full_label ?? '—' }}@if($student->section), {{ $student->section->name }}@endif</span>
                @if ($student->guardian_phone)
                    <span>অভিভাবক মোবাইল: {{ $student->guardian_phone }}</span>
                @endif
            </div>
        </div>
        <div class="ph-actions">
            @if ($student->guardian_phone)
                <a class="ph-icon-btn" href="tel:{{ $student->guardian_phone }}" title="কল করুন">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L14 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 2 6a2 2 0 0 1 2-2Z"/></svg>
                </a>
            @endif
            <a class="ph-edit" href="{{ route('stub', urlencode('প্রোফাইল সম্পাদনা')) }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                প্রোফাইল সম্পাদনা
            </a>
        </div>
    </div>

    {{-- KPI STRIP --}}
    <div class="kpi-grid compact">
        <div class="kpi-card" style="--accent:var(--student)">
            <div class="kpi-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="m8.5 12 2.3 2.3L16 9.7"/></svg></div>
            <div class="kpi-label">উপস্থিতির হার (এ মাসে)</div>
            <div class="kpi-value">{{ $attendancePct }}%</div>
            <div class="kpi-sub">{{ $presentDays }}/{{ $totalMarkedDays }} দিন উপস্থিত</div>
        </div>
        <div class="kpi-card" style="--accent:var(--bad)">
            <div class="kpi-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3.5" y="7" width="17" height="12" rx="2.5"/><path d="M3.5 11h17"/></svg></div>
            <div class="kpi-label">ফি বকেয়া</div>
            <div class="kpi-value">৳{{ number_format($totalDue, 0) }}</div>
            <div class="kpi-sub">মোট পরিশোধিত ৳{{ number_format($totalPaid, 0) }}</div>
        </div>
        <div class="kpi-card" style="--accent:var(--guardian)">
            <div class="kpi-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="9" cy="8" r="3"/><path d="M2.5 20c.9-3.4 3.6-5 6.5-5s5.6 1.6 6.5 5"/></svg></div>
            <div class="kpi-label">অভিভাবক</div>
            <div class="kpi-value" style="font-size:15px;">{{ $guardians->count() }} জন</div>
            <div class="kpi-sub">{{ $guardians->first()->name ?? '—' }}</div>
        </div>
        <div class="kpi-card" style="--accent:var(--gold)">
            <div class="kpi-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5.5A2 2 0 0 1 6 4h13v14H6a2 2 0 0 0-2 2V5.5Z"/></svg></div>
            <div class="kpi-label">শ্রেণি/শাখা</div>
            <div class="kpi-value" style="font-size:15px;">{{ $student->schoolClass?->full_label ?? '—' }}</div>
            <div class="kpi-sub">{{ $student->section->name ?? '' }}</div>
        </div>
    </div>

    {{-- TABS --}}
    <div class="tabs-bar">
        <button type="button" class="tab-btn {{ $activeTab === 'overview' ? 'active' : '' }}" wire:click="setTab('overview')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 12 12 4l8 8"/><path d="M6 10v9h12v-9"/></svg>ওভারভিউ
        </button>
        <button type="button" class="tab-btn {{ $activeTab === 'academic' ? 'active' : '' }}" wire:click="setTab('academic')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5.5A2 2 0 0 1 6 4h13v14H6a2 2 0 0 0-2 2V5.5Z"/></svg>একাডেমিক
        </button>
        <button type="button" class="tab-btn {{ $activeTab === 'fees' ? 'active' : '' }}" wire:click="setTab('fees')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3.5" y="7" width="17" height="12" rx="2.5"/><path d="M3.5 11h17"/></svg>ফি ও পেমেন্ট
        </button>
        <button type="button" class="tab-btn {{ $activeTab === 'attendance' ? 'active' : '' }}" wire:click="setTab('attendance')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="m8.5 12 2.3 2.3L16 9.7"/></svg>হাজিরা
        </button>
        <button type="button" class="tab-btn {{ $activeTab === 'guardian' ? 'active' : '' }}" wire:click="setTab('guardian')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3"/><path d="M2.5 20c.9-3.4 3.6-5 6.5-5s5.6 1.6 6.5 5"/></svg>অভিভাবক তথ্য
        </button>
        <button type="button" class="tab-btn {{ $activeTab === 'docs' ? 'active' : '' }}" wire:click="setTab('docs')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 3v5h5"/><path d="M6 3h8l5 5v13H6z"/></svg>ডকুমেন্টস
        </button>
    </div>

    {{-- TAB: OVERVIEW --}}
    @if ($activeTab === 'overview')
        <div class="grid2col">
            <div class="card">
                <div class="card-head"><div><h3>ব্যক্তিগত তথ্য</h3></div></div>
                <table class="info-table">
                    <tr><td>নাম (ইংরেজি)</td><td>{{ $student->name_en ?? '—' }}</td></tr>
                    <tr><td>লিঙ্গ</td><td>{{ $student->gender ?? '—' }}</td></tr>
                    <tr><td>জন্ম তারিখ</td><td>{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d F, Y') : '—' }}</td></tr>
                    <tr><td>জন্ম নিবন্ধন নম্বর</td><td>{{ $student->birth_reg_no ?? '—' }}</td></tr>
                    <tr><td>রক্তের গ্রুপ</td><td>{{ $student->blood_group ?? '—' }}</td></tr>
                    <tr><td>ধর্ম</td><td>{{ $student->religion ?? '—' }}</td></tr>
                    <tr><td>জাতীয়তা</td><td>{{ $student->nationality ?? '—' }}</td></tr>
                </table>
            </div>
            <div class="card">
                <div class="card-head"><div><h3>ভর্তি তথ্য</h3></div></div>
                <table class="info-table">
                    <tr><td>শিক্ষার্থী আইডি</td><td>{{ $student->student_id_no ?? '—' }}</td></tr>
                    <tr><td>ভর্তির ধরন</td><td>{{ $student->admission_type ?? '—' }}</td></tr>
                    <tr><td>পূর্ববর্তী প্রতিষ্ঠান</td><td>{{ $student->previous_school ?? '—' }}</td></tr>
                    <tr><td>শ্রেণি</td><td>{{ $student->schoolClass?->full_label ?? '—' }}</td></tr>
                    <tr><td>শাখা</td><td>{{ $student->section->name ?? '—' }}</td></tr>
                    <tr><td>অভিভাবকের মোবাইল</td><td>{{ $student->guardian_phone ?? '—' }}</td></tr>
                </table>
            </div>
        </div>
    @endif

    {{-- TAB: ACADEMIC --}}
    @if ($activeTab === 'academic')
        <div class="card">
            <div class="card-head">
                <div><h3>শ্রেণির বিষয় ও শিক্ষক</h3><p>ক্লাস রুটিন অনুযায়ী নির্ধারিত বিষয় থেকে তৈরি</p></div>
            </div>
            @if ($subjectRows->isNotEmpty())
                <table class="info-table">
                    <tr><td style="font-weight:600;">বিষয়</td><td style="font-weight:600;">শিক্ষক</td><td style="font-weight:600;">সাপ্তাহিক পিরিয়ড</td></tr>
                    @foreach ($subjectRows as $row)
                        <tr><td>{{ $row['subject'] }}</td><td>{{ $row['teacher'] }}</td><td>{{ $row['periods'] }}</td></tr>
                    @endforeach
                </table>
            @else
                <div class="empty-note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5.5A2 2 0 0 1 6 4h13v14H6a2 2 0 0 0-2 2V5.5Z"/></svg>
                    <div class="en-title">এখনো কোনো ক্লাস রুটিন যুক্ত করা হয়নি</div>
                    <div class="en-sub">ক্লাস রুটিন থেকে এই শ্রেণিতে বিষয়/শিক্ষক নির্ধারণ করলে এখানে দেখা যাবে।</div>
                </div>
            @endif
        </div>
    @endif

    {{-- TAB: FEES --}}
    @if ($activeTab === 'fees')
        <div class="card">
            <div class="card-head"><div><h3>ফি ও পেমেন্ট হিস্ট্রি</h3></div></div>
            @if ($fees->isNotEmpty())
                <table class="info-table">
                    <tr>
                        <td style="font-weight:600;">মাস/ধরন</td>
                        <td style="font-weight:600;">বকেয়া</td>
                        <td style="font-weight:600;">পরিশোধিত</td>
                        <td style="font-weight:600;">স্ট্যাটাস</td>
                        <td style="font-weight:600;"></td>
                    </tr>
                    @foreach ($fees as $fee)
                        <tr>
                            <td>{{ $fee->due_month }} — {{ $fee->fee_type }}</td>
                            <td>৳{{ number_format($fee->due_amount, 0) }}</td>
                            <td>৳{{ number_format($fee->amount_paid, 0) }}</td>
                            <td>
                                <span class="pill {{ $fee->status === 'paid' ? 'active' : ($fee->status === 'partial' ? 'day' : 'due') }}">
                                    {{ match($fee->status) { 'paid' => 'পরিশোধিত', 'partial' => 'আংশিক', 'overdue' => 'ওভারডিউ', default => 'বকেয়া' } }}
                                </span>
                            </td>
                            <td>
                                @if ($fee->status === 'paid')
                                    <a href="{{ route('fee-collections.receipt', $fee) }}" target="_blank">রশিদ</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </table>
            @else
                <div class="empty-note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3.5" y="7" width="17" height="12" rx="2.5"/></svg>
                    <div class="en-title">এখনো কোনো ফি রেকর্ড নেই</div>
                </div>
            @endif
        </div>
    @endif

    {{-- TAB: ATTENDANCE --}}
    @if ($activeTab === 'attendance')
        <div class="card">
            <div class="card-head"><div><h3>সাম্প্রতিক হাজিরা</h3><p>এ মাসে {{ $presentDays }}/{{ $totalMarkedDays }} দিন উপস্থিত ({{ $attendancePct }}%)</p></div></div>
            @if ($recentAttendance->isNotEmpty())
                <table class="info-table">
                    <tr><td style="font-weight:600;">তারিখ</td><td style="font-weight:600;">স্ট্যাটাস</td><td style="font-weight:600;">মন্তব্য</td></tr>
                    @foreach ($recentAttendance as $att)
                        <tr>
                            <td>{{ $att->date->format('d M, Y') }}</td>
                            <td>
                                <span class="pill {{ $att->status === 'present' ? 'active' : ($att->status === 'leave' ? 'day' : 'due') }}">
                                    {{ match($att->status) { 'present' => 'উপস্থিত', 'late' => 'বিলম্বে', 'leave' => 'ছুটি', default => 'অনুপস্থিত' } }}
                                </span>
                            </td>
                            <td>{{ $att->remarks ?? '—' }}</td>
                        </tr>
                    @endforeach
                </table>
            @else
                <div class="empty-note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="4" width="16" height="16" rx="3"/></svg>
                    <div class="en-title">এখনো কোনো হাজিরা রেকর্ড নেই</div>
                </div>
            @endif
        </div>
    @endif

    {{-- TAB: GUARDIAN --}}
    @if ($activeTab === 'guardian')
        <div class="card">
            <div class="card-head"><div><h3>অভিভাবক তথ্য</h3></div></div>
            @if ($guardians->isNotEmpty())
                @foreach ($guardians as $guardian)
                    <table class="info-table" style="margin-bottom:14px;">
                        <tr><td style="font-weight:600;">নাম</td><td>{{ $guardian->name }}</td></tr>
                        <tr><td>সম্পর্ক</td><td>{{ $guardian->pivot->relationship ?? '—' }}</td></tr>
                        <tr><td>ইমেইল</td><td>{{ $guardian->email ?? '—' }}</td></tr>
                        <tr><td>মোবাইল</td><td>{{ $student->guardian_phone ?? '—' }}</td></tr>
                    </table>
                @endforeach
            @else
                <div class="empty-note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="9" cy="8" r="3"/></svg>
                    <div class="en-title">কোনো অভিভাবক অ্যাকাউন্ট এখনো যুক্ত করা হয়নি</div>
                    <div class="en-sub">অভিভাবক পোর্টাল অ্যাকাউন্ট তৈরি হলে এখানে দেখা যাবে।</div>
                </div>
            @endif
        </div>
    @endif

    {{-- TAB: DOCUMENTS (pending) --}}
    @if ($activeTab === 'docs')
        <div class="card">
            <div class="empty-note">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M14 3v5h5"/><path d="M6 3h8l5 5v13H6z"/></svg>
                <div class="en-title">ডকুমেন্ট আপলোড শীঘ্রই আসছে</div>
                <div class="en-sub">জন্ম নিবন্ধন সনদ, ট্রান্সফার সার্টিফিকেট ও অন্যান্য কাগজপত্র আপলোড এবং সংরক্ষণের ফিচার শীঘ্রই যুক্ত হবে।</div>
            </div>
        </div>
    @endif
</div>
