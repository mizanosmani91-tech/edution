<div>
    <div class="page-head" style="margin-bottom:14px;">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">শিক্ষক ও স্টাফ / প্রোফাইল</div>
            <h2 style="margin:0;">শিক্ষক প্রোফাইল</h2>
        </div>
        <a href="{{ route('teachers.index') }}" class="btn-ghost">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><path d="M11 5 4 12l7 7M4 12h16"/></svg>
            তালিকায় ফিরুন
        </a>
    </div>

    {{-- PROFILE HEADER --}}
    <div class="profile-header">
        <div class="ph-photo">
            @if ($teacher->photo_path)
                <img src="{{ asset('storage/'.$teacher->photo_path) }}" alt="{{ $teacher->name }}">
            @else
                {{ mb_substr($teacher->name, 0, 1) }}
            @endif
        </div>
        <div class="ph-info">
            <h2>{{ $teacher->name }}</h2>
            <div class="ph-meta">
                <span class="status {{ $teacher->status === 'active' ? '' : 'inactive' }}">
                    ● {{ match($teacher->status) { 'active' => 'সক্রিয়', 'leave' => 'ছুটিতে', default => 'নিষ্ক্রিয়' } }}
                </span>
                @if ($teacher->teacher_id_no)
                    <span>{{ $teacher->teacher_id_no }}</span>
                @endif
                @if ($teacher->designation)
                    <span>{{ $teacher->designation }}</span>
                @endif
                @if ($teacher->experience_years)
                    <span>অভিজ্ঞতা: {{ $teacher->experience_years }} বছর</span>
                @endif
            </div>
        </div>
        <div class="ph-actions">
            @if ($teacher->phone)
                <a class="ph-icon-btn" href="tel:{{ $teacher->phone }}" title="কল করুন">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L14 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 2 6a2 2 0 0 1 2-2Z"/></svg>
                </a>
            @endif
            @if ($teacher->email)
                <a class="ph-icon-btn" href="mailto:{{ $teacher->email }}" title="ইমেইল">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 6 8 6 8-6"/></svg>
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
        <div class="kpi-card" style="--accent:var(--teacher)">
            <div class="kpi-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5.5A2 2 0 0 1 6 4h13v14H6a2 2 0 0 0-2 2V5.5Z"/></svg></div>
            <div class="kpi-label">নির্ধারিত ক্লাস</div>
            <div class="kpi-value">{{ $classCount }}</div>
            <div class="kpi-sub">{{ $periodCount }}টি পিরিয়ড / সপ্তাহ</div>
        </div>
        <div class="kpi-card" style="--accent:var(--student)">
            <div class="kpi-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5.5A2 2 0 0 1 6 4h13v14H6a2 2 0 0 0-2 2V5.5Z"/></svg></div>
            <div class="kpi-label">পড়ানো বিষয়</div>
            <div class="kpi-value">{{ $subjectCount }}</div>
            <div class="kpi-sub">মোট বিষয়</div>
        </div>
        <div class="kpi-card" style="--accent:var(--admin)">
            <div class="kpi-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 19V10l8-6 8 6v9"/><path d="M4 19h16M9 19v-6h6v6"/></svg></div>
            <div class="kpi-label">চাকরির ধরন</div>
            <div class="kpi-value" style="font-size:15px;">{{ $teacher->employee_type ?? '—' }}</div>
            <div class="kpi-sub">@if($teacher->joining_date) যোগদান: {{ $teacher->joining_date->format('d M, Y') }} @endif</div>
        </div>
        <div class="kpi-card" style="--accent:var(--gold)">
            <div class="kpi-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3.5" y="7" width="17" height="12" rx="2.5"/><path d="M3.5 11h17"/></svg></div>
            <div class="kpi-label">মোট বেতন (গ্রস)</div>
            <div class="kpi-value">৳{{ number_format($grossSalary) }}</div>
            <div class="kpi-sub">মাসিক</div>
        </div>
    </div>

    {{-- TABS --}}
    <div class="tabs-bar">
        <button type="button" class="tab-btn {{ $activeTab === 'overview' ? 'active' : '' }}" wire:click="setTab('overview')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 12 12 4l8 8"/><path d="M6 10v9h12v-9"/></svg>ওভারভিউ
        </button>
        <button type="button" class="tab-btn {{ $activeTab === 'classes' ? 'active' : '' }}" wire:click="setTab('classes')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="8" cy="8" r="3"/><circle cx="17" cy="9" r="2.4"/><path d="M2.5 20c.9-3.4 3.1-5 5.5-5s4.6 1.6 5.5 5"/></svg>ক্লাস ও বিষয়
        </button>
        <button type="button" class="tab-btn {{ $activeTab === 'salary' ? 'active' : '' }}" wire:click="setTab('salary')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3.5" y="7" width="17" height="12" rx="2.5"/><path d="M3.5 11h17"/></svg>বেতন
        </button>
        <button type="button" class="tab-btn {{ $activeTab === 'attendance' ? 'active' : '' }}" wire:click="setTab('attendance')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="m8.5 12 2.3 2.3L16 9.7"/></svg>উপস্থিতি ও ছুটি
        </button>
        <button type="button" class="tab-btn {{ $activeTab === 'docs' ? 'active' : '' }}" wire:click="setTab('docs')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 3v5h5"/><path d="M6 3h8l5 5v13H6z"/></svg>ডকুমেন্টস ও অন্যান্য
        </button>
    </div>

    {{-- TAB: OVERVIEW --}}
    @if ($activeTab === 'overview')
        <div class="grid2col">
            <div class="card">
                <div class="card-head"><div><h3>ব্যক্তিগত তথ্য</h3></div></div>
                <table class="info-table">
                    <tr><td>নাম (ইংরেজি)</td><td>{{ $teacher->name_en ?? '—' }}</td></tr>
                    <tr><td>লিঙ্গ</td><td>{{ $teacher->gender ?? '—' }}</td></tr>
                    <tr><td>জাতীয় পরিচয়পত্র (NID)</td><td>{{ $teacher->nid ?? '—' }}</td></tr>
                    <tr><td>মোবাইল</td><td>{{ $teacher->phone ?? '—' }}</td></tr>
                    <tr><td>ইমেইল</td><td>{{ $teacher->email ?? '—' }}</td></tr>
                    <tr><td>ঠিকানা</td><td>{{ $teacher->address ?? '—' }}</td></tr>
                    <tr><td>জরুরী যোগাযোগ</td><td>{{ $teacher->emergency_contact ?? '—' }}</td></tr>
                </table>
            </div>
            <div class="card">
                <div class="card-head"><div><h3>শিক্ষাগত ও কর্মজীবন তথ্য</h3></div></div>
                <table class="info-table">
                    <tr><td>শিক্ষাগত যোগ্যতা</td><td>{{ $teacher->education ?? '—' }}</td></tr>
                    <tr><td>পাশের প্রতিষ্ঠান</td><td>{{ $teacher->passing_institution ?? '—' }}</td></tr>
                    <tr><td>পদবি</td><td>{{ $teacher->designation ?? '—' }}</td></tr>
                    <tr><td>কর্মচারীর ধরন</td><td>{{ $teacher->employee_type ?? '—' }}</td></tr>
                    <tr><td>অভিজ্ঞতা</td><td>{{ $teacher->experience_years ? $teacher->experience_years.' বছর' : '—' }}</td></tr>
                    <tr><td>পূর্ববর্তী কর্মস্থল</td><td>{{ $teacher->previous_workplace ?? '—' }}</td></tr>
                    <tr><td>যোগদানের তারিখ</td><td>{{ $teacher->joining_date?->format('d F, Y') ?? '—' }}</td></tr>
                </table>
            </div>
        </div>
    @endif

    {{-- TAB: CLASSES & SUBJECTS --}}
    @if ($activeTab === 'classes')
        <div class="card">
            <div class="card-head">
                <div><h3>নির্ধারিত ক্লাস ও বিষয়</h3><p>ক্লাস রুটিন অনুযায়ী নির্ধারিত পিরিয়ড থেকে তৈরি</p></div>
            </div>
            @if ($classChips->isNotEmpty())
                <div class="class-chip-list">
                    @foreach ($classChips as $chip)
                        <div class="class-chip">
                            <div>
                                <div class="cc-num">{{ $chip['label'] }}</div>
                                <div class="cc-sub">{{ $chip['subjects'] ?: '—' }} — সপ্তাহে {{ $chip['periods'] }} পিরিয়ড</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5.5A2 2 0 0 1 6 4h13v14H6a2 2 0 0 0-2 2V5.5Z"/></svg>
                    <div class="en-title">এখনো কোনো ক্লাস রুটিনে যুক্ত করা হয়নি</div>
                    <div class="en-sub">ক্লাস রুটিন থেকে এই শিক্ষককে বিষয়/ক্লাস নির্ধারণ করলে এখানে দেখা যাবে।</div>
                </div>
            @endif
        </div>
    @endif

    {{-- TAB: SALARY --}}
    @if ($activeTab === 'salary')
        <div class="grid2col">
            <div class="card">
                <div class="card-head"><div><h3>বেতন কাঠামো</h3><p>মাসিক গ্রস বেতন বিভাজন</p></div></div>
                <div class="fee-line"><span>মূল বেতন</span><span>৳{{ number_format($teacher->base_salary ?? 0) }}</span></div>
                <div class="fee-line"><span>বাড়ি ভাড়া ভাতা</span><span>৳{{ number_format($teacher->house_rent ?? 0) }}</span></div>
                <div class="fee-line"><span>চিকিৎসা ভাতা</span><span>৳{{ number_format($teacher->medical_allowance ?? 0) }}</span></div>
                <div class="fee-line total"><span>মোট (গ্রস)</span><span>৳{{ number_format($grossSalary) }}</span></div>
            </div>
            <div class="card">
                <div class="card-head"><div><h3>ব্যাংক ও পেমেন্ট তথ্য</h3></div></div>
                <table class="info-table">
                    <tr><td>ব্যাংকের নাম</td><td>{{ $teacher->bank_name ?? '—' }}</td></tr>
                    <tr><td>শাখা</td><td>{{ $teacher->bank_branch ?? '—' }}</td></tr>
                    <tr><td>হিসাব নম্বর</td><td>{{ $teacher->bank_account ?? '—' }}</td></tr>
                    <tr><td>মোবাইল ব্যাংকিং</td><td>{{ $teacher->mobile_banking ?? '—' }}</td></tr>
                </table>
            </div>
        </div>
        <div class="empty-note" style="padding:16px 0 0;">
            <div class="en-sub">পে-রোল হিস্ট্রি ও মাসিক পরিশোধ ট্র্যাকিং শীঘ্রই যুক্ত হবে (বেতন ও পে-রোল মডিউল)।</div>
        </div>
    @endif

    {{-- TAB: ATTENDANCE & LEAVE --}}
    @if ($activeTab === 'attendance')
        <div class="card" style="margin-bottom:16px;">
            <div class="card-head"><div><h3>সাম্প্রতিক হাজিরা</h3><p>এ মাসে {{ $presentDays }}/{{ $markedDays }} দিন উপস্থিত</p></div></div>
            @if ($recentAttendance->isNotEmpty())
                <table class="info-table">
                    <tr><td style="font-weight:600;">তারিখ</td><td style="font-weight:600;">চেক ইন</td><td style="font-weight:600;">চেক আউট</td><td style="font-weight:600;">স্ট্যাটাস</td></tr>
                    @foreach ($recentAttendance as $att)
                        <tr>
                            <td>{{ $att->date->format('d M, Y') }}</td>
                            <td>{{ $att->check_in?->format('h:i A') ?? '—' }}</td>
                            <td>{{ $att->check_out?->format('h:i A') ?? '—' }}</td>
                            <td>
                                <span class="pill {{ $att->status === 'present' ? 'active' : ($att->status === 'leave' ? 'day' : 'due') }}">
                                    {{ match($att->status) { 'present' => 'উপস্থিত', 'late' => 'দেরিতে', 'leave' => 'ছুটি', default => 'অনুপস্থিত' } }}
                                </span>
                            </td>
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
        <div class="card">
            <div class="card-head"><div><h3>ছুটির হিস্ট্রি</h3></div></div>
            @if ($leaveHistory->isNotEmpty())
                <table class="info-table">
                    <tr><td style="font-weight:600;">তারিখ</td><td style="font-weight:600;">কারণ</td><td style="font-weight:600;">স্ট্যাটাস</td></tr>
                    @foreach ($leaveHistory as $leave)
                        <tr>
                            <td>{{ $leave->date_from->format('d M') }} – {{ $leave->date_to->format('d M, Y') }}</td>
                            <td>{{ $leave->reason }}</td>
                            <td>
                                <span class="pill {{ $leave->status === 'approved' ? 'active' : ($leave->status === 'rejected' ? 'due' : 'day') }}">
                                    {{ match($leave->status) { 'approved' => 'অনুমোদিত', 'rejected' => 'বাতিল', default => 'অপেক্ষমান' } }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </table>
            @else
                <div class="empty-note">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="5" width="16" height="16" rx="2"/></svg>
                    <div class="en-title">এখনো কোনো ছুটির আবেদন নেই</div>
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
                <div class="en-sub">NID, সনদপত্র ও অন্যান্য কাগজপত্র আপলোড এবং সংরক্ষণের ফিচার শীঘ্রই যুক্ত হবে।</div>
            </div>
        </div>
    @endif
</div>
