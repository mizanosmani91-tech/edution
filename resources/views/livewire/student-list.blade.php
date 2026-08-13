<div>
    <div class="page-head">
        <div>
            <h2>সকল শিক্ষার্থী</h2>
            <p>মোট {{ number_format($totalStudents) }} জন শিক্ষার্থী নথিভুক্ত আছে — অনুসন্ধান করুন অথবা নতুন ভর্তি করুন</p>
        </div>
        <a href="{{ route('students.admission') }}" class="btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            নতুন শিক্ষার্থী ভর্তি
        </a>
    </div>

    <div class="stat-strip">
        <div class="stat-chip" style="--accent:var(--guardian)">
            <div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="9" cy="8" r="3"/><path d="M2.5 20c.9-3.4 3.6-5 6.5-5s5.6 1.6 6.5 5"/></svg></div>
            <div><div class="sv">{{ number_format($totalStudents) }}</div><div class="sl">মোট শিক্ষার্থী</div></div>
        </div>
        <div class="stat-chip" style="--accent:var(--good)">
            <div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20 6 9 17l-5-5"/></svg></div>
            <div><div class="sv">{{ number_format($activeStudents) }}</div><div class="sl">সক্রিয়</div></div>
        </div>
        <div class="stat-chip" style="--accent:var(--ink-soft)">
            <div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M9 9l6 6M15 9l-6 6"/></svg></div>
            <div><div class="sv">{{ number_format($inactiveStudents) }}</div><div class="sl">নিষ্ক্রিয়</div></div>
        </div>
        <div class="stat-chip" style="--accent:var(--bad)">
            <div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 9v4"/><circle cx="12" cy="16" r=".4" fill="currentColor"/><path d="M10.3 4.5 2.6 18a1.8 1.8 0 0 0 1.6 2.7h15.6a1.8 1.8 0 0 0 1.6-2.7L13.7 4.5a1.8 1.8 0 0 0-3.4 0Z"/></svg></div>
            <div><div class="sv">{{ number_format($dueStudents) }}</div><div class="sl">ফি বকেয়া</div></div>
        </div>
    </div>

    <div class="filter-card">
        <div class="f-field">
            <label>শ্রেণি</label>
            <select wire:model.live="classFilter">
                <option value="">সকল শ্রেণি</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->full_label }}</option>
                @endforeach
            </select>
        </div>
        <div class="f-field">
            <label>শাখা</label>
            <select wire:model.live="sectionFilter" @if(!$classFilter) disabled @endif>
                <option value="">সকল শাখা</option>
                @foreach ($sections as $section)
                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="f-field">
            <label>স্ট্যাটাস</label>
            <select wire:model.live="statusFilter">
                <option value="">সকল</option>
                <option value="active">সক্রিয়</option>
                <option value="due">ফি বকেয়া</option>
                <option value="inactive">নিষ্ক্রিয়</option>
            </select>
        </div>
        <div class="f-field f-search">
            <label>খুঁজুন</label>
            <div class="shell">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="নাম বা স্টুডেন্ট আইডি লিখুন…">
            </div>
        </div>
        <button class="f-reset" wire:click="resetFilters">রিসেট</button>
    </div>

    <div class="table-card">
        <table>
            <thead><tr>
                <th>শিক্ষার্থী</th><th>শ্রেণি/শাখা</th><th>অভিভাবক</th><th>মোবাইল</th><th>স্ট্যাটাস</th><th>কার্যক্রম</th>
            </tr></thead>
            <tbody>
                @forelse ($students as $student)
                    @php
                        $guardianName = $student->guardians->first()?->name;
                        $hasDue = \App\Models\FeeCollection::where('student_id', $student->id)->whereIn('status', ['due','partial','overdue'])->exists();
                    @endphp
                    <tr wire:key="student-{{ $student->id }}">
                        <td>
                            <div class="stud">
                                <div class="ini">{{ mb_substr($student->name, 0, 1) }}</div>
                                <div>
                                    <div class="name">{{ $student->name }}</div>
                                    <div class="id">{{ $student->student_id_no }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $student->schoolClass?->full_label ?? '—' }}@if($student->section), {{ $student->section->name }}@endif</td>
                        <td>{{ $guardianName ?? '—' }}</td>
                        <td>{{ $student->guardian_phone ?? '—' }}</td>
                        <td>
                            @if ($hasDue)
                                <span class="pill due">বকেয়া</span>
                            @elseif ($student->status === 'active')
                                <span class="pill active">সক্রিয়</span>
                            @else
                                <span class="pill day">নিষ্ক্রিয়</span>
                            @endif
                        </td>
                        <td>
                            <div class="row-actions">
                                <a href="{{ route('api.students.show', $student) }}" title="দেখুন">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1.5 12S5 5 12 5s10.5 7 10.5 7-3.5 7-10.5 7S1.5 12 1.5 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                <a href="{{ route('stub', urlencode('শিক্ষার্থী সম্পাদনা')) }}" title="সম্পাদনা">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো শিক্ষার্থী পাওয়া যায়নি</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="table-foot">
            <div>মোট {{ $students->total() }} জনের মধ্যে {{ $students->firstItem() ?? 0 }}–{{ $students->lastItem() ?? 0 }} জন দেখানো হচ্ছে</div>
            <div class="pager">{{ $students->links() }}</div>
        </div>
    </div>
</div>