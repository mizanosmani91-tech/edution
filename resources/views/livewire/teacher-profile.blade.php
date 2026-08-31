<div class="profile-page" x-data="{ tab: 'overview' }">
    <div class="page-top">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">শিক্ষক ও স্টাফ / কর্মী তালিকা / প্রোফাইল</div>
            <h2>শিক্ষক প্রোফাইল</h2>
            <p>কর্মসংস্থান, একাডেমিক দায়িত্ব, বেতন ও ব্যক্তিগত তথ্য এক জায়গায়</p>
        </div>
        <div class="head-actions">
            <a href="{{ route('teachers.index') }}" class="btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><path d="M11 5 4 12l7 7M4 12h16"/></svg>
                তালিকায় ফিরুন
            </a>
            <a href="{{ route('teachers.edit', $teacher) }}" class="btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                প্রোফাইল সম্পাদনা
            </a>
        </div>
    </div>

    <div class="profile-grid">
        {{-- SHORT DETAILS CARD --}}
        <aside class="short-card">
            <div class="short-banner" style="--accent:var(--teacher)"></div>
            <div class="short-photo-wrap">
                <div class="short-photo" style="--accent:var(--teacher)">
                    @if ($teacher->photo_path)
                        <img src="{{ asset('storage/'.$teacher->photo_path) }}" alt="{{ $teacher->name }}">
                    @else
                        {{ mb_substr($teacher->name, 0, 1) }}
                    @endif
                </div>
            </div>
            <div class="short-body">
                <div class="nm">{{ $teacher->name }}</div>
                @if ($teacher->name_en)<div class="nm-en">{{ $teacher->name_en }}</div>@endif
                <div class="short-pills">
                    <span class="pill {{ $teacher->status === 'active' ? 'active' : 'inactive' }}">{{ match($teacher->status) { 'active' => 'সক্রিয়', 'leave' => 'ছুটিতে', default => 'নিষ্ক্রিয়' } }}</span>
                    @if ($teacher->employee_type)<span class="pill blue">{{ $teacher->employee_type }}</span>@endif
                    @if ($teacher->designation)<span class="pill gold">{{ $teacher->designation }}</span>@endif
                </div>

                @if ($teacher->teacher_id_no)
                    <div class="short-id">
                        <span>স্টাফ আইডি: <b>{{ $teacher->teacher_id_no }}</b></span>
                    </div>
                @endif

                <div class="short-list">
                    <div class="row"><span class="k"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="6" width="16" height="13" rx="2"/></svg>পদবি</span><span class="v">{{ $teacher->designation ?? '—' }}</span></div>
                    <div class="row"><span class="k"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/></svg>যোগদানের তারিখ</span><span class="v">{{ $teacher->joining_date?->format('d M, Y') ?? '—' }}</span></div>
                    <div class="row"><span class="k"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/></svg>অভিজ্ঞতা</span><span class="v">{{ $teacher->experience_years ? $teacher->experience_years.' বছর' : '—' }}</span></div>
                    <div class="row"><span class="k"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 19V10l8-6 8 6v9"/></svg>শিক্ষাগত যোগ্যতা</span><span class="v">{{ $teacher->education ?? '—' }}</span></div>
                </div>

                <div class="short-attendance">
                    <div class="ring" style="background:conic-gradient(var(--good) 0% {{ $monthPct }}%, #E3D8BE {{ $monthPct }}% 100%);"><div class="ring-inner">{{ $monthPct }}%</div></div>
                    <div><div class="t1">মাসিক হাজিরা</div><div class="t2">{{ now()->translatedFormat('F, Y') }}</div></div>
                </div>

                @if ($teacher->phone || $teacher->email)
                    <div class="short-contact">
                        <div class="lbl">যোগাযোগ</div>
                        <div class="nm">{{ $teacher->phone ?? $teacher->email }}</div>
                        <div class="contact-row">
                            @if ($teacher->phone)
                                <a class="mini-btn" href="tel:{{ $teacher->phone }}" title="কল করুন"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2Z"/></svg></a>
                            @endif
                            @if ($teacher->email)
                                <a class="mini-btn" href="mailto:{{ $teacher->email }}" title="ইমেইল"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg></a>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="short-actions">
                    <a href="{{ route('leave-requests.index') }}" class="sa-ghost">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/></svg>
                        ছুটির আবেদন দেখুন
                    </a>
                </div>
            </div>
        </aside>

        {{-- TABS --}}
        <div class="tabs-col">
            <div class="tab-bar">
                <button type="button" class="tab-btn" :class="{active: tab==='overview'}" @click="tab='overview'">ওভারভিউ</button>
                <button type="button" class="tab-btn" :class="{active: tab==='classes'}" @click="tab='classes'">ক্লাস দায়িত্ব</button>
                <button type="button" class="tab-btn" :class="{active: tab==='attendance'}" @click="tab='attendance'">হাজিরা</button>
                <button type="button" class="tab-btn" :class="{active: tab==='payroll'}" @click="tab='payroll'">বেতন ও পে-রোল</button>
                <button type="button" class="tab-btn" :class="{active: tab==='leave'}" @click="tab='leave'">ছুটি</button>
                <button type="button" class="tab-btn" :class="{active: tab==='documents'}" @click="tab='documents'">ডকুমেন্টস</button>
                <button type="button" class="tab-btn" :class="{active: tab==='portal'}" @click="tab='portal'">পোর্টাল অ্যাক্সেস</button>
            </div>

            {{-- OVERVIEW --}}
            <div x-show="tab==='overview'">
                <div class="stat-strip">
                    <div class="stat-chip" style="--accent:var(--good)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="m8.5 12 2.3 2.3L16 9.7"/></svg></div><div><div class="sv">{{ $attendancePct }}%</div><div class="sl">বার্ষিক হাজিরা</div></div></div>
                    <div class="stat-chip" style="--accent:var(--teacher)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5.5A2 2 0 0 1 6 4h13v14H6a2 2 0 0 0-2 2V5.5Z"/></svg></div><div><div class="sv">{{ $classCount }}টি</div><div class="sl">ক্লাসের দায়িত্বে</div></div></div>
                    <div class="stat-chip" style="--accent:var(--gold)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/></svg></div><div><div class="sv">{{ $teacher->experience_years ?? 0 }} বছর</div><div class="sl">কর্মরত আছেন</div></div></div>
                    <div class="stat-chip" style="--accent:var(--bad)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="18" rx="2"/></svg></div><div><div class="sv">{{ $remainingLeave }} দিন</div><div class="sl">অবশিষ্ট ছুটি</div></div></div>
                </div>

                <div class="card">
                    <h3>ব্যক্তিগত তথ্য</h3>
                    <div class="info-grid">
                        <div class="info-item"><div class="k">পূর্ণ নাম (ইংরেজি)</div><div class="v">{{ $teacher->name_en ?? '—' }}</div></div>
                        <div class="info-item"><div class="k">লিঙ্গ</div><div class="v">{{ $teacher->gender ?? '—' }}</div></div>
                        <div class="info-item"><div class="k">জাতীয় পরিচয়পত্র (NID)</div><div class="v">{{ $teacher->nid ?? '—' }}</div></div>
                        <div class="info-item"><div class="k">মোবাইল</div><div class="v">{{ $teacher->phone ?? '—' }}</div></div>
                        <div class="info-item"><div class="k">ইমেইল</div><div class="v">{{ $teacher->email ?? '—' }}</div></div>
                        <div class="info-item"><div class="k">বর্তমান ঠিকানা</div><div class="v">{{ $teacher->address ?? '—' }}</div></div>
                        <div class="info-item"><div class="k">জরুরী যোগাযোগ</div><div class="v">{{ $teacher->emergency_contact ?? '—' }}</div></div>
                    </div>
                </div>

                <div class="card">
                    <h3>কর্মসংস্থান সংক্ষিপ্ত তথ্য</h3>
                    <div class="info-grid">
                        <div class="info-item"><div class="k">পদবি</div><div class="v">{{ $teacher->designation ?? '—' }}</div></div>
                        <div class="info-item"><div class="k">কর্মচারীর ধরন</div><div class="v">{{ $teacher->employee_type ?? '—' }}</div></div>
                        <div class="info-item"><div class="k">যোগদানের তারিখ</div><div class="v">{{ $teacher->joining_date?->format('d F, Y') ?? '—' }}</div></div>
                        <div class="info-item"><div class="k">পাশের প্রতিষ্ঠান</div><div class="v">{{ $teacher->passing_institution ?? '—' }}</div></div>
                        <div class="info-item"><div class="k">পূর্ববর্তী কর্মস্থল</div><div class="v">{{ $teacher->previous_workplace ?? '—' }}</div></div>
                        <div class="info-item"><div class="k">শিক্ষাগত যোগ্যতা</div><div class="v">{{ $teacher->education ?? '—' }}</div></div>
                    </div>
                </div>

                <div class="card">
                    <h3>সাম্প্রতিক কার্যক্রম</h3>
                    @if ($activity->isNotEmpty())
                        <div class="timeline">
                            @foreach ($activity as $item)
                                <div class="t-row"><div class="t-dot" style="--accent:var(--teacher)"></div><div class="t-body"><div class="tt">{{ $item['text'] }}</div><div class="td">{{ $item['date']?->format('d F, Y') }}</div></div></div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-note"><div class="en-sub">এখনো কোনো সাম্প্রতিক কার্যক্রম রেকর্ড হয়নি।</div></div>
                    @endif
                </div>
            </div>

            {{-- CLASSES --}}
            <div x-show="tab==='classes'">
                <div class="card">
                    <h3>নির্ধারিত ক্লাস ও বিষয়</h3>
                    @if ($classRows->isNotEmpty())
                        <table>
                            <thead><tr><th>শ্রেণি</th><th>শাখা</th><th>বিষয়</th><th>সাপ্তাহিক ক্লাস</th></tr></thead>
                            <tbody>
                                @foreach ($classRows as $row)
                                    <tr><td>{{ $row['class'] }}</td><td>{{ $row['section'] }}</td><td>{{ $row['subject'] }}</td><td>{{ $row['periods'] }}টি</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-note">
                            <div class="en-title">এখনো কোনো ক্লাস রুটিনে যুক্ত করা হয়নি</div>
                            <div class="en-sub">ক্লাস রুটিন থেকে এই শিক্ষককে বিষয়/ক্লাস নির্ধারণ করলে এখানে দেখা যাবে।</div>
                        </div>
                    @endif
                </div>
                @if ($chipList->isNotEmpty())
                    <div class="card">
                        <h3>সাপ্তাহিক রুটিন সংক্ষিপ্ত</h3>
                        <div class="chip-row">
                            @foreach ($chipList as $chip)
                                <span class="chip">{{ $chip }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- ATTENDANCE --}}
            <div x-show="tab==='attendance'">
                <div class="stat-strip">
                    <div class="stat-chip" style="--accent:var(--good)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="m8.5 12 2.3 2.3L16 9.7"/></svg></div><div><div class="sv">{{ $presentDaysYear }}</div><div class="sl">উপস্থিত দিন</div></div></div>
                    <div class="stat-chip" style="--accent:var(--bad)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg></div><div><div class="sv">{{ $absentDaysYear }}</div><div class="sl">অনুপস্থিত দিন</div></div></div>
                    <div class="stat-chip" style="--accent:var(--gold)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div><div><div class="sv">{{ $lateDaysYear }}</div><div class="sl">দেরিতে উপস্থিত</div></div></div>
                </div>
                <div class="card">
                    <h3>মাসিক হাজিরার প্রবণতা</h3>
                    <div class="chart-box"><canvas id="attTrendChart" data-trend='@json($trend)'></canvas></div>
                </div>
                <div class="card">
                    <h3>সাম্প্রতিক চেক ইন/আউট রেকর্ড</h3>
                    @if ($recentAttendance->isNotEmpty())
                        <table>
                            <thead><tr><th>তারিখ</th><th>চেক ইন</th><th>চেক আউট</th><th>স্ট্যাটাস</th></tr></thead>
                            <tbody>
                                @foreach ($recentAttendance as $att)
                                    <tr>
                                        <td>{{ $att->date->format('d M, Y') }}</td>
                                        <td>{{ $att->check_in?->format('h:i A') ?? '—' }}</td>
                                        <td>{{ $att->check_out?->format('h:i A') ?? '—' }}</td>
                                        <td><span class="tag {{ $att->status === 'present' ? 'good' : ($att->status === 'late' ? 'gold' : 'bad') }}">{{ match($att->status) { 'present' => 'উপস্থিত', 'late' => 'দেরিতে', 'leave' => 'ছুটি', default => 'অনুপস্থিত' } }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-note"><div class="en-sub">এখনো কোনো হাজিরা রেকর্ড নেই।</div></div>
                    @endif
                </div>
            </div>

            {{-- PAYROLL --}}
            <div x-show="tab==='payroll'">
                <div class="card">
                    <h3>বেতন কাঠামো</h3>
                    <div class="salary-box">
                        <div class="line"><span>মূল বেতন</span><span>৳{{ number_format($teacher->base_salary ?? 0) }}</span></div>
                        <div class="line"><span>বাড়ি ভাড়া ভাতা</span><span>৳{{ number_format($teacher->house_rent ?? 0) }}</span></div>
                        <div class="line"><span>চিকিৎসা ভাতা</span><span>৳{{ number_format($teacher->medical_allowance ?? 0) }}</span></div>
                        <div class="line total"><span>মোট (গ্রস)</span><span>৳{{ number_format($grossSalary) }}</span></div>
                    </div>
                </div>
                <div class="card">
                    <h3>ব্যাংক ও পেমেন্ট তথ্য</h3>
                    <div class="info-grid">
                        <div class="info-item"><div class="k">ব্যাংকের নাম</div><div class="v">{{ $teacher->bank_name ?? '—' }}</div></div>
                        <div class="info-item"><div class="k">শাখা</div><div class="v">{{ $teacher->bank_branch ?? '—' }}</div></div>
                        <div class="info-item"><div class="k">হিসাব নম্বর</div><div class="v">{{ $teacher->bank_account ?? '—' }}</div></div>
                        <div class="info-item"><div class="k">মোবাইল ব্যাংকিং</div><div class="v">{{ $teacher->mobile_banking ?? '—' }}</div></div>
                    </div>
                </div>
                <div class="card">
                    <div class="empty-note"><div class="en-sub">পে-রোল হিস্ট্রি ও মাসিক পরিশোধ ট্র্যাকিং শীঘ্রই যুক্ত হবে (বেতন ও পে-রোল মডিউল)।</div></div>
                </div>
            </div>

            {{-- LEAVE --}}
            <div x-show="tab==='leave'">
                <div class="stat-strip">
                    <div class="stat-chip" style="--accent:var(--good)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="18" rx="2"/></svg></div><div><div class="sv">{{ $annualQuota }} দিন</div><div class="sl">বার্ষিক ছুটির কোটা</div></div></div>
                    <div class="stat-chip" style="--accent:var(--gold)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 9v4"/></svg></div><div><div class="sv">{{ $usedLeaveDays }} দিন</div><div class="sl">ব্যবহৃত</div></div></div>
                    <div class="stat-chip" style="--accent:var(--teacher)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/></svg></div><div><div class="sv">{{ $remainingLeave }} দিন</div><div class="sl">অবশিষ্ট</div></div></div>
                </div>
                <div class="card">
                    <h3>ছুটির আবেদন ইতিহাস</h3>
                    @if ($leaveHistory->isNotEmpty())
                        <table>
                            <thead><tr><th>ধরন</th><th>শুরু</th><th>শেষ</th><th>দিন</th><th>কারণ</th><th>স্ট্যাটাস</th></tr></thead>
                            <tbody>
                                @foreach ($leaveHistory as $leave)
                                    <tr>
                                        <td>{{ match($leave->leave_type) { 'sick' => 'অসুস্থতা', 'personal' => 'ব্যক্তিগত', 'maternity_paternity' => 'প্রসূতি/পিতৃত্বকালীন', 'family' => 'পারিবারিক', 'other' => 'অন্যান্য', default => 'নৈমিত্তিক' } }}</td>
                                        <td>{{ $leave->date_from->format('d M') }}</td>
                                        <td>{{ $leave->date_to->format('d M') }}</td>
                                        <td>{{ $leave->total_days }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($leave->reason, 30) }}</td>
                                        <td><span class="tag {{ $leave->status === 'approved' ? 'good' : ($leave->status === 'rejected' ? 'bad' : 'gold') }}">{{ match($leave->status) { 'approved' => 'অনুমোদিত', 'rejected' => 'প্রত্যাখ্যাত', default => 'অপেক্ষমাণ' } }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-note"><div class="en-sub">এখনো কোনো ছুটির আবেদন নেই।</div></div>
                    @endif
                </div>
            </div>

            {{-- DOCUMENTS --}}
            <div x-show="tab==='documents'">
                <div class="card">
                    <div class="card-row">
                        <div><h3>আপলোডকৃত ডকুমেন্টস</h3><p class="sub">চাকরি সংক্রান্ত কাগজপত্র</p></div>
                    </div>
                    <div class="empty-note">
                        <div class="en-title">ডকুমেন্ট আপলোড শীঘ্রই আসছে</div>
                        <div class="en-sub">NID, সনদপত্র ও অন্যান্য কাগজপত্র আপলোড এবং সংরক্ষণের ফিচার শীঘ্রই যুক্ত হবে।</div>
                    </div>
                </div>
            </div>

            {{-- PORTAL --}}
            <div x-show="tab==='portal'">
                <div class="cred-card">
                    <div class="cred-head">
                        <div class="cred-ic" style="background:color-mix(in srgb, var(--teacher) 15%, white); color:var(--teacher);"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="6" width="16" height="13" rx="2"/><path d="M9 6V5a3 3 0 0 1 6 0v1"/></svg></div>
                        <div><div class="t1">স্টাফ পোর্টাল লগইন</div><div class="t2">হাজিরা, রুটিন ও ফলাফল এন্ট্রির জন্য</div></div>
                    </div>
                    @if ($portalUser)
                        <div class="info-grid">
                            <div class="info-item"><div class="k">লগইন ইমেইল</div><div class="v">{{ $portalUser->email }}</div></div>
                            <div class="info-item"><div class="k">অ্যাকাউন্ট তৈরি</div><div class="v">{{ $portalUser->created_at->format('d M, Y') }}</div></div>
                        </div>
                    @else
                        <div class="empty-note"><div class="en-sub">এই শিক্ষকের জন্য এখনো কোনো পোর্টাল লগইন অ্যাকাউন্ট তৈরি করা হয়নি।</div></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:navigated', initAttTrendChart);
        document.addEventListener('DOMContentLoaded', initAttTrendChart);
        function initAttTrendChart() {
            const canvas = document.getElementById('attTrendChart');
            if (!canvas || canvas.dataset.rendered) return;
            const trend = JSON.parse(canvas.dataset.trend || '[]');
            function draw() {
                canvas.dataset.rendered = '1';
                Chart.defaults.font.family = "'Hind Siliguri', sans-serif";
                Chart.defaults.color = '#6B7280';
                new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: trend.map(t => t.label),
                        datasets: [{ data: trend.map(t => t.pct), borderColor: '#3B82F6', backgroundColor: 'rgba(59,130,246,.12)', tension: .4, fill: true, pointRadius: 0, borderWidth: 2.5, spanGaps: true }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { min: 0, max: 100, grid: { color: 'rgba(31,36,50,.06)' }, ticks: { callback: v => v + '%' } }, x: { grid: { display: false } } } }
                });
            }
            if (window.Chart) { draw(); } else {
                const s = document.createElement('script');
                s.src = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.5.1/chart.umd.min.js';
                s.onload = draw;
                document.head.appendChild(s);
            }
        }
    </script>
</div>
