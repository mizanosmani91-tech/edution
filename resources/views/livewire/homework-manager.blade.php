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
            <thead><tr><th>শিরোনাম</th><th>শ্রেণি</th><th>বিষয়</th><th>শিক্ষক</th><th>জমার শেষ তারিখ</th><th>পড়া আদায়</th><th></th></tr></thead>
            <tbody>
                @forelse ($homeworks as $h)
                    <tr wire:key="hw-{{ $h->id }}">
                        <td>{{ $h->title }}</td>
                        <td>{{ $h->schoolClass->full_label ?? '—' }}@if($h->section), {{ $h->section->name }}@endif</td>
                        <td>{{ $h->subject->name ?? '—' }}</td>
                        <td>{{ $h->teacher->name ?? '—' }}</td>
                        <td>{{ $h->due_date->format('d M, Y') }}</td>
                        <td>
                            @if ($h->completions_count > 0)
                                <span class="pill {{ $h->done_count >= $h->completions_count ? 'active' : 'day' }}">{{ $h->done_count }}/{{ $h->completions_count }} চেক করা</span>
                            @else
                                <span class="pill due">এখনো চেক করা হয়নি</span>
                            @endif
                        </td>
                        <td>
                            <div class="row-actions">
                                <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="openCheckModal('{{ $h->id }}')">যাচাই করুন</button>
                                <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="edit('{{ $h->id }}')">সম্পাদনা</button>
                                <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="delete('{{ $h->id }}')" wire:confirm="মুছে ফেলতে চান?">মুছুন</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো হোমওয়ার্ক পাওয়া যায়নি</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:14px;">{{ $homeworks->links() }}</div>

    {{-- পড়া আদায়/চেক-অফ রোস্টার --}}
    @if ($checkingHomeworkId && $checkingHomework)
        <div class="modal-overlay" wire:click.self="closeCheckModal">
            <div class="modal-box" style="max-width:520px;">
                <div class="modal-head">
                    <h3>পড়া আদায় — {{ $checkingHomework->title }}</h3>
                    <button class="modal-close" wire:click="closeCheckModal" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <p class="sub" style="margin-top:-8px;">প্রতিটা শিক্ষার্থীর জন্য পড়া করেছে/করেনি/আংশিক বেছে দিন।</p>
                <div style="max-height:340px;overflow-y:auto;display:flex;flex-direction:column;gap:8px;">
                    @forelse ($checkingStudents as $st)
                        @php $current = $checkingCompletions->get($st->id)?->status; @endphp
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 12px;border:1px solid var(--line);border-radius:10px;">
                            <div style="font-weight:600;font-size:13px;">{{ $st->name }}</div>
                            <div class="status-group">
                                <button type="button" class="status-btn {{ $current === 'done' ? 'active' : '' }}" data-status="present" wire:click="markCompletion('{{ $st->id }}', 'done')" title="করেছে">
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                                </button>
                                <button type="button" class="status-btn {{ $current === 'partial' ? 'active' : '' }}" data-status="late" wire:click="markCompletion('{{ $st->id }}', 'partial')" title="আংশিক">
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 2"/></svg>
                                </button>
                                <button type="button" class="status-btn {{ $current === 'not_done' ? 'active' : '' }}" data-status="absent" wire:click="markCompletion('{{ $st->id }}', 'not_done')" title="করেনি">
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="sub">এই ক্লাসে কোনো শিক্ষার্থী পাওয়া যায়নি।</p>
                    @endforelse
                </div>
                <div class="modal-foot">
                    <button class="btn-primary" wire:click="closeCheckModal" type="button">বন্ধ করুন</button>
                </div>
            </div>
        </div>
    @endif

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
                @unless ($isTeacher)
                    <div class="field">
                        <label>শিক্ষক</label>
                        <select wire:model="teacherId">
                            <option value="">নির্বাচন করুন</option>
                            @foreach ($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endunless
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
