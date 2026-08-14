<div class="lifecycle-page">
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">পরীক্ষা ও ফলাফল / সময়সূচি</div>
            <h2>পরীক্ষার সময়সূচি</h2>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('import.exam-results') }}" class="btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12M7 10l5 5 5-5"/><path d="M5 21h14"/></svg>
                পুরাতন ফলাফল ইমপোর্ট
            </a>
            <button class="btn-primary" wire:click="openExamModal" type="button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
                নতুন পরীক্ষা
            </button>
        </div>
    </div>

    <div class="lc-grid">
        <aside class="picker-card">
            <div class="stud-list">
                @forelse ($exams as $exam)
                    <div class="stud-row {{ $selectedExamId === $exam->id ? 'selected-preview' : '' }}" wire:click="selectExam('{{ $exam->id }}')" wire:key="ex-{{ $exam->id }}" style="flex-direction:column;align-items:flex-start;gap:4px;">
                        <div style="display:flex;justify-content:space-between;width:100%;">
                            <span style="font-size:13px;font-weight:600;">{{ $exam->name }}</span>
                            <span class="pill {{ $exam->is_published ? 'active' : 'day' }}" style="font-size:10.5px;">{{ $exam->is_published ? 'প্রকাশিত' : 'অপ্রকাশিত' }}</span>
                        </div>
                        <span style="font-size:11px;color:var(--ink-soft);">{{ $exam->academic_year }} — {{ $exam->exam_subjects_count }}টি বিষয় নির্ধারিত</span>
                    </div>
                @empty
                    <div style="text-align:center;color:var(--ink-soft);padding:20px 0;font-size:13px;">কোনো পরীক্ষা তৈরি করা হয়নি</div>
                @endforelse
            </div>
        </aside>

        <div>
            @if ($selectedExam)
                <div class="cert-form-card" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                    <div>
                        <h3 style="font-family:'Tiro Bangla',serif;font-size:16px;margin:0 0 4px;">{{ $selectedExam->name }}</h3>
                        <p style="font-size:12px;color:var(--ink-soft);margin:0;">{{ $selectedExam->start_date?->format('d M') }} — {{ $selectedExam->end_date?->format('d M, Y') }}</p>
                    </div>
                    <div class="row-actions">
                        <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="editExam('{{ $selectedExam->id }}')">সম্পাদনা</button>
                        <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="togglePublish('{{ $selectedExam->id }}')">{{ $selectedExam->is_published ? 'অপ্রকাশিত করুন' : 'প্রকাশ করুন' }}</button>
                        <button class="btn-primary" style="padding:6px 12px;font-size:12.5px;" wire:click="openSubjectModal">বিষয় যোগ করুন</button>
                    </div>
                </div>

                <div class="table-card">
                    <table>
                        <thead><tr><th>বিষয়</th><th>শ্রেণি</th><th>শিক্ষক</th><th>পূর্ণমান</th><th>পাশ মার্ক</th><th>তারিখ</th><th>সময়</th><th>কক্ষ</th><th></th></tr></thead>
                        <tbody>
                            @forelse ($scheduleRows as $row)
                                <tr wire:key="sr-{{ $row->id }}">
                                    <td>{{ $row->subject->name ?? '—' }}</td>
                                    <td>{{ $row->schoolClass->full_label ?? '—' }}</td>
                                    <td>{{ $row->teacher->name ?? '—' }}</td>
                                    <td>{{ $row->full_marks }}</td>
                                    <td>{{ $row->pass_marks }}</td>
                                    <td>{{ $row->exam_date?->format('d M, Y') ?? '—' }}</td>
                                    <td>{{ $row->start_time ? \Carbon\Carbon::parse($row->start_time)->format('h:i A') : '—' }}@if($row->end_time) – {{ \Carbon\Carbon::parse($row->end_time)->format('h:i A') }}@endif</td>
                                    <td>{{ $row->room ?? '—' }}</td>
                                    <td>
                                        <div class="row-actions">
                                            <button class="btn-ghost" style="padding:5px 10px;font-size:12px;" wire:click="editSubjectRow('{{ $row->id }}')">সম্পাদনা</button>
                                            <button class="btn-ghost" style="padding:5px 10px;font-size:12px;" wire:click="deleteSubjectRow('{{ $row->id }}')" wire:confirm="মুছে ফেলতে চান?">মুছুন</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" style="text-align:center;color:var(--ink-soft);padding:30px 0;">এখনো কোনো বিষয় যোগ করা হয়নি</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="picker-card" style="text-align:center;color:var(--ink-soft);padding:40px 20px;">সময়সূচি দেখতে/তৈরি করতে বাম পাশ থেকে একটা পরীক্ষা নির্বাচন করুন</div>
            @endif
        </div>
    </div>

    @if ($showExamModal)
        <div class="modal-overlay" wire:click.self="$set('showExamModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>{{ $editingExamId ? 'পরীক্ষা সম্পাদনা' : 'নতুন পরীক্ষা' }}</h3>
                    <button class="modal-close" wire:click="$set('showExamModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="field">
                    <label>পরীক্ষার নাম <span class="req">*</span></label>
                    <input type="text" wire:model="name" placeholder="যেমনঃ প্রথম সাময়িক পরীক্ষা">
                    @error('name') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    <div class="preset-chips">
                        <span class="lbl">দ্রুত যোগ:</span>
                        <button type="button" class="preset-chip" wire:click="$set('name', 'প্রথম সাময়িক পরীক্ষা')">প্রথম সাময়িক পরীক্ষা</button>
                        <button type="button" class="preset-chip" wire:click="$set('name', 'দ্বিতীয় সাময়িক পরীক্ষা')">দ্বিতীয় সাময়িক পরীক্ষা</button>
                        <button type="button" class="preset-chip" wire:click="$set('name', 'অর্ধ-বার্ষিক পরীক্ষা')">অর্ধ-বার্ষিক পরীক্ষা</button>
                        <button type="button" class="preset-chip" wire:click="$set('name', 'বার্ষিক পরীক্ষা')">বার্ষিক পরীক্ষা</button>
                        <button type="button" class="preset-chip" wire:click="$set('name', 'মাসিক মূল্যায়ন পরীক্ষা')">মাসিক মূল্যায়ন পরীক্ষা</button>
                        <button type="button" class="preset-chip" wire:click="$set('name', 'সাপ্তাহিক পরীক্ষা')">সাপ্তাহিক পরীক্ষা</button>
                        <button type="button" class="preset-chip" wire:click="$set('name', 'নির্বাচনী পরীক্ষা')">নির্বাচনী পরীক্ষা</button>
                        <button type="button" class="preset-chip" wire:click="$set('name', 'মডেল টেস্ট')">মডেল টেস্ট</button>
                    </div>
                </div>
                <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="field">
                        <label>পরীক্ষার ধরন</label>
                        <select wire:model="examType">
                            <option value="term">সাময়িক পরীক্ষা</option>
                            <option value="final">বার্ষিক/ফাইনাল</option>
                            <option value="class_test">ক্লাস টেস্ট</option>
                            <option value="model_test">মডেল টেস্ট</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>শিক্ষাবর্ষ</label>
                        <input type="text" wire:model="academicYear">
                    </div>
                </div>
                <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="field"><label>শুরুর তারিখ</label><input type="date" wire:model="startDate"></div>
                    <div class="field"><label>শেষের তারিখ</label><input type="date" wire:model="endDate"></div>
                </div>
                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showExamModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="saveExam" type="button">সংরক্ষণ করুন</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showSubjectModal)
        <div class="modal-overlay" wire:click.self="$set('showSubjectModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>বিষয় নির্ধারণ</h3>
                    <button class="modal-close" wire:click="$set('showSubjectModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="field">
                        <label>বিষয় <span class="req">*</span></label>
                        <select wire:model="subjectId">
                            <option value="">নির্বাচন করুন</option>
                            @foreach ($subjects as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>শ্রেণি <span class="req">*</span></label>
                        <select wire:model="classId">
                            <option value="">নির্বাচন করুন</option>
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->full_label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="field">
                    <label>দায়িত্বপ্রাপ্ত শিক্ষক</label>
                    <select wire:model="teacherId">
                        <option value="">নির্বাচন করুন</option>
                        @foreach ($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="field"><label>পূর্ণমান</label><input type="number" wire:model="fullMarks"></div>
                    <div class="field"><label>পাশ মার্ক</label><input type="number" wire:model="passMarks"></div>
                </div>
                <div class="field"><label>পরীক্ষার তারিখ</label><input type="date" wire:model="examDate"></div>
                <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="field"><label>শুরুর সময়</label><input type="time" wire:model="startTime"></div>
                    <div class="field"><label>শেষের সময়</label><input type="time" wire:model="endTime"></div>
                </div>
                <div class="field"><label>কক্ষ নং</label><input type="text" wire:model="room"></div>
                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showSubjectModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="saveSubjectRow" type="button">সংরক্ষণ করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>
