<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">ফি/অর্থ / পেমেন্ট গেটওয়ে</div>
            <h2>অনলাইন পেমেন্ট গেটওয়ে</h2>
        </div>
    </div>

    <div class="alert-note" style="margin-bottom:16px;">
        bKash ও Nagad দুইটার জন্যই অটোমেটিক অনলাইন পেমেন্ট চালু আছে — নিচের তথ্য সঠিকভাবে দিলে অভিভাবক নিজেই পরিশোধ করতে পারবেন, আর সেটা এডমিনের ম্যানুয়াল কনফার্মেশন ছাড়াই স্বয়ংক্রিয়ভাবে হিসেবে যোগ হয়ে যাবে। প্রথমে "Sandbox মোড" চালু রেখে টেস্ট করে নিন, তারপর প্রোডাকশন ক্রেডেনশিয়াল দিয়ে Sandbox বন্ধ করুন। Nagad এর "Merchant Private Key" ও "PG Public Key" Nagad Merchant Portal থেকে "Key Generate" করে পাওয়া যায় — শুধু BEGIN/END লাইন বাদ দিয়ে মাঝের অংশটুকু এখানে বসান।
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
            <div class="field"><label>App Key</label><input type="text" wire:model="bkashApiKey" placeholder="bKash App Key"></div>
            <div class="field"><label>App Secret</label><input type="password" wire:model="bkashApiSecret"></div>
            <div class="field"><label>Username</label><input type="text" wire:model="bkashUsername" placeholder="bKash Checkout Username"></div>
            <div class="field"><label>Password</label><input type="password" wire:model="bkashPassword"></div>
            <div class="field" style="display:flex;align-items:center;gap:8px;padding-top:22px;">
                <label class="switch"><input type="checkbox" wire:model="bkashSandbox"><span class="switch-track"></span></label>
                <label style="margin:0;">Sandbox মোড (টেস্টের জন্য)</label>
            </div>
        </div>
    </div>

    <div class="cert-form-card lifecycle-page" style="margin-bottom:16px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <h3 style="font-family:'Tiro Bangla',serif;font-size:16px;margin:0;">Nagad</h3>
            <label class="switch"><input type="checkbox" wire:model="nagadEnabled"><span class="switch-track"></span></label>
        </div>
        <div class="info-grid" style="grid-template-columns:1fr 1fr;">
            <div class="field"><label>মার্চেন্ট নম্বর</label><input type="text" wire:model="nagadMerchantNumber"></div>
            <div class="field"><label>Merchant ID</label><input type="text" wire:model="nagadMerchantId" placeholder="Nagad Merchant Portal থেকে"></div>
            <div class="field" style="grid-column:1/-1;"><label>Merchant Private Key</label><textarea wire:model="nagadMerchantPrivateKey" rows="3" placeholder="-----BEGIN/END RSA PRIVATE KEY----- বাদ দিয়ে মাঝের অংশ"></textarea></div>
            <div class="field" style="grid-column:1/-1;"><label>Nagad PG Public Key</label><textarea wire:model="nagadPgPublicKey" rows="3" placeholder="-----BEGIN/END PUBLIC KEY----- বাদ দিয়ে মাঝের অংশ"></textarea></div>
            <div class="field" style="display:flex;align-items:center;gap:8px;padding-top:6px;">
                <label class="switch"><input type="checkbox" wire:model="nagadSandbox"><span class="switch-track"></span></label>
                <label style="margin:0;">Sandbox মোড (টেস্টের জন্য)</label>
            </div>
        </div>
    </div>

    <button class="btn-primary" wire:click="save" type="button">সংরক্ষণ করুন</button>
</div>
