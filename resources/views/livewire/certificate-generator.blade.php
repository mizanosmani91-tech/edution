<div class="lifecycle-page">
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">শিক্ষার্থী / {{ $type === 'character' ? 'চারিত্রিক সনদপত্র' : 'ছাড়পত্র' }}</div>
            <h2>{{ $type === 'character' ? 'চারিত্রিক সনদপত্র (Character Certificate)' : 'ছাড়পত্র (Transfer Certificate)' }}</h2>
        </div>
    </div>

    <div class="lc-grid">
        <aside class="picker-card">
            <div class="picker-filters">
                <select wire:model.live="classId">
                    <option value="">সকল শ্রেণি</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->full_label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="picker-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="নাম বা আইডি লিখুন…">
            </div>
            <div class="stud-list">
                @forelse ($students as $student)
                    <div class="stud-row {{ $selectedStudentId === $student->id ? 'selected-preview' : '' }}" wire:click="selectStudent('{{ $student->id }}')" wire:key="pick-{{ $student->id }}">
                        <div class="ini">{{ mb_substr($student->name, 0, 1) }}</div>
                        <div class="info">
                            <div class="nm">{{ $student->name }}</div>
                            <div class="ds">{{ $student->student_id_no }}</div>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center;color:var(--ink-soft);padding:20px 0;font-size:13px;">কোনো শিক্ষার্থী পাওয়া যায়নি</div>
                @endforelse
            </div>
        </aside>

        <div>
            @if ($selected)
                @if (! $generated)
                    <div class="cert-form-card">
                        @if ($type === 'transfer')
                            <div class="field">
                                <label>স্থানান্তরের কারণ <span class="req">*</span></label>
                                <input type="text" wire:model="reason" placeholder="যেমনঃ অভিভাবকের বদলি, পারিবারিক কারণ ইত্যাদি">
                                @error('reason') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                            </div>
                        @endif
                        <div class="field">
                            <label>অতিরিক্ত মন্তব্য (ঐচ্ছিক)</label>
                            <input type="text" wire:model="remarks">
                        </div>
                        <button class="btn-primary" wire:click="generate" type="button">সনদ তৈরি করুন</button>
                    </div>
                @else
                    <div class="action-row" style="margin-bottom:12px;">
                        <button type="button" class="btn-primary" onclick="window.print()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V3h12v6"/><rect x="4" y="9" width="16" height="8" rx="1.5"/><path d="M6 17h12v5H6z"/></svg>
                            প্রিন্ট / PDF হিসেবে সেভ করুন
                        </button>
                        <button type="button" class="btn-ghost" wire:click="$set('generatedId', null)">নতুন সনদ</button>
                    </div>

                    <div class="cert-sheet">
                        <div class="cert-no">নম্বরঃ {{ $generated->certificate_no }}</div>
                        <div class="cert-head">
                            <h1>{{ auth()->user()->institution?->name ?? 'বিদ্যাপঞ্জি' }}</h1>
                            <p>পরিচালিত হয় বিদ্যাপঞ্জি এডুকেশন ম্যানেজমেন্ট সিস্টেমের মাধ্যমে</p>
                        </div>
                        <div class="cert-title">{{ $type === 'character' ? 'চারিত্রিক সনদপত্র' : 'ছাড়পত্র (Transfer Certificate)' }}</div>
                        <div class="cert-body">
                            @if ($type === 'character')
                                <p>এই মর্মে প্রত্যয়ন করা যাচ্ছে যে, <b>{{ $generated->student->name }}</b> (আইডি নং: {{ $generated->student->student_id_no }}), পিতা/মাতার মাধ্যমে ভর্তিকৃত, {{ $generated->student->schoolClass?->full_label ?? '—' }}@if($generated->student->section) শ্রেণির {{ $generated->student->section->name }} শাখার@endif একজন শিক্ষার্থী। প্রতিষ্ঠানে অধ্যয়নকালীন তার আচার-আচরণ ও চরিত্র সন্তোষজনক ছিল।</p>
                                @if ($generated->remarks)
                                    <p>মন্তব্যঃ {{ $generated->remarks }}</p>
                                @endif
                                <p>আমরা তার ভবিষ্যৎ জীবনের সর্বাঙ্গীণ মঙ্গল কামনা করছি।</p>
                            @else
                                <p>এই মর্মে প্রত্যয়ন করা যাচ্ছে যে, <b>{{ $generated->student->name }}</b> (আইডি নং: {{ $generated->student->student_id_no }}) {{ $generated->student->schoolClass?->full_label ?? '—' }}@if($generated->student->section) শ্রেণির {{ $generated->student->section->name }} শাখার@endif একজন শিক্ষার্থী ছিল। নিম্নোক্ত কারণে তাকে এই ছাড়পত্র প্রদান করা হলোঃ</p>
                                <p><b>কারণঃ</b> {{ $generated->reason }}</p>
                                @if ($generated->remarks)
                                    <p>মন্তব্যঃ {{ $generated->remarks }}</p>
                                @endif
                                <p>প্রতিষ্ঠান ত্যাগের তারিখঃ {{ $generated->issue_date->format('d M, Y') }}</p>
                            @endif
                        </div>
                        <div class="cert-sign-row">
                            <div>শ্রেণি শিক্ষকের স্বাক্ষর</div>
                            <div>প্রধান শিক্ষক/অধ্যক্ষের স্বাক্ষর</div>
                        </div>
                    </div>
                @endif
            @else
                <div class="picker-card" style="text-align:center;color:var(--ink-soft);padding:40px 20px;">সনদ তৈরি করতে বাম পাশ থেকে একজন শিক্ষার্থী নির্বাচন করুন</div>
            @endif

            <div class="history-card">
                <h3>সাম্প্রতিক ইস্যুকৃত সনদ</h3>
                <table>
                    <thead><tr><th>নম্বর</th><th>শিক্ষার্থী</th><th>তারিখ</th></tr></thead>
                    <tbody>
                        @forelse ($history as $h)
                            <tr wire:key="hist-{{ $h->id }}"><td>{{ $h->certificate_no }}</td><td>{{ $h->student->name ?? '—' }}</td><td>{{ $h->issue_date->format('d M, Y') }}</td></tr>
                        @empty
                            <tr><td colspan="3" style="text-align:center;color:var(--ink-soft);padding:16px 0;">এখনো কোনো সনদ ইস্যু করা হয়নি</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
