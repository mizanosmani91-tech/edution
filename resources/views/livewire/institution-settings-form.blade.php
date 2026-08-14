<div>
    <div class="page-head">
        <div>
            <h2>প্রতিষ্ঠান সেটিংস</h2>
            <p>আপনার প্রতিষ্ঠানের তথ্য, ফিচার ও ব্র্যান্ডিং কনফিগার করুন</p>
        </div>
    </div>

    @if ($saved)
        <div class="info-box" style="background:rgba(47,110,82,.1);border-color:rgba(47,110,82,.3);color:var(--good);">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20 6 9 17l-5-5"/></svg>
            সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে।
        </div>
    @endif

    {{-- প্রতিষ্ঠান প্রোফাইল --}}
    <div class="settings-section">
        <h3>প্রতিষ্ঠান প্রোফাইল</h3>
        <p class="sub">এই তথ্য পোর্টাল ও প্রিন্টেবল ডকুমেন্টে দেখানো হবে</p>

        <div class="grid2">
            <div class="field">
                <label>প্রতিষ্ঠানের নাম <span class="req">*</span></label>
                <input type="text" wire:model="institutionName">
                @error('institutionName') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label>ফোন নম্বর</label>
                <input type="text" wire:model="institutionPhone">
            </div>
        </div>
        <div class="field full">
            <label>ঠিকানা</label>
            <textarea wire:model="institutionAddress"></textarea>
        </div>
    </div>

    {{-- ফিচার টগল --}}
    <div class="settings-section">
        <h3>ফিচার সেটিংস</h3>
        <p class="sub">প্রতিষ্ঠানের প্রয়োজন অনুযায়ী চালু/বন্ধ করুন</p>

        <div class="switch-row">
            <div class="switch-label">
                <div class="t1">বিভাগ (Department) সিস্টেম</div>
                <div class="t2">চালু করলে ক্লাসের সাথে বিভাগ যোগ করা যাবে (বিজ্ঞান/মানবিক ইত্যাদি)</div>
            </div>
            <label class="switch">
                <input type="checkbox" wire:model="hasDepartments">
                <span class="switch-track"></span>
            </label>
        </div>

        <div class="switch-row">
            <div class="switch-label">
                <div class="t1">পরপর পিরিয়ড ব্লকিং</div>
                <div class="t2">চালু থাকলে একজন শিক্ষকের রুটিনে পরপর দুই পিরিয়ড দেওয়া যাবে না</div>
            </div>
            <label class="switch">
                <input type="checkbox" wire:model="consecutivePeriodBlocking">
                <span class="switch-track"></span>
            </label>
        </div>
    </div>

    {{-- লোগো ও ফেভিকন --}}
    <div class="settings-section">
        <h3>লোগো ও ফেভিকন</h3>
        <p class="sub">লোগো ড্যাশবোর্ডের সাইডবারে ও লগইন পেজে দেখাবে, ফেভিকন ব্রাউজার ট্যাবের আইকনে ব্যবহৃত হবে</p>
        <div style="display:flex; gap:40px; flex-wrap:wrap;">
            <div>
                <p style="font-size:12.5px; font-weight:700; margin-bottom:8px;">লোগো</p>
                <livewire:photo-upload
                    model="App\Models\Institution"
                    :model-id="auth()->user()->institution_id"
                    category="institution-logos"
                    :current-url="auth()->user()->institution->logo_path ? \Illuminate\Support\Facades\Storage::disk('public')->url(auth()->user()->institution->logo_path) : null"
                    key="institution-logo-upload"
                />
            </div>
            <div>
                <p style="font-size:12.5px; font-weight:700; margin-bottom:8px;">ফেভিকন</p>
                <livewire:photo-upload
                    model="App\Models\Institution"
                    :model-id="auth()->user()->institution_id"
                    category="institution-favicons"
                    :current-url="auth()->user()->institution->favicon_path ? \Illuminate\Support\Facades\Storage::disk('public')->url(auth()->user()->institution->favicon_path) : null"
                    key="institution-favicon-upload"
                />
            </div>
        </div>
    </div>

    {{-- ব্র্যান্ডিং --}}
    <div class="settings-section">
        <h3>ব্র্যান্ড কালার</h3>
        <p class="sub">আপনার প্রতিষ্ঠানের নিজস্ব রং সেট করুন (ভবিষ্যতে white-labeling-এ ব্যবহৃত হবে)</p>

        <div class="color-row">
            <div class="color-field">
                <label>প্রাইমারি রং</label>
                <input type="color" wire:model="themePrimaryColor">
            </div>
            <div class="color-field">
                <label>অ্যাকসেন্ট রং</label>
                <input type="color" wire:model="themeAccentColor">
            </div>
        </div>
    </div>

    <div class="save-bar">
        <button class="btn-primary" wire:click="save" type="button">সংরক্ষণ করুন</button>
    </div>
</div>