<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">একাডেমিক / ক্লাস ডায়েরি</div>
            <h2 style="margin:0;">ক্লাস ডায়েরি</h2>
        </div>
        <button class="btn-primary" wire:click="openModal" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            নতুন ডায়েরি লিখুন
        </button>
    </div>

    <div class="select-card" style="margin-bottom:16px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
        <button class="btn-ghost" wire:click="previousDay" type="button" aria-label="আগের দিন">‹</button>
        <input type="date" wire:model.live="dateFilter" style="max-width:170px;">
        <button class="btn-ghost" wire:click="nextDay" type="button" aria-label="পরের দিন">›</button>
        <select wire:model.live="classFilter" style="max-width:220px;">
            <option value="">সকল শ্রেণি</option>
            @foreach ($classes as $c)
                <option value="{{ $c->id }}">{{ $c->full_label }}</option>
            @endforeach
        </select>
    </div>

    @forelse ($entries as $e)
        <div class="card" style="margin-bottom:12px;" wire:key="diary-{{ $e->id }}">
            <div class="card-head" style="display:flex;justify-content:space-between;gap:10px;align-items:start;">
                <div>
                    <h3 style="margin:0;">{{ $e->topic }}</h3>
                    <div class="cc-sub">
                        {{ $e->schoolClass->full_label ?? '—' }}@if($e->section), {{ $e->section->name }}@endif
                        @if($e->subject) · {{ $e->subject->name }}@endif
                        @if($e->teacher) · {{ $e->teacher->name }}@endif
                    </div>
                </div>
                <div class="row-actions">
                    <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="edit('{{ $e->id }}')" type="button">সম্পাদনা</button>
                    <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="delete('{{ $e->id }}')" wire:confirm="এই ডায়েরি মুছে ফেলতে চান?" type="button">মুছুন</button>
                </div>
            </div>
            @if ($e->classwork)
                <p style="margin:8px 0 0;"><strong>ক্লাসে যা হয়েছে:</strong> {!! nl2br(e($e->classwork)) !!}</p>
            @endif
            @if ($e->homework)
                <p style="margin:6px 0 0;"><strong>বাড়ির কাজ:</strong> {!! nl2br(e($e->homework)) !!}</p>
            @endif
            @if ($e->remarks)
                <p style="margin:6px 0 0;color:var(--ink-soft);"><strong>অভিভাবকের জন্য:</strong> {!! nl2br(e($e->remarks)) !!}</p>
            @endif
        </div>
    @empty
        <div class="card">
            <div class="empty-note"><div class="en-title">এই দিনে কোনো ডায়েরি লেখা হয়নি</div></div>
        </div>
    @endforelse

    <div style="margin-top:14px;">{{ $entries->links() }}</div>

    @if ($showModal)
        <div class="modal-overlay" wire:click.self="$set('showModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>{{ $editingId ? 'ডায়েরি সম্পাদনা' : 'নতুন ক্লাস ডায়েরি' }}</h3>
                    <button class="modal-close" wire:click="$set('showModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="row-2">
                    <div class="field">
                        <label>শ্রেণি <span class="req">*</span></label>
                        <select wire:model.live="classId">
                            <option value="">— নির্বাচন করুন —</option>
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->full_label }}</option>
                            @endforeach
                        </select>
                        @error('classId') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label>সেকশন <span class="opt">— খালি রাখলে সব সেকশন</span></label>
                        <select wire:model="sectionId">
                            <option value="">সব সেকশন</option>
                            @foreach ($sections as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('sectionId') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="row-2">
                    <div class="field">
                        <label>বিষয়</label>
                        <select wire:model="subjectId">
                            <option value="">—</option>
                            @foreach ($subjects as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>তারিখ <span class="req">*</span></label>
                        <input type="date" wire:model="entryDate">
                        @error('entryDate') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    </div>
                </div>

                @unless ($isTeacher)
                    <div class="field">
                        <label>শিক্ষক</label>
                        <select wire:model="teacherId">
                            <option value="">—</option>
                            @foreach ($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endunless

                <div class="field">
                    <label>আজকের বিষয় <span class="req">*</span></label>
                    <input type="text" wire:model="topic" placeholder="যেমন: ভগ্নাংশের যোগ-বিয়োগ">
                    @error('topic') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>

                <div class="field">
                    <label>ক্লাসে যা হয়েছে</label>
                    <textarea wire:model="classwork" rows="3"></textarea>
                </div>
                <div class="field">
                    <label>বাড়ির কাজ</label>
                    <textarea wire:model="homework" rows="3"></textarea>
                </div>
                <div class="field">
                    <label>অভিভাবকের জন্য মন্তব্য <span class="opt">— ঐচ্ছিক</span></label>
                    <textarea wire:model="remarks" rows="2"></textarea>
                </div>

                <div class="modal-actions" style="display:flex;gap:8px;justify-content:flex-end;">
                    <button class="btn-ghost" type="button" wire:click="$set('showModal', false)">বাতিল</button>
                    <button class="btn-primary" type="button" wire:click="save">সংরক্ষণ</button>
                </div>
            </div>
        </div>
    @endif
</div>
