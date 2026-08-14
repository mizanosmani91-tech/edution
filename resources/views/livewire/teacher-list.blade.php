<div>
    <div class="page-head">
        <div>
            <h2>সকল শিক্ষক ও স্টাফ</h2>
            <p>মোট {{ number_format($totalTeachers) }} জন কর্মরত আছেন — অনুসন্ধান করুন অথবা নতুন নিয়োগ দিন</p>
        </div>
        <div class="row-actions">
            <a href="{{ route('import.teachers') }}" class="btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12M7 10l5 5 5-5"/><path d="M5 21h14"/></svg>
                ইমপোর্ট করুন
            </a>
            <a href="{{ route('teachers.hire') }}" class="btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
                নতুন শিক্ষক নিয়োগ
            </a>
        </div>
    </div>

    <div class="stat-strip">
        <div class="stat-chip" style="--accent:var(--teacher)">
            <div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="6" width="16" height="13" rx="2"/><path d="M9 6V5a3 3 0 0 1 6 0v1"/></svg></div>
            <div><div class="sv">{{ number_format($totalTeachers) }}</div><div class="sl">মোট কর্মরত</div></div>
        </div>
        <div class="stat-chip" style="--accent:var(--good)">
            <div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="m8.5 12 2.3 2.3L16 9.7"/></svg></div>
            <div><div class="sv">{{ number_format($activeTeachers) }}</div><div class="sl">সক্রিয়</div></div>
        </div>
        <div class="stat-chip" style="--accent:var(--gold)">
            <div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M8 7V3M16 7V3M4 11h16M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/></svg></div>
            <div><div class="sv">{{ number_format($onLeaveTeachers) }}</div><div class="sl">ছুটিতে</div></div>
        </div>
    </div>

    <div class="filter-card">
        <div class="f-field">
            <label>পদবি</label>
            <select wire:model.live="designationFilter">
                <option value="">সকল পদবি</option>
                @foreach ($designations as $d)
                    <option value="{{ $d }}">{{ $d }}</option>
                @endforeach
            </select>
        </div>
        <div class="f-field">
            <label>স্ট্যাটাস</label>
            <select wire:model.live="statusFilter">
                <option value="">সকল</option>
                <option value="active">সক্রিয়</option>
                <option value="leave">ছুটিতে</option>
                <option value="inactive">নিষ্ক্রিয়</option>
            </select>
        </div>
        <div class="f-field f-search">
            <label>খুঁজুন</label>
            <div class="shell">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="নাম বা স্টাফ আইডি লিখুন…">
            </div>
        </div>
        <button class="f-reset" wire:click="resetFilters">রিসেট</button>
    </div>

    <div class="table-card">
        <table>
            <thead><tr>
                <th>শিক্ষক</th><th>পদবি</th><th>বিষয়</th><th>নির্ধারিত ক্লাস</th><th>মোবাইল</th><th>যোগদান</th><th>স্ট্যাটাস</th><th>কার্যক্রম</th>
            </tr></thead>
            <tbody>
                @forelse ($teachers as $teacher)
                    @php
                        $periods = $routineData->get($teacher->id, collect());
                        $subjects = $periods->pluck('subject.name')->unique()->filter()->implode(', ');
                        $classes = $periods->pluck('schoolClass.name')->unique()->filter()->implode(', ');
                    @endphp
                    <tr wire:key="teacher-{{ $teacher->id }}">
                        <td>
                            <a href="{{ route('teachers.profile', $teacher) }}" class="stud">
                                <div class="ini">{{ mb_substr($teacher->name, 0, 1) }}</div>
                                <div>
                                    <div class="name">{{ $teacher->name }}</div>
                                    <div class="id">{{ $teacher->teacher_id_no }}</div>
                                </div>
                            </a>
                        </td>
                        <td>{{ $teacher->designation ?? '—' }}</td>
                        <td>{{ $subjects ?: '—' }}</td>
                        <td>{{ $classes ?: '—' }}</td>
                        <td>{{ $teacher->phone ?? '—' }}</td>
                        <td>{{ $teacher->joining_date ? \Carbon\Carbon::parse($teacher->joining_date)->diffForHumans(null, true) : '—' }}</td>
                        <td>
                            @if ($teacher->status === 'active')
                                <span class="pill active">সক্রিয়</span>
                            @elseif ($teacher->status === 'leave')
                                <span class="pill leave">ছুটিতে</span>
                            @else
                                <span class="pill inactive">নিষ্ক্রিয়</span>
                            @endif
                        </td>
                        <td>
                            <div class="row-actions">
                                <a href="{{ route('teachers.profile', $teacher) }}" title="প্রোফাইল দেখুন">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1.5 12S5 5 12 5s10.5 7 10.5 7-3.5 7-10.5 7S1.5 12 1.5 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                <a href="{{ route('stub', urlencode('শিক্ষক সম্পাদনা')) }}" title="সম্পাদনা">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো শিক্ষক পাওয়া যায়নি</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="table-foot">
            <div>মোট {{ $teachers->total() }} জনের মধ্যে {{ $teachers->firstItem() ?? 0 }}–{{ $teachers->lastItem() ?? 0 }} জন দেখানো হচ্ছে</div>
            <div class="pager">{{ $teachers->links() }}</div>
        </div>
    </div>
</div>