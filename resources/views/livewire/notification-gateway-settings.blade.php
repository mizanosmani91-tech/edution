<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">যোগাযোগ / গেটওয়ে সেটিংস</div>
            <h2>SMS / Email গেটওয়ে সেটিংস</h2>
        </div>
    </div>

    <div class="alert-note" style="margin-bottom:16px;">
        SMS চালু করলে ফি বকেয়া, ছাত্র/ছাত্রীর অনুপস্থিতি, ও পরীক্ষার ফলাফল প্রকাশের সময় অভিভাবকদের কাছে স্বয়ংক্রিয়ভাবে SMS যাবে (in-app নোটিফিকেশনের পাশাপাশি)। এখনই শুধু <b>BulkSMSBD</b> প্রোভাইডার লাইভ — bulksmsbd.net এর API key ও Sender ID এখানে বসান। অন্য প্রোভাইডার (Alpha SMS/Twilio) সিলেক্ট করলে সেভ হবে কিন্তু SMS পাঠানো হবে না, যতক্ষণ না সেটার জন্য আলাদা কোড যোগ করা হয়।
    </div>

    @if ($savedMessage)
        <div class="alert-note" style="margin-bottom:16px;">{{ $savedMessage }}</div>
    @endif

    <div class="cert-form-card lifecycle-page" style="margin-bottom:16px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <h3 style="font-family:'Tiro Bangla',serif;font-size:16px;margin:0;">SMS Gateway</h3>
            <label class="switch"><input type="checkbox" wire:model="smsEnabled"><span class="switch-track"></span></label>
        </div>
        <div class="info-grid" style="grid-template-columns:1fr 1fr;">
            <div class="field">
                <label>প্রোভাইডার</label>
                <select wire:model="smsProvider">
                    <option value="bulksmsbd">BulkSMSBD</option>
                    <option value="alpha_sms">Alpha SMS</option>
                    <option value="twilio">Twilio</option>
                    <option value="other">অন্যান্য</option>
                </select>
            </div>
            <div class="field"><label>Sender ID</label><input type="text" wire:model="smsSenderId"></div>
        </div>
        <div class="field"><label>API Key</label><input type="password" wire:model="smsApiKey"></div>
    </div>

    <div class="cert-form-card lifecycle-page" style="margin-bottom:16px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <h3 style="font-family:'Tiro Bangla',serif;font-size:16px;margin:0;">Email Notification (SMTP)</h3>
            <label class="switch"><input type="checkbox" wire:model="emailEnabled"><span class="switch-track"></span></label>
        </div>
        <div class="info-grid" style="grid-template-columns:1fr 1fr;">
            <div class="field"><label>SMTP Host</label><input type="text" wire:model="emailSmtpHost" placeholder="smtp.gmail.com"></div>
            <div class="field"><label>SMTP Port</label><input type="text" wire:model="emailSmtpPort"></div>
        </div>
        <div class="info-grid" style="grid-template-columns:1fr 1fr;">
            <div class="field"><label>Username</label><input type="text" wire:model="emailSmtpUsername"></div>
            <div class="field"><label>Password</label><input type="password" wire:model="emailSmtpPassword"></div>
        </div>
        <div class="info-grid" style="grid-template-columns:1fr 1fr 1fr;">
            <div class="field">
                <label>Encryption</label>
                <select wire:model="emailSmtpEncryption">
                    <option value="tls">TLS</option>
                    <option value="ssl">SSL</option>
                    <option value="none">None</option>
                </select>
            </div>
            <div class="field"><label>From Address</label><input type="email" wire:model="emailFromAddress"></div>
            <div class="field"><label>From Name</label><input type="text" wire:model="emailFromName"></div>
        </div>
    </div>

    <button class="btn-primary" wire:click="save" type="button">সংরক্ষণ করুন</button>
</div>
