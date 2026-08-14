<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">একাডেমিক / হোমওয়ার্ক</div>
            <h2>হোমওয়ার্ক/অ্যাসাইনমেন্ট</h2>
        </div>
        <button class="btn-primary" wire:click="openModal" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            নতুন হোমওয়ার্ক
        </button>
    </div>

    <div class="select-card" style="margin-bottom:16px;">
        <select wire:model.live="classFilter" style="max-width:220px;">
            <option value="">সকল শ্রেণি</option>
            @foreach ($classes as $c)
                <option value="{{ $c->id }}">{{ $c->full_label }}</option>
            @endforeach
        </select>
    </div>

    <div class="table-card">
        <table>
            <thead><tr><th>শিরোনাম</th><th>শ্রেণি</th><th>বিষয়</th><th>শিক্ষক</th><th>জমার শেষ তারিখ</th><th></th></tr></thead>
            <tbody>
                @forelse ($homeworks as $h)
                    <tr wire:key="hw-{{ $h->id }}">
                        <td>{{ $h->title }}</td>
                        <td>{{ $h->schoolClass->full_label ?? '—' }}@if($h->section), {{ $h->section->name }}@endif</td>
                        <td>{{ $h->subject->name ?? '—' }}</td>
                        <td>{{ $h->teacher->name ?? '—' }}</td>
                        <td>{{ $h->due_date->format('d M, Y') }}</td>
                        <td>
                            <div class="row-actions">
                                <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="edit('{{ $h->id }}')">সম্পাদনা</button>
                                <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="delete('{{ $h->id }}')" wire:confirm="মুছে ফেলতে চান?">মুছুন</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো হোমওয়ার্ক পাওয়া যায়নি</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:14px;">{{ $homeworks->links() }}</div>

    @if ($showModal)
        <div class="modal-overlay" wire:click.self="$set('showModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>{{ $editingId ? 'হোমওয়ার্ক সম্পাদনা' : 'নতুন হোমওয়ার্ক' }}</h3>
                    <button class="modal-close" wire:click="$set('showModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="field">
                    <label>শিরোনাম <span class="req">*</span></label>
                    <input type="text" wire:model="title">
                    @error('title') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="field"><label>বিস্তারিত</label><textarea wire:model="description" rows="3"></textarea></div>
                <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="field">
                        <label>শ্রেণি <span class="req">*</span></label>
                        <select wire:model="classId">
                            <option value="">নির্বাচন করুন</option>
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->full_label }}</option>
                            @endforeach
                        </select>
                        @error('classId') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label>বিষয়</label>
                        <select wire:model="subjectId">
                            <option value="">নির্বাচন করুন</option>
                            @foreach ($subjects as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="field">
                    <label>শিক্ষক</label>
                    <select wire:model="teacherId">
                        <option value="">নির্বাচন করুন</option>
                        @foreach ($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="field"><label>প্রদানের তারিখ</label><input type="date" wire:model="assignedDate"></div>
                    <div class="field"><label>জমার শেষ তারিখ</label><input type="date" wire:model="dueDate"></div>
                </div>
                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="save" type="button">সংরক্ষণ করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>
