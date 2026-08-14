<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">একাডেমিক / প্রশ্ন ব্যাংক</div>
            <h2>প্রশ্ন ব্যাংক</h2>
        </div>
        <button class="btn-primary" wire:click="openModal" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            নতুন প্রশ্ন
        </button>
    </div>

    <div class="select-card" style="margin-bottom:16px;display:flex;gap:12px;flex-wrap:wrap;">
        <select wire:model.live="classFilter" style="max-width:220px;">
            <option value="">সকল শ্রেণি</option>
            @foreach ($classes as $c)
                <option value="{{ $c->id }}">{{ $c->full_label }}</option>
            @endforeach
        </select>
        <select wire:model.live="subjectFilter" style="max-width:220px;">
            <option value="">সকল বিষয়</option>
            @foreach ($subjects as $s)
                <option value="{{ $s->id }}">{{ $s->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="table-card">
        <table>
            <thead><tr><th>প্রশ্ন</th><th>শ্রেণি</th><th>বিষয়</th><th>ধরন</th><th>মান</th><th>কঠিনতা</th><th></th></tr></thead>
            <tbody>
                @forelse ($questions as $q)
                    <tr wire:key="qb-{{ $q->id }}">
                        <td style="max-width:320px;">{{ \Illuminate\Support\Str::limit($q->question_text, 70) }}</td>
                        <td>{{ $q->schoolClass->full_label ?? '—' }}</td>
                        <td>{{ $q->subject->name ?? '—' }}</td>
                        <td>{{ match($q->question_type) { 'mcq' => 'MCQ', 'essay' => 'রচনামূলক', default => 'সংক্ষিপ্ত' } }}</td>
                        <td>{{ $q->marks }}</td>
                        <td><span class="pill {{ $q->difficulty === 'hard' ? 'due' : ($q->difficulty === 'medium' ? 'day' : 'active') }}">{{ match($q->difficulty) { 'hard' => 'কঠিন', 'easy' => 'সহজ', default => 'মাঝারি' } }}</span></td>
                        <td>
                            <div class="row-actions">
                                <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="edit('{{ $q->id }}')">সম্পাদনা</button>
                                <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="delete('{{ $q->id }}')" wire:confirm="মুছে ফেলতে চান?">মুছুন</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো প্রশ্ন যুক্ত করা হয়নি</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:14px;">{{ $questions->links() }}</div>

    @if ($showModal)
        <div class="modal-overlay" wire:click.self="$set('showModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>{{ $editingId ? 'প্রশ্ন সম্পাদনা' : 'নতুন প্রশ্ন' }}</h3>
                    <button class="modal-close" wire:click="$set('showModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="field">
                        <label>শ্রেণি</label>
                        <select wire:model="classId">
                            <option value="">নির্বাচন করুন</option>
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->full_label }}</option>
                            @endforeach
                        </select>
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
                <div class="info-grid" style="grid-template-columns:1fr 1fr 1fr;">
                    <div class="field">
                        <label>প্রশ্নের ধরন</label>
                        <select wire:model.live="questionType">
                            <option value="short">সংক্ষিপ্ত</option>
                            <option value="mcq">MCQ</option>
                            <option value="essay">রচনামূলক</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>কঠিনতা</label>
                        <select wire:model="difficulty">
                            <option value="easy">সহজ</option>
                            <option value="medium">মাঝারি</option>
                            <option value="hard">কঠিন</option>
                        </select>
                    </div>
                    <div class="field"><label>মান</label><input type="number" min="1" max="100" wire:model="marks"></div>
                </div>
                <div class="field">
                    <label>প্রশ্ন <span class="req">*</span></label>
                    <textarea wire:model="questionText" rows="3"></textarea>
                    @error('questionText') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>

                @if ($questionType === 'mcq')
                    <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                        @foreach ($mcqOptions as $i => $opt)
                            <div class="field"><label>অপশন {{ $i + 1 }}</label><input type="text" wire:model="mcqOptions.{{ $i }}"></div>
                        @endforeach
                    </div>
                    <div class="field"><label>সঠিক উত্তর</label><input type="text" wire:model="correctAnswer" placeholder="সঠিক অপশনের টেক্সট লিখুন"></div>
                @endif

                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="save" type="button">সংরক্ষণ করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>
