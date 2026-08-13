<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">শিক্ষার্থী / Alumni</div>
            <h2>Alumni ডিরেক্টরি</h2>
        </div>
        <button class="btn-primary" wire:click="openAddModal" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            Alumni তালিকায় যুক্ত করুন
        </button>
    </div>

    <div class="stat-strip">
        <div class="stat-chip" style="--accent:var(--maroon)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5"/></svg></div><div><div class="sv">{{ $totalAlumni }}</div><div class="sl">মোট Alumni</div></div></div>
    </div>

    <div class="select-card" style="margin-bottom:16px;display:flex;gap:12px;flex-wrap:wrap;">
        <select wire:model.live="classId" style="max-width:220px;">
            <option value="">সকল শ্রেণি (পূর্বের)</option>
            @foreach ($classes as $c)
                <option value="{{ $c->id }}">{{ $c->full_label }}</option>
            @endforeach
        </select>
        <input type="text" wire:model.live.debounce.400ms="search" placeholder="নাম দিয়ে খুঁজুন…" style="max-width:260px;">
    </div>

    <div class="table-card">
        <table>
            <thead><tr><th>নাম</th><th>আইডি নং</th><th>সর্বশেষ শ্রেণি</th><th>আপডেট</th><th></th></tr></thead>
            <tbody>
                @forelse ($alumni as $a)
                    <tr wire:key="al-{{ $a->id }}">
                        <td>{{ $a->name }}</td>
                        <td>{{ $a->student_id_no }}</td>
                        <td>{{ $a->schoolClass?->full_label ?? '—' }}@if($a->section), {{ $a->section->name }}@endif</td>
                        <td>{{ $a->updated_at->format('d M, Y') }}</td>
                        <td>
                            <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="restoreActive('{{ $a->id }}')" wire:confirm="এই শিক্ষার্থীকে আবার সক্রিয় তালিকায় ফেরত আনতে চান?">ফেরত আনুন</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;color:var(--ink-soft);padding:30px 0;">এখনো কোনো Alumni যুক্ত করা হয়নি</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:14px;">{{ $alumni->links() }}</div>

    @if ($showAddModal)
        <div class="modal-overlay" wire:click.self="$set('showAddModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>Alumni তালিকায় যুক্ত করুন</h3>
                    <button class="modal-close" wire:click="$set('showAddModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="field">
                    <label>সক্রিয় শিক্ষার্থীর নাম লিখুন</label>
                    <input type="text" wire:model.live.debounce.400ms="addSearch" placeholder="নাম লিখুন…">
                </div>
                <div class="stud-list-plain" style="max-height:280px;overflow-y:auto;">
                    @forelse ($this->activeMatches as $s)
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 4px;border-bottom:1px solid var(--line);">
                            <span style="font-size:13.5px;">{{ $s->name }} <span style="color:var(--ink-soft);font-size:12px;">({{ $s->student_id_no }})</span></span>
                            <button class="btn-primary" style="padding:5px 12px;font-size:12px;" wire:click="markAlumni('{{ $s->id }}')">যুক্ত করুন</button>
                        </div>
                    @empty
                        @if ($addSearch)
                            <p style="text-align:center;color:var(--ink-soft);font-size:13px;padding:12px 0;">কোনো শিক্ষার্থী পাওয়া যায়নি</p>
                        @endif
                    @endforelse
                </div>
                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showAddModal', false)" type="button">বন্ধ করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>
