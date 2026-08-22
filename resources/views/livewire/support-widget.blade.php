<div x-data="{}" x-init="$wire.pageUrl = window.location.href; $wire.browserInfo = navigator.userAgent;">
    <button type="button" wire:click="openModal"
        style="position:fixed; right:20px; bottom:86px; z-index:60; display:flex; align-items:center; gap:8px; background:var(--cover-maroon,#6C5CE7); color:#fff; border:none; border-radius:999px; padding:12px 18px; box-shadow:0 8px 24px rgba(0,0,0,.18); font-weight:700; font-size:13px; cursor:pointer;">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 0 1 5 0c0 1.7-2 2-2 3.5"/><path d="M12 17h.01"/></svg>
        সাহায্য দরকার?
    </button>

    @if ($open)
        <div class="modal-overlay open" wire:click.self="closeModal">
            <div class="modal-box" style="max-width:480px;">
                <div class="modal-head">
                    <div><h3>সাহায্য দরকার?</h3></div>
                    <button class="modal-close" type="button" wire:click="closeModal">&times;</button>
                </div>
                <div class="modal-body">
                    @if ($sent)
                        <div style="text-align:center; padding:20px 0;">
                            <div style="font-size:38px; margin-bottom:8px;">✅</div>
                            <p style="font-weight:700; margin-bottom:6px;">টিকেট পাঠানো হয়েছে</p>
                            <p style="color:var(--ink-soft); font-size:13px;">আমাদের টিম দ্রুতই যোগাযোগ করবে। সব উত্তর <a href="{{ route('support-tickets.index') }}" style="font-weight:700;text-decoration:underline;">সাপোর্ট টিকেট পেজে</a> দেখতে পাবেন।</p>
                        </div>
                    @else
                        <p style="color:var(--ink-soft); font-size:12.5px; margin-bottom:12px;">যে পেজে আছেন সেটার ঠিকানা স্বয়ংক্রিয়ভাবে যুক্ত হয়ে যাবে — আপনি শুধু সমস্যাটা লিখুন।</p>
                        <div class="field"><label>বিষয়</label><input type="text" wire:model="subject" placeholder="যেমন: ফি জমা দিতে সমস্যা হচ্ছে"></div>
                        @error('subject')<div class="err">{{ $message }}</div>@enderror
                        <div class="field"><label>অগ্রাধিকার</label>
                            <select wire:model="priority">
                                <option value="low">সাধারণ</option>
                                <option value="med">মধ্যম</option>
                                <option value="high">জরুরি</option>
                            </select>
                        </div>
                        <div class="field"><label>বিস্তারিত লিখুন</label><textarea wire:model="body" rows="4" placeholder="কী সমস্যা হচ্ছে, কী করার চেষ্টা করছিলেন..."></textarea></div>
                        @error('body')<div class="err">{{ $message }}</div>@enderror
                    @endif
                </div>
                @unless ($sent)
                    <div class="modal-foot" style="justify-content:flex-end;">
                        <button class="btn-primary" type="button" wire:click="submit">টিকেট জমা দিন</button>
                    </div>
                @endunless
            </div>
        </div>
    @endif
</div>
