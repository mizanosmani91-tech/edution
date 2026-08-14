<div class="idcard-page" x-data="{ showBlood: true, showQr: true, showPhone: true, showEn: true, theme: '#5C1A2B', themeDeep: '#3E1120' }" :style="'--theme:'+theme+';--theme-deep:'+themeDeep">
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">শিক্ষার্থী / আইডি কার্ড</div>
            <h2>আইডি কার্ড জেনারেশন</h2>
            <p>শিক্ষার্থী নির্বাচন করলেই কার্ডের প্রিভিউ সাথে সাথে হালনাগাদ হবে</p>
        </div>
    </div>

    <div class="idc-grid">
        {{-- LEFT: picker --}}
        <aside class="picker-card">
            <div class="picker-filters">
                <select wire:model.live="classId">
                    <option value="">সকল শ্রেণি</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->full_label }}</option>
                    @endforeach
                </select>
                <select wire:model.live="sectionId" @if(!$classId) disabled @endif>
                    <option value="">সকল শাখা</option>
                    @foreach ($sections as $section)
                        <option value="{{ $section->id }}">{{ $section->name }}</option>
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
                            <div class="ds">{{ $student->schoolClass?->full_label ?? '—' }}@if($student->section), {{ $student->section->name }}@endif — {{ $student->student_id_no }}</div>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center;color:var(--ink-soft);padding:20px 0;font-size:13px;">কোনো শিক্ষার্থী পাওয়া যায়নি</div>
                @endforelse
            </div>
        </aside>

        {{-- RIGHT: preview + customize --}}
        <div>
            @if ($selected)
                <div class="preview-wrap">
                    {{-- FRONT --}}
                    <div class="id-card">
                        <div class="idc-top">
                            <div class="idc-school">
                                <div class="idc-school-ic"><svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.6"><path d="M4 6.5c2.8-1.4 5.6-1.4 8 0v11c-2.4-1.4-5.2-1.4-8 0v-11Z"/><path d="M20 6.5c-2.8-1.4-5.6-1.4-8 0v11c2.4-1.4 5.2-1.4 8 0v-11Z"/></svg></div>
                                <div><div class="nm">{{ auth()->user()->institution?->name ?? 'EDUTION' }}</div><div class="tag">STUDENT IDENTITY CARD</div></div>
                            </div>
                        </div>
                        <div class="idc-photo-wrap">
                            <div class="idc-photo">
                                @if ($selected->photo_path)
                                    <img src="{{ asset('storage/'.$selected->photo_path) }}" alt="{{ $selected->name }}">
                                @else
                                    {{ mb_substr($selected->name, 0, 1) }}
                                @endif
                            </div>
                        </div>
                        <div class="idc-body">
                            <div class="idc-name">{{ $selected->name }}</div>
                            <div class="idc-name-en" x-show="showEn">{{ $selected->name_en ?? '' }}</div>
                            <div class="idc-role-badge">{{ $selected->schoolClass?->full_label ?? '—' }}@if($selected->section), শাখা {{ $selected->section->name }}@endif</div>
                        </div>
                        <div class="idc-info">
                            <div class="row"><span class="k">শিক্ষার্থী আইডি</span><span class="v">{{ $selected->student_id_no ?? '—' }}</span></div>
                            <div class="row" x-show="showBlood"><span class="k">রক্তের গ্রুপ</span><span class="v">{{ $selected->blood_group ?? '—' }}</span></div>
                            <div class="row" x-show="showPhone"><span class="k">অভিভাবকের মোবাইল</span><span class="v">{{ $selected->guardian_phone ?? '—' }}</span></div>
                        </div>
                        <div class="idc-footer">
                            <span class="validity">মেয়াদ: {{ now()->addYear()->translatedFormat('F, Y') }}</span>
                            <span class="id-code">EDUTION</span>
                        </div>
                    </div>

                    {{-- BACK --}}
                    <div class="id-card back">
                        <div class="idc-back-inner">
                            <div class="idc-back-title">{{ auth()->user()->institution?->name ?? 'EDUTION' }}</div>
                            <div class="idc-back-rows">
                                <div class="row"><span class="k">জন্ম তারিখ</span><span class="v">{{ $selected->date_of_birth ? \Carbon\Carbon::parse($selected->date_of_birth)->format('d M, Y') : '—' }}</span></div>
                                <div class="row"><span class="k">রক্তের গ্রুপ</span><span class="v">{{ $selected->blood_group ?? '—' }}</span></div>
                                <div class="row"><span class="k">জরুরি যোগাযোগ</span><span class="v">{{ $selected->guardian_phone ?? '—' }}</span></div>
                            </div>
                            <div class="idc-qr" x-show="showQr">
                                <svg viewBox="0 0 100 100"><rect width="100" height="100" fill="#fff"/>
                                    <g fill="#2A2320">
                                        <rect x="6" y="6" width="24" height="24"/><rect x="12" y="12" width="12" height="12" fill="#fff"/>
                                        <rect x="70" y="6" width="24" height="24"/><rect x="76" y="12" width="12" height="12" fill="#fff"/>
                                        <rect x="6" y="70" width="24" height="24"/><rect x="12" y="76" width="12" height="12" fill="#fff"/>
                                        <rect x="40" y="6" width="6" height="6"/><rect x="50" y="6" width="6" height="6"/><rect x="40" y="16" width="6" height="6"/>
                                        <rect x="60" y="40" width="6" height="6"/><rect x="70" y="40" width="6" height="6"/><rect x="80" y="46" width="6" height="6"/>
                                        <rect x="40" y="40" width="6" height="6"/><rect x="46" y="50" width="6" height="6"/><rect x="40" y="60" width="6" height="6"/>
                                        <rect x="60" y="70" width="6" height="6"/><rect x="70" y="80" width="6" height="6"/><rect x="82" y="70" width="6" height="6"/>
                                        <rect x="52" y="82" width="6" height="6"/><rect x="62" y="88" width="6" height="6"/>
                                    </g>
                                </svg>
                            </div>
                            <div class="idc-sign"><div class="line"></div><div class="lbl">প্রিন্সিপালের স্বাক্ষর</div></div>
                            <div class="idc-back-note">হারিয়ে গেলে অনুগ্রহ করে বিদ্যালয় অফিসে যোগাযোগ করুন। এই কার্ড শুধুমাত্র শিক্ষার্থী পরিচয় যাচাইয়ের জন্য বৈধ।</div>
                        </div>
                    </div>
                </div>

                <div class="customize-card">
                    <h3>কাস্টমাইজ করুন</h3>
                    <div class="cust-grid">
                        <div class="cust-item"><span class="lbl">রক্তের গ্রুপ দেখান</span><label class="switch"><input type="checkbox" x-model="showBlood"><span class="switch-track"></span></label></div>
                        <div class="cust-item"><span class="lbl">QR কোড দেখান</span><label class="switch"><input type="checkbox" x-model="showQr"><span class="switch-track"></span></label></div>
                        <div class="cust-item"><span class="lbl">অভিভাবকের নম্বর দেখান</span><label class="switch"><input type="checkbox" x-model="showPhone"><span class="switch-track"></span></label></div>
                        <div class="cust-item"><span class="lbl">ইংরেজি নাম দেখান</span><label class="switch"><input type="checkbox" x-model="showEn"><span class="switch-track"></span></label></div>
                    </div>
                    <div class="color-row">
                        <span class="lbl">থিম রঙ:</span>
                        <div class="color-dot" :class="{active: theme==='#5C1A2B'}" style="background:#5C1A2B" @click="theme='#5C1A2B';themeDeep='#3E1120'"></div>
                        <div class="color-dot" :class="{active: theme==='#35528F'}" style="background:#35528F" @click="theme='#35528F';themeDeep='#1F3459'"></div>
                        <div class="color-dot" :class="{active: theme==='#2F6E52'}" style="background:#2F6E52" @click="theme='#2F6E52';themeDeep='#1B4433'"></div>
                        <div class="color-dot" :class="{active: theme==='#A65A2E'}" style="background:#A65A2E" @click="theme='#A65A2E';themeDeep='#733D1D'"></div>
                    </div>
                </div>

                <div class="action-row">
                    <button type="button" class="btn-primary" onclick="window.print()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V3h12v6"/><rect x="4" y="9" width="16" height="8" rx="1.5"/><path d="M6 17h12v5H6z"/></svg>
                        প্রিন্ট / PDF হিসেবে সেভ করুন
                    </button>
                </div>
                <p style="font-size:12px;color:var(--ink-soft);margin-top:8px;">একসাথে অনেকগুলো কার্ড বাল্ক জেনারেট করার ফিচার শীঘ্রই যুক্ত হবে — আপাতত একটা একটা করে প্রিন্ট/PDF করা যাবে।</p>
            @else
                <div class="picker-card" style="text-align:center;color:var(--ink-soft);padding:40px 20px;">প্রিভিউ দেখতে বাম পাশ থেকে একজন শিক্ষার্থী নির্বাচন করুন</div>
            @endif
        </div>
    </div>
</div>
