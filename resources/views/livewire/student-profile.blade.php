<div class="profile-page" x-data="{ tab: 'overview' }">
    <div class="page-top">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">শিক্ষার্থী / শিক্ষার্থী তালিকা / প্রোফাইল</div>
            <h2>শিক্ষার্থী প্রোফাইল</h2>
            <p>সম্পূর্ণ একাডেমিক, আর্থিক ও ব্যক্তিগত তথ্য এক জায়গায়</p>
        </div>
        <div class="head-actions">
            @if (auth()->user()->role === 'guardian')
                <a href="{{ route('portal.guardian') }}" class="btn-ghost">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><path d="M11 5 4 12l7 7M4 12h16"/></svg>
                    পোর্টালে ফিরুন
                </a>
            @else
                <a href="{{ route('students.index') }}" class="btn-ghost">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15"><path d="M11 5 4 12l7 7M4 12h16"/></svg>
                    তালিকায় ফিরুন
                </a>
                <a href="{{ route('stub', urlencode('প্রোফাইল সম্পাদনা')) }}" class="btn-ghost">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                    প্রোফাইল সম্পাদনা
                </a>
            @endif
        </div>
    </div>

    <div class="profile-grid">
        {{-- SHORT DETAILS CARD --}}
        <aside class="short-card">
            <div class="short-banner" style="background:linear-gradient(120deg,#6E2136,var(--cover-maroon-deep));"></div>
            <div class="short-photo-wrap">
                <div class="short-photo" style="background:linear-gradient(135deg,var(--gold-light),var(--gold));color:var(--cover-maroon-deep);">
                    @if ($student->photo_path)
                        <img src="{{ asset('storage/'.$student->photo_path) }}" alt="{{ $student->name }}">
                    @else
                        {{ mb_substr($student->name, 0, 1) }}
                    @endif
                </div>
            </div>
            <div class="short-body">
                <div class="nm">{{ $student->name }}</div>
                @if ($student->name_en)<div class="nm-en">{{ $student->name_en }}</div>@endif
                <div class="short-pills">
                    <span class="pill {{ $student->status === 'active' ? 'active' : 'inactive' }}">{{ $student->status === 'active' ? 'অধ্যয়নরত' : 'নিষ্ক্রিয়' }}</span>
                    @if ($student->admission_type)<span class="pill gold">{{ $student->admission_type }}</span>@endif
                </div>

                @if ($student->student_id_no)
                    <div class="short-id"><span>স্টুডেন্ট আইডি: <b>{{ $student->student_id_no }}</b></span></div>
                @endif

                <div class="short-list">
                    <div class="row"><span class="k"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5.5A2 2 0 0 1 6 4h13v14H6a2 2 0 0 0-2 2V5.5Z"/></svg>শ্রেণি/শাখা</span><span class="v">{{ $student->schoolClass?->full_label ?? '—' }}@if($student->section), {{ $student->section->name }}@endif</span></div>
                    <div class="row"><span class="k"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/></svg>জন্ম তারিখ</span><span class="v">{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d M, Y') : '—' }}</span></div>
                    <div class="row"><span class="k"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/></svg>লিঙ্গ</span><span class="v">{{ $student->gender ?? '—' }}</span></div>
                    <div class="row"><span class="k"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3s6 6.5 6 11a6 6 0 1 1-12 0c0-4.5 6-11 6-11Z"/></svg>রক্তের গ্রুপ</span><span class="v">{{ $student->blood_group ?? '—' }}</span></div>
                    <div class="row"><span class="k"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3.5" y="7" width="17" height="12" rx="2.5"/></svg>ভর্তির তারিখ</span><span class="v">{{ $student->created_at?->format('d M, Y') ?? '—' }}</span></div>
                </div>

                <div class="short-attendance">
                    <div class="ring" style="background:conic-gradient(var(--good) 0% {{ $attendancePct }}%, #E3D8BE {{ $attendancePct }}% 100%);"><div class="ring-inner">{{ $attendancePct }}%</div></div>
                    <div><div class="t1">বার্ষিক হাজিরা</div><div class="t2">{{ now()->format('Y') }}</div></div>
                </div>

                @if ($student->guardian_phone)
                    <div class="short-contact">
                        <div class="lbl">অভিভাবকের যোগাযোগ</div>
                        <div class="nm">{{ $guardians->first()->name ?? '—' }}</div>
                        <div class="contact-row">
                            <a class="mini-btn" href="tel:{{ $student->guardian_phone }}" title="কল করুন"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2Z"/></svg></a>
                        </div>
                    </div>
                @endif
            </div>
        </aside>

        {{-- TABS --}}
        <div class="tabs-col">
            <div class="tab-bar" style="--accent:var(--cover-maroon);">
                <button type="button" class="tab-btn" :class="{active: tab==='overview'}" @click="tab='overview'">ওভারভিউ</button>
                <button type="button" class="tab-btn" :class="{active: tab==='academic'}" @click="tab='academic'">একাডেমিক</button>
                <button type="button" class="tab-btn" :class="{active: tab==='attendance'}" @click="tab='attendance'">হাজিরা</button>
                <button type="button" class="tab-btn" :class="{active: tab==='fees'}" @click="tab='fees'">ফি ও পেমেন্ট</button>
                <button type="button" class="tab-btn" :class="{active: tab==='guardian'}" @click="tab='guardian'">অভিভাবক তথ্য</button>
                <button type="button" class="tab-btn" :class="{active: tab==='documents'}" @click="tab='documents'">ডকুমেন্টস</button>
                <button type="button" class="tab-btn" :class="{active: tab==='portal'}" @click="tab='portal'">পোর্টাল অ্যাক্সেস</button>
            </div>

            {{-- OVERVIEW --}}
            <div x-show="tab==='overview'">
                <div class="stat-strip">
                    <div class="stat-chip" style="--accent:var(--good)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="m8.5 12 2.3 2.3L16 9.7"/></svg></div><div><div class="sv">{{ $attendancePct }}%</div><div class="sl">বার্ষিক হাজিরা</div></div></div>
                    <div class="stat-chip" style="--accent:var(--teacher)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/></svg></div><div><div class="sv">{{ $studyingYears }} বছর</div><div class="sl">অধ্যয়নরত</div></div></div>
                    <div class="stat-chip" style="--accent:var(--bad)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3.5" y="7" width="17" height="12" rx="2.5"/></svg></div><div><div class="sv">৳{{ number_format($totalDue) }}</div><div class="sl">ফি বকেয়া</div></div></div>
                    <div class="stat-chip" style="--accent:var(--gold)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="9" cy="8" r="3"/><path d="M2.5 20c.9-3.4 3.6-5 6.5-5s5.6 1.6 6.5 5"/></svg></div><div><div class="sv">{{ $guardians->count() }} জন</div><div class="sl">অভিভাবক</div></div></div>
                </div>

                <div class="card">
                    <h3>ব্যক্তিগত তথ্য</h3>
                    <div class="info-grid">
                        <div class="info-item"><div class="k">নাম (ইংরেজি)</div><div class="v">{{ $student->name_en ?? '—' }}</div></div>
                        <div class="info-item"><div class="k">লিঙ্গ</div><div class="v">{{ $student->gender ?? '—' }}</div></div>
                        <div class="info-item"><div class="k">জন্ম নিবন্ধন নম্বর</div><div class="v">{{ $student->birth_reg_no ?? '—' }}</div></div>
                        <div class="info-item"><div class="k">রক্তের গ্রুপ</div><div class="v">{{ $student->blood_group ?? '—' }}</div></div>
                        <div class="info-item"><div class="k">ধর্ম</div><div class="v">{{ $student->religion ?? '—' }}</div></div>
                        <div class="info-item"><div class="k">জাতীয়তা</div><div class="v">{{ $student->nationality ?? '—' }}</div></div>
                    </div>
                </div>

                <div class="card">
                    <h3>ভর্তি তথ্য</h3>
                    <div class="info-grid">
                        <div class="info-item"><div class="k">শিক্ষার্থী আইডি</div><div class="v">{{ $student->student_id_no ?? '—' }}</div></div>
                        <div class="info-item"><div class="k">ভর্তির ধরন</div><div class="v">{{ $student->admission_type ?? '—' }}</div></div>
                        <div class="info-item"><div class="k">পূর্ববর্তী প্রতিষ্ঠান</div><div class="v">{{ $student->previous_school ?? '—' }}</div></div>
                        <div class="info-item"><div class="k">শ্রেণি/শাখা</div><div class="v">{{ $student->schoolClass?->full_label ?? '—' }}@if($student->section), {{ $student->section->name }}@endif</div></div>
                    </div>
                </div>
            </div>

            {{-- ACADEMIC --}}
            <div x-show="tab==='academic'">
                <div class="card">
                    <h3>শ্রেণির বিষয় ও শিক্ষক</h3>
                    <p class="sub">ক্লাস রুটিন অনুযায়ী নির্ধারিত বিষয় থেকে তৈরি</p>
                    @if ($subjectRows->isNotEmpty())
                        <table>
                            <thead><tr><th>বিষয়</th><th>শিক্ষক</th><th>সাপ্তাহিক পিরিয়ড</th></tr></thead>
                            <tbody>
                                @foreach ($subjectRows as $row)
                                    <tr><td>{{ $row['subject'] }}</td><td>{{ $row['teacher'] }}</td><td>{{ $row['periods'] }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-note">
                            <div class="en-title">এখনো কোনো ক্লাস রুটিন যুক্ত করা হয়নি</div>
                            <div class="en-sub">ক্লাস রুটিন থেকে এই শ্রেণিতে বিষয়/শিক্ষক নির্ধারণ করলে এখানে দেখা যাবে।</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ATTENDANCE --}}
            <div x-show="tab==='attendance'">
                <div class="stat-strip">
                    <div class="stat-chip" style="--accent:var(--good)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="m8.5 12 2.3 2.3L16 9.7"/></svg></div><div><div class="sv">{{ $presentDaysYear }}</div><div class="sl">উপস্থিত দিন</div></div></div>
                    <div class="stat-chip" style="--accent:var(--bad)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg></div><div><div class="sv">{{ $absentDaysYear }}</div><div class="sl">অনুপস্থিত দিন</div></div></div>
                    <div class="stat-chip" style="--accent:var(--gold)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="18" rx="2"/></svg></div><div><div class="sv">{{ $leaveDaysYear }}</div><div class="sl">অনুমোদিত ছুটি</div></div></div>
                    <div class="stat-chip" style="--accent:var(--teacher)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 19V10l8-6 8 6v9"/></svg></div><div><div class="sv">{{ $attendancePct }}%</div><div class="sl">সামগ্রিক হাজিরা</div></div></div>
                </div>
                <div class="card">
                    <h3>সাম্প্রতিক হাজিরা</h3>
                    @if ($recentAttendance->isNotEmpty())
                        <table>
                            <thead><tr><th>তারিখ</th><th>স্ট্যাটাস</th><th>মন্তব্য</th></tr></thead>
                            <tbody>
                                @foreach ($recentAttendance as $att)
                                    <tr>
                                        <td>{{ $att->date->format('d M, Y') }}</td>
                                        <td><span class="tag {{ $att->status === 'present' ? 'good' : ($att->status === 'leave' ? 'gold' : 'bad') }}">{{ match($att->status) { 'present' => 'উপস্থিত', 'late' => 'বিলম্বে', 'leave' => 'ছুটি', default => 'অনুপস্থিত' } }}</span></td>
                                        <td>{{ $att->remarks ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-note"><div class="en-sub">এখনো কোনো হাজিরা রেকর্ড নেই।</div></div>
                    @endif
                </div>
            </div>

            {{-- FEES --}}
            <div x-show="tab==='fees'">
                <div class="stat-strip">
                    <div class="stat-chip" style="--accent:var(--good)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3.5" y="7" width="17" height="12" rx="2.5"/></svg></div><div><div class="sv">৳{{ number_format($totalPaidThisYear) }}</div><div class="sl">মোট পরিশোধিত ({{ now()->format('Y') }})</div></div></div>
                    <div class="stat-chip" style="--accent:var(--bad)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 9v4"/></svg></div><div><div class="sv">৳{{ number_format($totalDue) }}</div><div class="sl">মোট বকেয়া</div></div></div>
                    @if ($monthlyFee)
                        <div class="stat-chip" style="--accent:var(--teacher)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="6" width="16" height="13" rx="2"/></svg></div><div><div class="sv">৳{{ number_format($monthlyFee->amount_due) }}</div><div class="sl">মাসিক বেতন</div></div></div>
                    @endif
                </div>
                <div class="card">
                    <h3>ফি ও পেমেন্ট হিস্ট্রি</h3>
                    @if ($fees->isNotEmpty())
                        <table>
                            <thead><tr><th>মাস/ধরন</th><th>বকেয়া</th><th>পরিশোধিত</th><th>স্ট্যাটাস</th><th></th></tr></thead>
                            <tbody>
                                @foreach ($fees as $fee)
                                    <tr>
                                        <td>{{ $fee->due_month }} — {{ $fee->fee_type }}</td>
                                        <td>৳{{ number_format($fee->due_amount, 0) }}</td>
                                        <td>৳{{ number_format($fee->amount_paid, 0) }}</td>
                                        <td><span class="tag {{ $fee->status === 'paid' ? 'good' : ($fee->status === 'partial' ? 'gold' : 'bad') }}">{{ match($fee->status) { 'paid' => 'পরিশোধিত', 'partial' => 'আংশিক', 'overdue' => 'ওভারডিউ', default => 'বকেয়া' } }}</span></td>
                                        <td>@if ($fee->status === 'paid')<a href="{{ route('fee-collections.receipt', $fee) }}" target="_blank">রশিদ</a>@endif</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-note"><div class="en-sub">এখনো কোনো ফি রেকর্ড নেই।</div></div>
                    @endif
                </div>
            </div>

            {{-- GUARDIAN --}}
            <div x-show="tab==='guardian'">
                <div class="card">
                    <h3>অভিভাবক তথ্য</h3>
                    @if ($guardians->isNotEmpty())
                        @foreach ($guardians as $guardian)
                            <div class="info-grid" style="margin-bottom:16px;">
                                <div class="info-item"><div class="k">নাম</div><div class="v">{{ $guardian->name }}</div></div>
                                <div class="info-item"><div class="k">সম্পর্ক</div><div class="v">{{ $guardian->pivot->relationship ?? '—' }}</div></div>
                                <div class="info-item"><div class="k">ইমেইল</div><div class="v">{{ $guardian->email ?? '—' }}</div></div>
                                <div class="info-item"><div class="k">মোবাইল</div><div class="v">{{ $student->guardian_phone ?? '—' }}</div></div>
                            </div>
                        @endforeach
                    @else
                        <div class="empty-note">
                            <div class="en-title">কোনো অভিভাবক অ্যাকাউন্ট এখনো যুক্ত করা হয়নি</div>
                            <div class="en-sub">অভিভাবক পোর্টাল অ্যাকাউন্ট তৈরি হলে এখানে দেখা যাবে।</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- DOCUMENTS --}}
            <div x-show="tab==='documents'">
                <div class="card">
                    <div class="card-row"><div><h3>আপলোডকৃত ডকুমেন্টস</h3><p class="sub">জন্ম নিবন্ধন, ট্রান্সফার সার্টিফিকেট ইত্যাদি</p></div></div>
                    <div class="empty-note">
                        <div class="en-title">ডকুমেন্ট আপলোড শীঘ্রই আসছে</div>
                        <div class="en-sub">জন্ম নিবন্ধন সনদ, ট্রান্সফার সার্টিফিকেট ও অন্যান্য কাগজপত্র আপলোড এবং সংরক্ষণের ফিচার শীঘ্রই যুক্ত হবে।</div>
                    </div>
                </div>
            </div>

            {{-- PORTAL --}}
            <div x-show="tab==='portal'">
                <div class="cred-card">
                    <div class="cred-head">
                        <div class="cred-ic" style="background:color-mix(in srgb, var(--cover-maroon) 15%, white); color:var(--cover-maroon);"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="6" width="16" height="13" rx="2"/><path d="M9 6V5a3 3 0 0 1 6 0v1"/></svg></div>
                        <div><div class="t1">শিক্ষার্থী পোর্টাল লগইন</div><div class="t2">ফলাফল, রুটিন ও নোটিশ দেখার জন্য</div></div>
                    </div>
                    @if ($portalUser)
                        <div class="info-grid">
                            <div class="info-item"><div class="k">লগইন ইমেইল</div><div class="v">{{ $portalUser->email }}</div></div>
                            <div class="info-item"><div class="k">অ্যাকাউন্ট তৈরি</div><div class="v">{{ $portalUser->created_at->format('d M, Y') }}</div></div>
                        </div>
                    @else
                        <div class="empty-note"><div class="en-sub">এই শিক্ষার্থীর জন্য এখনো কোনো পোর্টাল লগইন অ্যাকাউন্ট তৈরি করা হয়নি।</div></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
