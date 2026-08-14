<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">ফি/অর্থ / পেমেন্ট গেটওয়ে</div>
            <h2>অনলাইন পেমেন্ট গেটওয়ে</h2>
        </div>
    </div>

    <div class="alert-note" style="margin-bottom:16px;">
        এখানে শুধু আপনার bKash/Nagad মার্চেন্ট তথ্য সংরক্ষণ করা যাবে। প্রকৃত অনলাইন পেমেন্ট গ্রহণের ফিচার এখনো সক্রিয় করা হয়নি — এর জন্য আপনার bKash/Nagad মার্চেন্ট অ্যাকাউন্ট থেকে আসল API credential দরকার হবে। credential দিলে পরবর্তী ধাপে লাইভ ইন্টিগ্রেশন যুক্ত করা হবে।
    </div>

    @if ($savedMessage)
        <div class="alert-note" style="margin-bottom:16px;">{{ $savedMessage }}</div>
    @endif

    <div class="cert-form-card lifecycle-page" style="margin-bottom:16px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <h3 style="font-family:'Tiro Bangla',serif;font-size:16px;margin:0;">bKash</h3>
            <label class="switch"><input type="checkbox" wire:model="bkashEnabled"><span class="switch-track"></span></label>
        </div>
        <div class="info-grid" style="grid-template-columns:1fr 1fr;">
            <div class="field"><label>মার্চেন্ট নম্বর</label><input type="text" wire:model="bkashMerchantNumber"></div>
            <div class="field"><label>API Key</label><input type="text" wire:model="bkashApiKey" placeholder="bKash sandbox/production API key"></div>
        </div>
        <div class="field"><label>API Secret</label><input type="password" wire:model="bkashApiSecret"></div>
    </div>

    <div class="cert-form-card lifecycle-page" style="margin-bottom:16px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <h3 style="font-family:'Tiro Bangla',serif;font-size:16px;margin:0;">Nagad</h3>
            <label class="switch"><input type="checkbox" wire:model="nagadEnabled"><span class="switch-track"></span></label>
        </div>
        <div class="info-grid" style="grid-template-columns:1fr 1fr;">
            <div class="field"><label>মার্চেন্ট নম্বর</label><input type="text" wire:model="nagadMerchantNumber"></div>
            <div class="field"><label>API Key</label><input type="text" wire:model="nagadApiKey"></div>
        </div>
    </div>

    <button class="btn-primary" wire:click="save" type="button">সংরক্ষণ করুন</button>
</div>
