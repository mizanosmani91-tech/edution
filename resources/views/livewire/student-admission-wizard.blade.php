<div class="wizard" x-data="{ step: {{ $currentStep }} }" x-effect="step = {{ $currentStep }}">
    {{-- STEP NAV --}}
    <div class="steps-card">
        <div class="progress-track"><div class="progress-fill" style="width:{{ ($currentStep / $totalSteps) * 100 }}%"></div></div>

        @foreach ([
            1 => ['t1' => 'শিক্ষার্থীর তথ্য', 't2' => 'নাম, জন্ম তারিখ'],
            2 => ['t1' => 'একাডেমিক তথ্য', 't2' => 'শ্রেণি, শাখা'],
            3 => ['t1' => 'অভিভাবকের তথ্য', 't2' => 'নাম, ফোন, ঠিকানা'],
            4 => ['t1' => 'আবাসন ও খাবার', 't2' => 'হোস্টেল, মিল প্ল্যান'],
            5 => ['t1' => 'সম্পন্ন', 't2' => 'ভর্তি নিশ্চিতকরণ'],
        ] as $num => $s)
            <div class="step-item {{ $currentStep === $num ? 'active' : ($currentStep > $num ? 'done' : '') }}" wire:click="goToStep({{ $num }})">
                <div class="step-num">{{ $num }}</div>
                <div class="step-text"><div class="t1">{{ $s['t1'] }}</div><div class="t2">{{ $s['t2'] }}</div></div>
            </div>
        @endforeach
    </div>

    {{-- FORM --}}
    <div class="form-card">

        {{-- STEP 1 --}}
        @if ($currentStep === 1)
            <div class="pane-head"><h2>শিক্ষার্থীর ব্যক্তিগত তথ্য</h2><p>শিক্ষার্থীর মৌলিক পরিচিতি সঠিকভাবে পূরণ করুন।</p></div>

            <div class="photo-row">
                <div class="photo-upload">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="6" width="18" height="14" rx="2.5"/><circle cx="12" cy="13" r="3.5"/><path d="M8 6l1.2-2.2h5.6L16 6"/></svg>
                    <span>ছবি আপলোড</span>
                </div>
                <div class="p-info">পাসপোর্ট সাইজ রঙিন ছবি আপলোড করুন<br>ফরম্যাট: JPG/PNG, সর্বোচ্চ ২ MB (পরে যোগ হবে)</div>
            </div>

            <div class="grid2">
                <div class="field"><label>পূর্ণ নাম (বাংলায়) <span class="req">*</span></label>
                    <input type="text" wire:model="name" placeholder="যেমন: তানভীর আহমেদ">
                    @error('name') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="field"><label>পূর্ণ নাম (ইংরেজিতে) <span class="opt">(ঐচ্ছিক)</span></label>
                    <input type="text" wire:model="name_en" placeholder="e.g. Tanvir Ahmed">
                </div>
                <div class="field"><label>জন্ম তারিখ <span class="req">*</span></label>
                    <input type="date" wire:model="date_of_birth">
                    @error('date_of_birth') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="field"><label>লিঙ্গ <span class="req">*</span></label>
                    <select wire:model="gender">
                        <option value="">নির্বাচন করুন</option>
                        <option value="male">ছেলে</option>
                        <option value="female">মেয়ে</option>
                        <option value="other">অন্যান্য</option>
                    </select>
                    @error('gender') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="field"><label>জন্ম নিবন্ধন নম্বর <span class="opt">(ঐচ্ছিক)</span></label>
                    <input type="text" wire:model="birth_reg_no" placeholder="১৭ ডিজিটের নম্বর">
                </div>
                <div class="field"><label>রক্তের গ্রুপ <span class="opt">(ঐচ্ছিক)</span></label>
                    <select wire:model="blood_group">
                        <option value="">জানা নেই</option>
                        @foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                            <option value="{{ $bg }}">{{ $bg }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field"><label>ধর্ম</label>
                    <select wire:model="religion">
                        @foreach (['ইসলাম','হিন্দু','খ্রিস্টান','বৌদ্ধ','অন্যান্য'] as $r)
                            <option value="{{ $r }}">{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field"><label>জাতীয়তা</label><input type="text" wire:model="nationality"></div>
            </div>
        @endif

        {{-- STEP 2 --}}
        @if ($currentStep === 2)
            <div class="pane-head"><h2>একাডেমিক তথ্য</h2><p>ভর্তির শ্রেণি ও শিক্ষাবর্ষ সংক্রান্ত তথ্য দিন।</p></div>

            <div class="grid3">
                <div class="field"><label>ভর্তির ধরন</label>
                    <select wire:model="admission_type">
                        <option value="new">নতুন ভর্তি</option>
                        <option value="transfer">স্থানান্তর</option>
                    </select>
                </div>
                <div class="field"><label>ভর্তি হবে শ্রেণি <span class="req">*</span></label>
                    <select wire:model.live="class_id">
                        <option value="">নির্বাচন করুন</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->full_label }}</option>
                        @endforeach
                    </select>
                    @error('class_id') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="field"><label>শাখা/সেকশন <span class="opt">(ঐচ্ছিক)</span></label>
                    <select wire:model="section_id">
                        <option value="">নির্বাচন করুন</option>
                        @foreach ($sections as $section)
                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if ($admission_type === 'transfer')
                <div class="field full">
                    <label>পূর্ববর্তী প্রতিষ্ঠানের নাম</label>
                    <input type="text" wire:model="previous_school" placeholder="যে প্রতিষ্ঠান থেকে স্থানান্তরিত হচ্ছে">
                </div>
            @endif
        @endif

        {{-- STEP 3 --}}
        @if ($currentStep === 3)
            <div class="pane-head"><h2>অভিভাবকের তথ্য</h2><p>যোগাযোগের জন্য অভিভাবকের তথ্য দিন — এই নম্বর দিয়েই অভিভাবক পোর্টালে লগইন করবেন।</p></div>

            <div class="grid2">
                <div class="field"><label>অভিভাবকের নাম <span class="req">*</span></label>
                    <input type="text" wire:model="guardian_name" placeholder="যেমন: মো. আব্দুল করিম">
                    @error('guardian_name') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="field"><label>সম্পর্ক</label>
                    <select wire:model="guardian_relation">
                        @foreach (['পিতা','মাতা','অভিভাবক'] as $rel)
                            <option value="{{ $rel }}">{{ $rel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field"><label>মোবাইল নম্বর <span class="req">*</span></label>
                    <input type="text" wire:model="guardian_phone" placeholder="০১৭XXXXXXXX">
                    @error('guardian_phone') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="field full">
                <label>ঠিকানা <span class="opt">(ঐচ্ছিক)</span></label>
                <textarea wire:model="address" placeholder="গ্রাম/মহল্লা, উপজেলা, জেলা"></textarea>
            </div>

            <div class="info-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
                এই মোবাইল নম্বর দিয়ে অভিভাবক একাউন্ট তৈরি/সংযুক্ত হবে — একই নম্বর আগে থেকে থাকলে (ভাই-বোন) নতুন করে অ্যাকাউন্ট তৈরি হবে না।
            </div>
        @endif

        {{-- STEP 4 --}}
        @if ($currentStep === 4)
            <div class="pane-head"><h2>আবাসন ও খাবার</h2><p>এই তথ্য ভবিষ্যতে হোস্টেল মডিউল যুক্ত হলে সক্রিয় হবে।</p></div>

            <div class="switch-row">
                <div class="switch-label"><div class="t1">আবাসিক শিক্ষার্থী</div><div class="t2">হোস্টেলে থাকবে</div></div>
                <label class="switch">
                    <input type="checkbox" wire:model="residential">
                    <span class="switch-track"></span>
                </label>
            </div>

            <div class="switch-row">
                <div class="switch-label"><div class="t1">মিল প্ল্যান</div><div class="t2">খাবারের ব্যবস্থা নেবে</div></div>
                <label class="switch">
                    <input type="checkbox" wire:model="meal">
                    <span class="switch-track"></span>
                </label>
            </div>

            <div class="info-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
                হোস্টেল ও মিল ফি এখনো স্বয়ংক্রিয়ভাবে যুক্ত হয় না — এই তথ্য পরে হোস্টেল মডিউল বানানোর সময় সক্রিয় হবে।
            </div>
        @endif

        {{-- STEP 5: RESULT --}}
        @if ($currentStep === 5)
            <div class="pane-head"><h2>ভর্তি সম্পন্ন হয়েছে</h2><p>শিক্ষার্থীর তথ্য সফলভাবে সংরক্ষণ করা হয়েছে।</p></div>

            @if ($generatedStudentId)
                <div class="id-box">
                    <div>
                        <div class="lbl">স্টুডেন্ট আইডি</div>
                        <div class="val">{{ $generatedStudentId }}</div>
                    </div>
                </div>

                <div class="info-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
                    অভিভাবকের একাউন্ট তৈরি হয়েছে (পাসওয়ার্ড এখনো সেট করা হয়নি) — সেটিংস থেকে অভিভাবকের পাসওয়ার্ড রিসেট করে জানিয়ে দিন।
                </div>

                <div style="margin-top:20px;">
                    <a href="{{ route('students.index') }}" class="btn-primary">শিক্ষার্থী তালিকায় ফিরে যান</a>
                </div>
            @endif
        @endif

        {{-- FOOTER NAVIGATION --}}
        @if ($currentStep < 5)
            <div class="pane-foot">
                @if ($currentStep > 1)
                    <button type="button" class="btn-ghost" wire:click="prevStep">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 19l-7-7 7-7"/></svg>
                        পূর্ববর্তী
                    </button>
                @else
                    <span></span>
                @endif

                <button type="button" class="btn-primary {{ $currentStep === 4 ? 'final' : '' }}" wire:click="nextStep">
                    @if ($currentStep === 4)
                        ভর্তি নিশ্চিত করুন
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>
                    @else
                        পরবর্তী ধাপ
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 5l7 7-7 7"/></svg>
                    @endif
                </button>
            </div>
        @endif
    </div>
</div>