<div class="wizard">
    <div class="steps-card">
        <div class="progress-track"><div class="progress-fill" style="width:{{ ($currentStep / $totalSteps) * 100 }}%"></div></div>

        @foreach ([
            1 => ['t1' => 'ব্যক্তিগত তথ্য', 't2' => 'নাম, জন্ম তারিখ, ঠিকানা'],
            2 => ['t1' => 'শিক্ষাগত ও পেশাগত', 't2' => 'যোগ্যতা, অভিজ্ঞতা'],
            3 => ['t1' => 'বিষয় ও ক্লাস', 't2' => 'দায়িত্ব নির্ধারণ'],
            4 => ['t1' => 'বেতন ও ব্যাংক', 't2' => 'পে-রোল তথ্য'],
            5 => ['t1' => 'পোর্টাল অ্যাক্সেস', 't2' => 'সম্পন্ন'],
        ] as $num => $s)
            <div class="step-item {{ $currentStep === $num ? 'active' : ($currentStep > $num ? 'done' : '') }}" wire:click="goToStep({{ $num }})">
                <div class="step-num">{{ $num }}</div>
                <div class="step-text"><div class="t1">{{ $s['t1'] }}</div><div class="t2">{{ $s['t2'] }}</div></div>
            </div>
        @endforeach
    </div>

    <div class="form-card">

        {{-- STEP 1 --}}
        @if ($currentStep === 1)
            <div class="pane-head"><h2>ব্যক্তিগত তথ্য</h2><p>শিক্ষক/স্টাফের মৌলিক পরিচিতি পূরণ করুন।</p></div>

            <div class="photo-row">
                <div class="photo-upload">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="6" width="18" height="14" rx="2.5"/><circle cx="12" cy="13" r="3.5"/><path d="M8 6l1.2-2.2h5.6L16 6"/></svg>
                    <span>ছবি আপলোড</span>
                </div>
                <div class="p-info">পাসপোর্ট সাইজ রঙিন ছবি আপলোড করুন<br>ফরম্যাট: JPG/PNG, সর্বোচ্চ ২ MB (পরে যোগ হবে)</div>
            </div>

            <div class="grid2">
                <div class="field"><label>পূর্ণ নাম (বাংলায়) <span class="req">*</span></label>
                    <input type="text" wire:model="name" placeholder="যেমন: মোছা. সালমা খাতুন">
                    @error('name') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="field"><label>পূর্ণ নাম (ইংরেজিতে) <span class="opt">(ঐচ্ছিক)</span></label>
                    <input type="text" wire:model="name_en" placeholder="e.g. Salma Khatun">
                </div>
                <div class="field"><label>জন্ম তারিখ <span class="req">*</span></label>
                    <input type="date" wire:model="date_of_birth">
                    @error('date_of_birth') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="field"><label>লিঙ্গ <span class="req">*</span></label>
                    <select wire:model="gender">
                        <option value="">নির্বাচন করুন</option>
                        <option value="male">পুরুষ</option>
                        <option value="female">মহিলা</option>
                        <option value="other">অন্যান্য</option>
                    </select>
                    @error('gender') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="field"><label>জাতীয় পরিচয়পত্র (NID) <span class="opt">(ঐচ্ছিক)</span></label>
                    <input type="text" wire:model="nid" placeholder="১০/১৭ ডিজিটের NID নম্বর">
                </div>
                <div class="field"><label>মোবাইল নম্বর <span class="req">*</span></label>
                    <input type="tel" wire:model="phone" placeholder="০১৭XXXXXXXX">
                    @error('phone') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="field"><label>ইমেইল <span class="opt">(পোর্টাল লগইনের জন্য দরকার)</span></label>
                    <input type="email" wire:model="email" placeholder="teacher@example.com">
                </div>
                <div class="field"><label>জরুরী যোগাযোগ নম্বর <span class="opt">(ঐচ্ছিক)</span></label>
                    <input type="tel" wire:model="emergency_contact" placeholder="নিকটাত্মীয়ের মোবাইল নম্বর">
                </div>
            </div>

            <div class="field full">
                <label>বর্তমান ঠিকানা <span class="opt">(ঐচ্ছিক)</span></label>
                <textarea wire:model="address" placeholder="গ্রাম/মহল্লা, ডাকঘর, উপজেলা, জেলা"></textarea>
            </div>
        @endif

        {{-- STEP 2 --}}
        @if ($currentStep === 2)
            <div class="pane-head"><h2>শিক্ষাগত ও পেশাগত তথ্য</h2><p>যোগ্যতা, পদবি ও অভিজ্ঞতা সংক্রান্ত তথ্য দিন।</p></div>

            <div class="grid2">
                <div class="field"><label>সর্বোচ্চ শিক্ষাগত যোগ্যতা <span class="opt">(ঐচ্ছিক)</span></label>
                    <input type="text" wire:model="education" placeholder="যেমন: এম.এসসি (গণিত)">
                </div>
                <div class="field"><label>পাসের প্রতিষ্ঠান <span class="opt">(ঐচ্ছিক)</span></label>
                    <input type="text" wire:model="passing_institution" placeholder="বিশ্ববিদ্যালয়/কলেজের নাম">
                </div>
                <div class="field"><label>পদবি <span class="req">*</span></label>
                    <select wire:model="designation">
                        <option value="">নির্বাচন করুন</option>
                        @foreach (['প্রধান শিক্ষক','সহকারী প্রধান শিক্ষক','সিনিয়র শিক্ষক','সহকারী শিক্ষক','অফিস স্টাফ'] as $d)
                            <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                    @error('designation') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="field"><label>কর্মচারীর ধরন</label>
                    <select wire:model="employee_type">
                        <option value="permanent">স্থায়ী</option>
                        <option value="temporary">অস্থায়ী</option>
                        <option value="parttime">খণ্ডকালীন</option>
                    </select>
                </div>
                <div class="field"><label>শিক্ষকতার অভিজ্ঞতা (বছর) <span class="opt">(ঐচ্ছিক)</span></label>
                    <input type="number" wire:model="experience_years" min="0" placeholder="যেমন: ৫">
                </div>
                <div class="field"><label>যোগদানের তারিখ <span class="req">*</span></label>
                    <input type="date" wire:model="joining_date">
                    @error('joining_date') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="field full">
                <label>পূর্ববর্তী কর্মস্থল <span class="opt">(ঐচ্ছিক)</span></label>
                <input type="text" wire:model="previous_workplace" placeholder="প্রতিষ্ঠানের নাম ও পদবি">
            </div>
        @endif

        {{-- STEP 3 --}}
        @if ($currentStep === 3)
            <div class="pane-head"><h2>বিষয় ও ক্লাস নির্ধারণ</h2><p>এই শিক্ষক কোন বিষয় ও ক্লাস পড়াবেন তা নির্বাচন করুন — এটা তথ্যগত, প্রকৃত রুটিন পরে "ক্লাস রুটিন" থেকে বসাবেন।</p></div>

            <div class="field-title">পাঠদানের বিষয় (একাধিক নির্বাচন করা যাবে)</div>
            <div class="check-grid">
                @foreach ($subjects as $subject)
                    <label class="check-pill">
                        <input type="checkbox" wire:model="subjects_taught" value="{{ $subject->id }}">
                        {{ $subject->name }}
                    </label>
                @endforeach
                @if ($subjects->isEmpty())
                    <p class="hint">এখনো কোনো বিষয় যোগ করা হয়নি — আগে "বিষয় ও সিলেবাস" থেকে বিষয় যোগ করুন।</p>
                @endif
            </div>

            <div class="field-title" style="margin-top:18px;">নির্ধারিত ক্লাস (একাধিক নির্বাচন করা যাবে)</div>
            <div class="check-grid">
                @foreach ($classes as $class)
                    <label class="check-pill">
                        <input type="checkbox" wire:model="assigned_classes" value="{{ $class->id }}">
                        {{ $class->full_label }}
                    </label>
                @endforeach
                @if ($classes->isEmpty())
                    <p class="hint">এখনো কোনো ক্লাস যোগ করা হয়নি।</p>
                @endif
            </div>
        @endif

        {{-- STEP 4 --}}
        @if ($currentStep === 4)
            <div class="pane-head"><h2>বেতন ও ব্যাংক তথ্য</h2><p>পে-রোল প্রক্রিয়াকরণের জন্য প্রয়োজনীয় তথ্য দিন — সব ঐচ্ছিক, পরে সেট করতে পারবেন।</p></div>

            <div class="grid3">
                <div class="field"><label>মূল বেতন (মাসিক)</label><input type="number" wire:model="base_salary" placeholder="৳ পরিমাণ"></div>
                <div class="field"><label>বাড়ি ভাড়া ভাতা</label><input type="number" wire:model="house_rent" placeholder="৳ পরিমাণ"></div>
                <div class="field"><label>চিকিৎসা/অন্যান্য ভাতা</label><input type="number" wire:model="medical_allowance" placeholder="৳ পরিমাণ"></div>
            </div>
            <div class="grid2">
                <div class="field"><label>ব্যাংকের নাম</label><input type="text" wire:model="bank_name" placeholder="যেমন: ইসলামী ব্যাংক বাংলাদেশ"></div>
                <div class="field"><label>শাখা</label><input type="text" wire:model="bank_branch" placeholder="শাখার নাম"></div>
                <div class="field"><label>হিসাব নম্বর</label><input type="text" wire:model="bank_account" placeholder="ব্যাংক অ্যাকাউন্ট নম্বর"></div>
                <div class="field"><label>মোবাইল ব্যাংকিং (বিকাশ/নগদ)</label><input type="tel" wire:model="mobile_banking" placeholder="০১৭XXXXXXXX"></div>
            </div>
        @endif

        {{-- STEP 5: RESULT --}}
        @if ($currentStep === 5)
            <div class="pane-head"><h2>নিয়োগ সম্পন্ন হয়েছে</h2><p>শিক্ষকের তথ্য সফলভাবে সংরক্ষণ করা হয়েছে।</p></div>

            @if ($generatedStaffId)
                <div class="id-box">
                    <div>
                        <div class="lbl">স্টাফ আইডি</div>
                        <div class="val">{{ $generatedStaffId }}</div>
                    </div>
                </div>

                @if ($email)
                    <div class="info-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
                        শিক্ষক পোর্টাল একাউন্ট তৈরি হয়েছে ({{ $email }}) — পাসওয়ার্ড রিসেট করে জানিয়ে দিন।
                    </div>
                @else
                    <div class="info-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
                        ইমেইল দেওয়া হয়নি বলে পোর্টাল একাউন্ট তৈরি হয়নি — পরে সম্পাদনা করে ইমেইল যোগ করলে একাউন্ট তৈরি করা যাবে।
                    </div>
                @endif

                <div style="margin-top:20px;">
                    <a href="{{ route('teachers.index') }}" class="btn-primary">শিক্ষক তালিকায় ফিরে যান</a>
                </div>
            @endif
        @endif

        {{-- FOOTER --}}
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
                        নিয়োগ নিশ্চিত করুন
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