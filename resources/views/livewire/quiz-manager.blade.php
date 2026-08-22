<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">একাডেমিক / অনলাইন কুইজ</div>
            <h2>অনলাইন কুইজ</h2>
            <p>প্রশ্ন ব্যাংকের MCQ প্রশ্ন দিয়ে কুইজ বানান — শিক্ষার্থী অনলাইনে দিয়ে সাথে সাথে ফলাফল পাবে</p>
        </div>
        <button class="btn-primary" wire:click="openModal" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            নতুন কুইজ
        </button>
    </div>

    @if (! $hasAnyMcqQuestions)
        <div class="alert-note" style="margin-bottom:16px;">
            এখনো কোনো MCQ প্রশ্ন প্রশ্ন ব্যাংকে যোগ করা হয়নি। কুইজ বানাতে হলে আগে "প্রশ্ন ব্যাংক" পেজ থেকে কিছু MCQ প্রশ্ন (সঠিক উত্তরসহ) যোগ করুন।
        </div>
    @endif

    <div class="table-card">
        <table>
            <thead><tr><th>শিরোনাম</th><th>শ্রেণি/বিষয়</th><th>সময়সীমা</th><th>প্রশ্ন</th><th>স্ট্যাটাস</th><th></th></tr></thead>
            <tbody>
                @forelse ($quizzes as $quiz)
                    <tr wire:key="quiz-{{ $quiz->id }}">
                        <td style="font-weight:600;">{{ $quiz->title }}</td>
                        <td>{{ $quiz->schoolClass->full_label ?? '—' }} @if($quiz->subject) — {{ $quiz->subject->name }} @endif</td>
                        <td style="font-size:12.5px;">
                            {{ $quiz->duration_minutes }} মিনিট
                            @if ($quiz->starts_at)
                                <br><span class="sub">{{ $quiz->starts_at->format('d M, h:i A') }} — {{ $quiz->ends_at?->format('d M, h:i A') ?? 'শেষ সময় নেই' }}</span>
                            @endif
                        </td>
                        <td>{{ $quiz->questions->count() }}টা ({{ $quiz->total_marks }} নম্বর)</td>
                        <td>
                            <span class="pill {{ $quiz->is_published ? 'active' : 'due' }}">{{ $quiz->is_published ? 'প্রকাশিত' : 'খসড়া' }}</span>
                        </td>
                        <td>
                            <div class="row-actions" style="flex-wrap:wrap;gap:6px;">
                                <button class="btn-ghost" style="padding:6px 10px;font-size:12px;" wire:click="edit('{{ $quiz->id }}')">সম্পাদনা</button>
                                <button class="btn-ghost" style="padding:6px 10px;font-size:12px;" wire:click="togglePublish('{{ $quiz->id }}')">{{ $quiz->is_published ? 'অপ্রকাশিত করুন' : 'প্রকাশ করুন' }}</button>
                                <button class="btn-ghost" style="padding:6px 10px;font-size:12px;" wire:click="viewResults('{{ $quiz->id }}')">ফলাফল</button>
                                <button class="btn-ghost" style="padding:6px 10px;font-size:12px;" wire:click="delete('{{ $quiz->id }}')" wire:confirm="মুছে ফেলতে চান? সব শিক্ষার্থীর উত্তরও মুছে যাবে।">মুছুন</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো কুইজ তৈরি করা হয়নি</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:14px;">{{ $quizzes->links() }}</div>

    {{-- ================= নতুন/সম্পাদনা মোডাল ================= --}}
    @if ($showModal)
        <div class="modal-overlay" wire:click.self="$set('showModal', false)">
            <div class="modal-box" style="max-width:640px;">
                <div class="modal-head">
                    <h3>{{ $editingId ? 'কুইজ সম্পাদনা' : 'নতুন কুইজ' }}</h3>
                    <button class="modal-close" wire:click="$set('showModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="field">
                    <label>শিরোনাম <span class="req">*</span></label>
                    <input type="text" wire:model="title" placeholder="যেমন: গণিত — অধ্যায় ৩ কুইজ">
                    @error('title') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>

                <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="field">
                        <label>শ্রেণি <span class="req">*</span></label>
                        <select wire:model.live="classId">
                            <option value="">নির্বাচন করুন</option>
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->full_label }}</option>
                            @endforeach
                        </select>
                        @error('classId') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label>বিষয় <span class="opt">(ঐচ্ছিক)</span></label>
                        <select wire:model.live="subjectId">
                            <option value="">সকল বিষয়</option>
                            @foreach ($subjects as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="info-grid" style="grid-template-columns:1fr 1fr 1fr;">
                    <div class="field"><label>সময়সীমা (মিনিট)</label><input type="number" min="1" max="180" wire:model="durationMinutes"></div>
                    <div class="field"><label>শুরু <span class="opt">(ঐচ্ছিক)</span></label><input type="datetime-local" wire:model="startsAt"></div>
                    <div class="field"><label>শেষ <span class="opt">(ঐচ্ছিক)</span></label><input type="datetime-local" wire:model="endsAt"></div>
                </div>

                <div class="field" style="display:flex;align-items:center;gap:8px;">
                    <label class="switch"><input type="checkbox" wire:model="shuffleQuestions"><span class="switch-track"></span></label>
                    <label style="margin:0;">প্রতিটা শিক্ষার্থীর জন্য প্রশ্নের ক্রম এলোমেলো করুন</label>
                </div>

                <div class="field">
                    <label>প্রশ্ন নির্বাচন করুন <span class="req">*</span></label>
                    @error('selectedQuestionIds') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror

                    @if (! $classId)
                        <p class="hint">আগে একটা শ্রেণি নির্বাচন করুন — সেই শ্রেণির MCQ প্রশ্ন এখানে দেখা যাবে।</p>
                    @elseif ($availableQuestions->isEmpty())
                        <p class="hint">এই শ্রেণি/বিষয়ের জন্য কোনো MCQ প্রশ্ন পাওয়া যায়নি। প্রশ্ন ব্যাংকে আগে যোগ করুন।</p>
                    @else
                        <div style="max-height:260px;overflow-y:auto;border:1px solid var(--line);border-radius:10px;padding:8px;">
                            @foreach ($availableQuestions as $q)
                                <label style="display:flex;align-items:flex-start;gap:8px;padding:8px;border-bottom:1px solid var(--line);cursor:pointer;">
                                    <input type="checkbox" wire:model="selectedQuestionIds" value="{{ $q->id }}" style="margin-top:3px;">
                                    <span style="flex:1;font-size:13.5px;">
                                        {{ $q->question_text }}
                                        <span class="sub" style="display:block;font-size:11.5px;">{{ $q->subject->name ?? '' }} · সঠিক উত্তর: {{ $q->correct_answer }}</span>
                                    </span>
                                    <input type="number" min="1" max="100" wire:model="questionMarks.{{ $q->id }}" placeholder="মান" style="width:60px;font-size:12px;">
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="save" type="button">সংরক্ষণ করুন</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ================= ফলাফল মোডাল ================= --}}
    @if ($showResultsModal && $resultsQuiz)
        <div class="modal-overlay" wire:click.self="$set('showResultsModal', false)">
            <div class="modal-box" style="max-width:560px;">
                <div class="modal-head">
                    <h3>ফলাফল — {{ $resultsQuiz->title }}</h3>
                    <button class="modal-close" wire:click="$set('showResultsModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="table-card" style="box-shadow:none;border:1px solid var(--line);">
                    <table>
                        <thead><tr><th>শিক্ষার্থী</th><th>স্কোর</th><th>জমার সময়</th></tr></thead>
                        <tbody>
                            @forelse ($resultsAttempts as $attempt)
                                <tr>
                                    <td>{{ $attempt->student->name ?? '—' }}</td>
                                    <td style="font-weight:600;">{{ $attempt->score }}/{{ $attempt->total_marks }}</td>
                                    <td style="font-size:12px;">{{ $attempt->submitted_at?->format('d M, h:i A') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" style="text-align:center;color:var(--ink-soft);padding:20px 0;">এখনো কেউ জমা দেয়নি</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
