<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">প্রতিষ্ঠানের সাবস্ক্রিপশন ও ব্যালেন্স</div>
            <h2 style="margin:0;">বিলিং</h2>
        </div>
    </div>

    @if ($institution->billing_suspended)
        <div class="info-box" style="margin-bottom:16px;background:#FCE4E4;border-color:#D9534F;">
            বকেয়ার কারণে অ্যাকাউন্ট সাসপেন্ড আছে — পেমেন্ট সাবমিট করে অনুমোদনের অপেক্ষায় থাকুন, দ্রুত সচল করে দেওয়া হবে।
        </div>
    @endif

    <div class="kpi-grid" style="margin-bottom:20px;">
        <div class="stat-chip">
            <div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="4" width="16" height="16" rx="3"/></svg></div>
            <div><div class="sv">{{ $activeStudentCount }}</div><div class="sl">সক্রিয় শিক্ষার্থী</div></div>
        </div>
        @if ($institution->isPrepaid())
            <div class="stat-chip">
                <div class="sic" style="background:color-mix(in srgb, var(--good) 14%, white);"><svg viewBox="0 0 24 24" fill="none" stroke="var(--good)" stroke-width="1.7"><rect x="3.5" y="7" width="17" height="12" rx="2.5"/></svg></div>
                <div><div class="sv">৳{{ number_format((float) $institution->prepaid_balance) }}</div><div class="sl">বর্তমান ব্যালেন্স</div></div>
            </div>
            <div class="stat-chip">
                <div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 19V10l8-6 8 6v9"/></svg></div>
                <div><div class="sv">৳{{ number_format($prepaidMonthlyCost) }}</div><div class="sl">আনুমানিক মাসিক খরচ (৳৫/ছাত্র)</div></div>
            </div>
        @else
            <div class="stat-chip">
                <div class="sic" style="background:color-mix(in srgb, var(--bad) 14%, white);"><svg viewBox="0 0 24 24" fill="none" stroke="var(--bad)" stroke-width="1.7"><rect x="3.5" y="7" width="17" height="12" rx="2.5"/></svg></div>
                <div><div class="sv">৳{{ number_format($postpaidDue ?? 0) }}</div><div class="sl">এই মাসের বিল</div></div>
            </div>
            <div class="stat-chip">
                <div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg></div>
                <div><div class="sv">{{ $institution->billing_grace_ends_at?->translatedFormat('d M') ?? '—' }}</div><div class="sl">গ্রেস পিরিয়ড শেষ</div></div>
            </div>
        @endif
    </div>

    <div class="settings-section" style="margin-bottom:20px;">
        <h3>{{ $institution->isPrepaid() ? 'প্রিপেইড প্ল্যান' : 'পোস্টপেইড প্ল্যান' }}</h3>
        @if ($institution->isPrepaid())
            <p class="sub">প্রতি শিক্ষার্থী মাসে ৳৫ — ব্যালেন্স থেকে স্বয়ংক্রিয়ভাবে কর্তন হয় প্রতি মাসের ১ তারিখে। ব্যালেন্স শেষ হয়ে গেলে অ্যাক্সেস সাময়িকভাবে বন্ধ হয়ে যায়, তাই আগে থেকেই টপ-আপ রাখা ভালো।</p>
            <button type="button" class="btn-primary" wire:click="openPayModal('wallet_topup')" style="margin-top:8px;">ব্যালেন্স টপ-আপ করুন</button>
        @else
            <p class="sub">শিক্ষার্থীসংখ্যা অনুযায়ী মাসিক টায়ার:</p>
            <table style="width:100%;font-size:13px;margin:8px 0 12px;">
                <tbody>
                    @php $prevBound = 1; @endphp
                    @foreach ($tiers as $upperBound => $price)
                        <tr style="{{ $activeStudentCount >= $prevBound && $activeStudentCount <= $upperBound ? 'font-weight:700;color:var(--cover-maroon);' : '' }}">
                            <td style="padding:4px 0;">{{ $prevBound }}–{{ $upperBound }} শিক্ষার্থী</td>
                            <td style="padding:4px 0;">৳{{ number_format($price) }}/মাস</td>
                        </tr>
                        @php $prevBound = $upperBound + 1; @endphp
                    @endforeach
                    <tr><td style="padding:4px 0;">{{ $prevBound }}+ শিক্ষার্থী</td><td style="padding:4px 0;">কাস্টম — যোগাযোগ করুন</td></tr>
                </tbody>
            </table>
            <button type="button" class="btn-primary" wire:click="openPayModal('subscription')">এই মাসের বিল পরিশোধ করুন</button>
        @endif
    </div>

    <div class="table-card">
        <h3 style="padding:14px 16px 0;">পেমেন্ট ইতিহাস</h3>
        <table>
            <thead><tr><th>তারিখ</th><th>ধরন</th><th>পরিমাণ</th><th>পদ্ধতি</th><th>স্ট্যাটাস</th></tr></thead>
            <tbody>
                @forelse ($payments as $pay)
                    <tr>
                        <td>{{ $pay->created_at->translatedFormat('d M, Y') }}</td>
                        <td>{{ $pay->purpose === 'wallet_topup' ? 'ব্যালেন্স টপ-আপ' : 'সাবস্ক্রিপশন' }}</td>
                        <td>৳{{ number_format($pay->amount, 2) }}</td>
                        <td>{{ $pay->method }}</td>
                        <td>
                            <span class="tag {{ $pay->status === 'approved' ? 'good' : ($pay->status === 'rejected' ? 'bad' : 'gold') }}">
                                {{ ['approved'=>'অনুমোদিত','rejected'=>'বাতিল','pending'=>'যাচাইয়ের অপেক্ষায়'][$pay->status] ?? $pay->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;color:var(--ink-soft);padding:24px 0;">এখনো কোনো পেমেন্ট সাবমিট করা হয়নি</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($institution->isPrepaid() && $walletTransactions->isNotEmpty())
        <div class="table-card" style="margin-top:20px;">
            <h3 style="padding:14px 16px 0;">ব্যালেন্স লেজার</h3>
            <table>
                <thead><tr><th>তারিখ</th><th>ধরন</th><th>পরিমাণ</th><th>ব্যালেন্স (পরে)</th><th>নোট</th></tr></thead>
                <tbody>
                    @foreach ($walletTransactions as $tx)
                        <tr>
                            <td>{{ $tx->created_at->translatedFormat('d M, Y') }}</td>
                            <td>{{ ['topup'=>'টপ-আপ','deduction'=>'কর্তন','adjustment'=>'সমন্বয়'][$tx->type] ?? $tx->type }}</td>
                            <td style="color:{{ $tx->amount >= 0 ? 'var(--good)' : 'var(--bad)' }};">{{ $tx->amount >= 0 ? '+' : '' }}৳{{ number_format($tx->amount, 2) }}</td>
                            <td>৳{{ number_format($tx->balance_after, 2) }}</td>
                            <td style="color:var(--ink-soft);font-size:12px;">{{ $tx->note }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- ============ পেমেন্ট সাবমিট মোডাল ============ --}}
    @if ($showPayModal)
        <div class="modal-overlay open" wire:click.self="closePayModal">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>{{ $payPurpose === 'wallet_topup' ? 'ব্যালেন্স টপ-আপ' : 'বিল পরিশোধ' }}</h3>
                    <button class="modal-close" wire:click="closePayModal" type="button">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="field">
                        <label>পরিমাণ (টাকা)</label>
                        <input type="number" step="0.01" wire:model="payAmount">
                        @error('payAmount') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label>পদ্ধতি</label>
                        <select wire:model="payMethod">
                            <option value="bkash">বিকাশ</option>
                            <option value="nagad">নগদ</option>
                            <option value="rocket">রকেট</option>
                            <option value="bank">ব্যাংক ট্রান্সফার</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>ট্রানজেকশন আইডি / রেফারেন্স</label>
                        <input type="text" wire:model="payRef" placeholder="যেমন: বিকাশ TrxID">
                        @error('payRef') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    </div>
                    <p class="sub">সাবমিট করার পর আমাদের টিম যাচাই করে অনুমোদন করবে (সাধারণত কিছুক্ষণের মধ্যে)।</p>
                    <button type="button" class="btn-primary" wire:click="submitPayment" style="width:100%;">সাবমিট করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>
