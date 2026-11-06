<div>
    <div class="page-head">
        <div>
            <h2>শিক্ষার্থীর তথ্য সম্পাদনা</h2>
            <p>{{ $student->name }} · আইডি: {{ $student->student_id_no }}</p>
        </div>
        <a href="{{ route('students.profile', $student) }}" class="btn-ghost">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M11 5 4 12l7 7M4 12h16"/></svg>
            প্রোফাইলে ফিরুন
        </a>
    </div>

    @if ($saved)
        <div class="info-box" style="background:rgba(16,185,129,.1);border-color:rgba(16,185,129,.3);color:var(--good);">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20 6 9 17l-5-5"/></svg>
            তথ্য সফলভাবে সংরক্ষণ করা হয়েছে।
        </div>
    @endif

    <div class="settings-section">
        <h3>ব্যক্তিগত তথ্য</h3>
        <div class="grid2">
            <div class="field">
                <label>নাম <span class="req">*</span></label>
                <input type="text" wire:model="name">
                @error('name') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label>নাম (ইংরেজি)</label>
                <input type="text" wire:model="name_en">
            </div>
            <div class="field">
                <label>জন্মতারিখ</label>
                <input type="date" wire:model="date_of_birth">
                @error('date_of_birth') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label>লিঙ্গ</label>
                <select wire:model="gender">
                    <option value="">নির্বাচন করুন</option>
                    <option value="male">ছেলে</option>
                    <option value="female">মেয়ে</option>
                    <option value="other">অন্যান্য</option>
                </select>
            </div>
            <div class="field">
                <label>জন্মনিবন্ধন নম্বর</label>
                <input type="text" wire:model="birth_reg_no">
            </div>
            <div class="field">
                <label>রক্তের গ্রুপ</label>
                <input type="text" wire:model="blood_group" placeholder="যেমন: A+">
            </div>
            <div class="field">
                <label>ধর্ম</label>
                <input type="text" wire:model="religion">
            </div>
            <div class="field">
                <label>জাতীয়তা</label>
                <input type="text" wire:model="nationality">
            </div>
        </div>
    </div>

    <div class="settings-section">
        <h3>একাডেমিক তথ্য</h3>
        <div class="grid2">
            <div class="field">
                <label>শ্রেণি <span class="req">*</span></label>
                <select wire:model.live="class_id">
                    <option value="">নির্বাচন করুন</option>
                    @foreach ($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->full_label }}</option>
                    @endforeach
                </select>
                @error('class_id') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label>সেকশন</label>
                <select wire:model="section_id">
                    <option value="">নেই</option>
                    @foreach ($sections as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
                @error('section_id') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label>ভর্তির ধরন</label>
                <select wire:model="admission_type">
                    <option value="new">নতুন ভর্তি</option>
                    <option value="transfer">স্থানান্তর</option>
                </select>
            </div>
            <div class="field">
                <label>আগের স্কুল</label>
                <input type="text" wire:model="previous_school">
            </div>
        </div>
    </div>

    <div class="settings-section">
        <h3>অভিভাবক ও অবস্থা</h3>
        <div class="grid2">
            <div class="field">
                <label>অভিভাবকের ফোন</label>
                <input type="text" wire:model="guardian_phone">
                @error('guardian_phone') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label>স্ট্যাটাস</label>
                <select wire:model="status">
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <p class="hint">রেকর্ড মোছা হয় না; ফি, নম্বর ও হাজিরার ইতিহাস থেকে যায়।</p>
                @error('status') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:8px;">
        <a href="{{ route('students.profile', $student) }}" class="btn-ghost">বাতিল</a>
        <button class="btn-primary" type="button" wire:click="save">সংরক্ষণ করুন</button>
    </div>
</div>
