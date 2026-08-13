<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">শিক্ষার্থী / আচরণ রেকর্ড</div>
            <h2>Discipline / আচরণ রেকর্ড</h2>
        </div>
        <button class="btn-primary" wire:click="openModal" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            নতুন রেকর্ড যুক্ত করুন
        </button>
    </div>

    <div class="select-card" style="margin-bottom:16px;display:flex;gap:12px;flex-wrap:wrap;">
        <select wire:model.live="classId" style="max-width:220px;">
            <option value="">সকল শ্রেণি</option>
            @foreach ($classes as $c)
                <option value="{{ $c->id }}">{{ $c->full_label }}</option>
            @endforeach
        </select>
        <input type="text" wire:model.live.debounce.400ms="search" placeholder="শিক্ষার্থীর নাম দিয়ে খুঁজুন…" style="max-width:260px;">
    </div>

    <div class="table-card">
        <table>
            <thead><tr><th>তারিখ</th><th>শিক্ষার্থী</th><th>ক্যাটাগরি</th><th>মাত্রা</th><th>বিবরণ</th><th></th></tr></thead>
            <tbody>
                @forelse ($records as $r)
                    <tr wire:key="dr-{{ $r->id }}">
                        <td>{{ $r->date->format('d M, Y') }}</td>
                        <td>{{ $r->student->name ?? '—' }}</td>
                        <td>{{ match($r->category) { 'attendance' => 'উপস্থিতি', 'behavior' => 'আচরণ', 'academic' => 'একাডেমিক', 'other' => 'অন্যান্য', default => 'সাধারণ' } }}</td>
                        <td><span class="pill {{ $r->severity === 'severe' ? 'due' : ($r->severity === 'moderate' ? 'day' : 'active') }}">{{ match($r->severity) { 'severe' => 'গুরুতর', 'moderate' => 'মাঝারি', default => 'সামান্য' } }}</span></td>
                        <td style="max-width:280px;">{{ \Illuminate\Support\Str::limit($r->description, 60) }}</td>
                        <td>
                            <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="delete('{{ $r->id }}')" wire:confirm="এই রেকর্ডটি মুছে ফেলতে চান?">মুছুন</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো আচরণ রেকর্ড পাওয়া যায়নি</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:14px;">{{ $records->links() }}</div>

    @if ($showModal)
        <div class="modal-overlay" wire:click.self="$set('showModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>নতুন আচরণ রেকর্ড</h3>
                    <button class="modal-close" wire:click="$set('showModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="field">
                    <label>শিক্ষার্থী <span class="req">*</span></label>
                    <select wire:model="studentId">
                        <option value="">নির্বাচন করুন</option>
                        @foreach ($studentOptions as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->student_id_no }})</option>
                        @endforeach
                    </select>
                    @error('studentId') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="field">
                        <label>তারিখ <span class="req">*</span></label>
                        <input type="date" wire:model="date">
                    </div>
                    <div class="field">
                        <label>ক্যাটাগরি</label>
                        <select wire:model="category">
                            <option value="general">সাধারণ</option>
                            <option value="attendance">উপস্থিতি</option>
                            <option value="behavior">আচরণ</option>
                            <option value="academic">একাডেমিক</option>
                            <option value="other">অন্যান্য</option>
                        </select>
                    </div>
                </div>
                <div class="field">
                    <label>মাত্রা</label>
                    <select wire:model="severity">
                        <option value="minor">সামান্য</option>
                        <option value="moderate">মাঝারি</option>
                        <option value="severe">গুরুতর</option>
                    </select>
                </div>
                <div class="field">
                    <label>বিবরণ <span class="req">*</span></label>
                    <textarea wire:model="description" rows="3"></textarea>
                    @error('description') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="field">
                    <label>গৃহীত ব্যবস্থা (ঐচ্ছিক)</label>
                    <textarea wire:model="actionTaken" rows="2"></textarea>
                </div>
                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="save" type="button">সংরক্ষণ করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>
