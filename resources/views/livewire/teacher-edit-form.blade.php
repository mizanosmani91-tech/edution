<div>
    <div class="page-head">
        <div>
            <h2>শিক্ষক তথ্য সম্পাদনা</h2>
            <p>{{ $teacher->name }}-এর তথ্য হালনাগাদ করুন</p>
        </div>
        <a href="{{ route('teachers.profile', $teacher) }}" class="btn-ghost">
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
                <label>পূর্ণ নাম <span class="req">*</span></label>
                <input type="text" wire:model="name">
                @error('name') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label>পূর্ণ নাম (ইংরেজি)</label>
                <input type="text" wire:model="name_en">
            </div>
            <div class="field">
                <label>লিঙ্গ</label>
                <select wire:model="gender">
                    <option value="">নির্বাচন করুন</option>
                    <option value="male">পুরুষ</option>
                    <option value="female">মহিলা</option>
                    <option value="other">অন্যান্য</option>
                </select>
            </div>
            <div class="field">
                <label>জাতীয় পরিচয়পত্র (NID)</label>
                <input type="text" wire:model="nid">
            </div>
            <div class="field">
                <label>মোবাইল নম্বর</label>
                <input type="text" wire:model="phone">
            </div>
            <div class="field">
                <label>ইমেইল</label>
                <input type="email" wire:model="email">
                @error('email') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label>জরুরী যোগাযোগ</label>
                <input type="text" wire:model="emergency_contact">
            </div>
            <div class="field">
                <label>স্ট্যাটাস</label>
                <select wire:model="status">
                    <option value="active">সক্রিয়</option>
                    <option value="leave">ছুটিতে</option>
                    <option value="inactive">নিষ্ক্রিয়</option>
                </select>
            </div>
        </div>
        <div class="field full">
            <label>বর্তমান ঠিকানা</label>
            <textarea wire:model="address"></textarea>
        </div>
    </div>

    <div class="settings-section">
        <h3>শিক্ষাগত ও পেশাগত তথ্য</h3>
        <div class="grid2">
            <div class="field">
                <label>শিক্ষাগত যোগ্যতা</label>
                <input type="text" wire:model="education">
            </div>
            <div class="field">
                <label>পাশের প্রতিষ্ঠান</label>
                <input type="text" wire:model="passing_institution">
            </div>
            <div class="field">
                <label>পদবি</label>
                <input type="text" wire:model="designation">
            </div>
            <div class="field">
                <label>কর্মচারীর ধরন</label>
                <select wire:model="employee_type">
                    <option value="permanent">স্থায়ী</option>
                    <option value="contractual">চুক্তিভিত্তিক</option>
                    <option value="parttime">খণ্ডকালীন</option>
                </select>
            </div>
            <div class="field">
                <label>অভিজ্ঞতা (বছর)</label>
                <input type="number" min="0" wire:model="experience_years">
            </div>
            <div class="field">
                <label>যোগদানের তারিখ</label>
                <input type="date" wire:model="joining_date">
            </div>
            <div class="field">
                <label>পূর্ববর্তী কর্মস্থল</label>
                <input type="text" wire:model="previous_workplace">
            </div>
        </div>
    </div>

    <div class="settings-section">
        <h3>বেতন ও ব্যাংক তথ্য</h3>
        <div class="grid2">
            <div class="field">
                <label>মূল বেতন</label>
                <input type="number" min="0" wire:model="base_salary">
            </div>
            <div class="field">
                <label>বাড়ি ভাড়া ভাতা</label>
                <input type="number" min="0" wire:model="house_rent">
            </div>
            <div class="field">
                <label>চিকিৎসা ভাতা</label>
                <input type="number" min="0" wire:model="medical_allowance">
            </div>
            <div class="field">
                <label>ব্যাংকের নাম</label>
                <input type="text" wire:model="bank_name">
            </div>
            <div class="field">
                <label>শাখা</label>
                <input type="text" wire:model="bank_branch">
            </div>
            <div class="field">
                <label>হিসাব নম্বর</label>
                <input type="text" wire:model="bank_account">
            </div>
            <div class="field">
                <label>মোবাইল ব্যাংকিং</label>
                <input type="text" wire:model="mobile_banking">
            </div>
        </div>
    </div>

    <div class="save-bar">
        <button class="btn-primary" wire:click="save" type="button">সংরক্ষণ করুন</button>
    </div>
</div>
