<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">যোগাযোগ / ভিজিটর লগ</div>
            <h2>ভিজিটর/গেট পাস</h2>
            <p>প্রতিষ্ঠানে কে কখন ঢুকল-বের হলো তার রেকর্ড</p>
        </div>
        <button class="btn-primary" wire:click="openModal" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            নতুন ভিজিটর এন্ট্রি
        </button>
    </div>

    @if ($stillInside > 0)
        <div class="alert-note" style="margin-bottom:16px;">
            এই মুহূর্তে <b>{{ $stillInside }}</b> জন ভিজিটর ভেতরে আছেন (চেক-আউট করা হয়নি)।
        </div>
    @endif

    <div class="select-card" style="margin-bottom:16px;display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
        <label style="font-size:13px;">তারিখ:</label>
        <input type="date" wire:model.live="dateFilter" style="max-width:180px;">
    </div>

    <div class="table-card">
        <table>
            <thead><tr><th>নাম</th><th>উদ্দেশ্য</th><th>কার সাথে দেখা</th><th>প্রবেশ</th><th>বাহির</th><th></th></tr></thead>
            <tbody>
                @forelse ($visitors as $v)
                    <tr wire:key="visitor-{{ $v->id }}">
                        <td style="font-weight:600;">{{ $v->name }} @if($v->phone)<span class="sub" style="display:block;font-size:11.5px;">{{ $v->phone }}</span>@endif</td>
                        <td>{{ $v->purpose }}</td>
                        <td>{{ $v->meeting_with ?: '—' }}</td>
                        <td style="font-size:12.5px;">{{ $v->check_in->format('h:i A') }}</td>
                        <td style="font-size:12.5px;">
                            @if ($v->check_out)
                                {{ $v->check_out->format('h:i A') }}
                            @else
                                <span class="pill day">ভেতরে আছেন</span>
                            @endif
                        </td>
                        <td>
                            @if (! $v->check_out)
                                <button class="btn-ghost" style="padding:6px 12px;font-size:12px;" wire:click="checkOut('{{ $v->id }}')">চেক-আউট</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;color:var(--ink-soft);padding:30px 0;">এই তারিখে কোনো ভিজিটর নেই</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:14px;">{{ $visitors->links() }}</div>

    @if ($showModal)
        <div class="modal-overlay" wire:click.self="$set('showModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>নতুন ভিজিটর এন্ট্রি</h3>
                    <button class="modal-close" wire:click="$set('showModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="field">
                        <label>নাম <span class="req">*</span></label>
                        <input type="text" wire:model="name">
                        @error('name') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    </div>
                    <div class="field"><label>ফোন <span class="opt">(ঐচ্ছিক)</span></label><input type="text" wire:model="phone"></div>
                </div>
                <div class="field">
                    <label>উদ্দেশ্য <span class="req">*</span></label>
                    <input type="text" wire:model="purpose" placeholder="যেমন: ভর্তি সংক্রান্ত, অভিভাবক সাক্ষাৎ">
                    @error('purpose') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="field"><label>কার সাথে দেখা করতে এসেছেন <span class="opt">(ঐচ্ছিক)</span></label><input type="text" wire:model="meetingWith"></div>
                <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="field">
                        <label>পরিচয়পত্রের ধরন <span class="opt">(ঐচ্ছিক)</span></label>
                        <select wire:model="idType">
                            <option value="">নির্বাচন করুন</option>
                            <option value="nid">জাতীয় পরিচয়পত্র</option>
                            <option value="driving_license">ড্রাইভিং লাইসেন্স</option>
                            <option value="other">অন্যান্য</option>
                        </select>
                    </div>
                    <div class="field"><label>পরিচয়পত্র নম্বর <span class="opt">(ঐচ্ছিক)</span></label><input type="text" wire:model="idNumber"></div>
                </div>
                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="save" type="button">এন্ট্রি করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>
