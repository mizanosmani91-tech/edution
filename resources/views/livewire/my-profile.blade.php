<div>
    <div class="page-head">
        <div>
            <h2>আমার প্রোফাইল</h2>
            <p>{{ $user->name }} · {{ ucfirst($user->role) }}</p>
        </div>
    </div>

    <div class="settings-section">
        <h3>ব্যক্তিগত তথ্য</h3>

        @if ($profileSaved)
            <div class="info-box" style="background:rgba(16,185,129,.1);border-color:rgba(16,185,129,.3);color:var(--good);">তথ্য সংরক্ষণ করা হয়েছে।</div>
        @endif

        <div class="grid2">
            <div class="field">
                <label>নাম <span class="req">*</span></label>
                <input type="text" wire:model="name">
                @error('name') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label>ফোন</label>
                <input type="text" wire:model="phone">
                @error('phone') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label>ইমেইল (লগইনের জন্য) <span class="req">*</span></label>
                <input type="email" wire:model="email">
                <p class="hint">ইমেইল বদলালে নিচের "বর্তমান পাসওয়ার্ড" ঘরটি পূরণ করতে হবে এবং এরপর নতুন ইমেইল দিয়ে লগইন করতে হবে।</p>
                @error('email') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label>বর্তমান পাসওয়ার্ড <span class="opt">— শুধু ইমেইল বদলালে</span></label>
                <input type="password" wire:model="email_password" autocomplete="current-password">
                @error('email_password') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;margin-top:8px;">
            <button class="btn-primary" type="button" wire:click="saveProfile">সংরক্ষণ করুন</button>
        </div>
    </div>

    <div class="settings-section">
        <h3>পাসওয়ার্ড বদলান</h3>

        @if ($passwordSaved)
            <div class="info-box" style="background:rgba(16,185,129,.1);border-color:rgba(16,185,129,.3);color:var(--good);">পাসওয়ার্ড বদলানো হয়েছে।</div>
        @endif

        <div class="grid2">
            <div class="field">
                <label>বর্তমান পাসওয়ার্ড <span class="req">*</span></label>
                <input type="password" wire:model="current_password" autocomplete="current-password">
                @error('current_password') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
            </div>
            <div></div>
            <div class="field">
                <label>নতুন পাসওয়ার্ড <span class="req">*</span> <span class="opt">— কমপক্ষে ৮ অক্ষর</span></label>
                <input type="password" wire:model="new_password" autocomplete="new-password">
                @error('new_password') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label>নতুন পাসওয়ার্ড আবার <span class="req">*</span></label>
                <input type="password" wire:model="new_password_confirmation" autocomplete="new-password">
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;margin-top:8px;">
            <button class="btn-primary" type="button" wire:click="changePassword">পাসওয়ার্ড বদলান</button>
        </div>
    </div>
</div>
