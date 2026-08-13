<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">হাজিরা / স্টাফ হাজিরা</div>
            <h2 style="margin:0;">স্টাফ হাজিরা</h2>
        </div>
    </div>

    <div class="stat-strip">
        <div class="stat-chip" style="--accent:var(--good)">
            <div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20 6 9 17l-5-5"/></svg></div>
            <div><div class="sv">{{ $presentCount }}</div><div class="sl">আজ উপস্থিত</div></div>
        </div>
        <div class="stat-chip" style="--accent:var(--ink-soft)">
            <div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="9" cy="8" r="3"/><path d="M2.5 20c.9-3.4 3.6-5 6.5-5s5.6 1.6 6.5 5"/></svg></div>
            <div><div class="sv">{{ $totalCount }}</div><div class="sl">মোট স্টাফ</div></div>
        </div>
    </div>

    <div class="filter-card">
        <div class="f-field">
            <label>তারিখ</label>
            <input type="date" wire:model.live="date">
        </div>
        <div class="f-field f-search">
            <label>খুঁজুন</label>
            <div class="shell">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="নাম লিখুন…">
            </div>
        </div>
    </div>

    <div class="table-card">
        <table>
            <thead><tr>
                <th>স্টাফ</th><th>পদবি</th><th>চেক ইন</th><th>চেক আউট</th><th>কর্মঘণ্টা</th><th>স্ট্যাটাস</th><th>কার্যক্রম</th>
            </tr></thead>
            <tbody>
                @forelse ($teachers as $teacher)
                    @php $rec = $records->get($teacher->id); @endphp
                    <tr wire:key="staff-att-{{ $teacher->id }}">
                        <td>
                            <div class="stud">
                                <div class="ini">{{ mb_substr($teacher->name, 0, 1) }}</div>
                                <div><div class="name">{{ $teacher->name }}</div><div class="id">{{ $teacher->teacher_id_no }}</div></div>
                            </div>
                        </td>
                        <td>{{ $teacher->designation ?? '—' }}</td>
                        <td>{{ $rec?->check_in?->format('h:i A') ?? '—' }}</td>
                        <td>{{ $rec?->check_out?->format('h:i A') ?? '—' }}</td>
                        <td>{{ $rec?->work_hours ?? '—' }}</td>
                        <td>
                            @if ($rec)
                                <span class="pill {{ $rec->status === 'present' ? 'active' : ($rec->status === 'late' ? 'day' : 'due') }}">
                                    {{ match($rec->status) { 'present' => 'উপস্থিত', 'late' => 'দেরিতে', 'leave' => 'ছুটি', default => 'অনুপস্থিত' } }}
                                </span>
                            @else
                                <span class="pill day">চিহ্নিত হয়নি</span>
                            @endif
                        </td>
                        <td>
                            <div class="row-actions">
                                @if (!$rec?->check_in)
                                    <button wire:click="checkIn('{{ $teacher->id }}')" class="btn-primary" style="padding:6px 12px;font-size:12.5px;">চেক ইন</button>
                                @elseif (!$rec?->check_out)
                                    <button wire:click="checkOut('{{ $teacher->id }}')" class="btn-ghost" style="padding:6px 12px;font-size:12.5px;">চেক আউট</button>
                                @endif
                                <button wire:click="markStatus('{{ $teacher->id }}','absent')" title="অনুপস্থিত মার্ক করুন">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M9 9l6 6M15 9l-6 6"/></svg>
                                </button>
                                <button wire:click="markStatus('{{ $teacher->id }}','leave')" title="ছুটি মার্ক করুন">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="5" width="16" height="16" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো সক্রিয় স্টাফ পাওয়া যায়নি</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="table-foot">
            <div>মোট {{ $totalCount }} জনের মধ্যে {{ $presentCount }} জন আজ উপস্থিত</div>
        </div>
    </div>
</div>
