<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">শিক্ষক ও স্টাফ / পে-রোল</div>
            <h2>পে-রোল / বেতন ব্যবস্থাপনা</h2>
        </div>
        <button class="btn-primary" wire:click="generatePayroll" type="button">এই মাসের পে-রোল তৈরি করুন</button>
    </div>

    <div class="select-card" style="margin-bottom:16px;display:flex;gap:12px;flex-wrap:wrap;">
        <select wire:model.live="month" style="max-width:180px;">
            @foreach (['জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'] as $i => $m)
                <option value="{{ $i + 1 }}">{{ $m }}</option>
            @endforeach
        </select>
        <select wire:model.live="year" style="max-width:140px;">
            @foreach (range(now()->year - 2, now()->year + 1) as $y)
                <option value="{{ $y }}">{{ $y }}</option>
            @endforeach
        </select>
    </div>

    <div class="stat-strip">
        <div class="stat-chip" style="--accent:var(--maroon)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div><div><div class="sv">৳{{ number_format($totalNet, 0) }}</div><div class="sl">মোট নেট পে</div></div></div>
        <div class="stat-chip" style="--accent:var(--good)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20 6 9 17l-5-5"/></svg></div><div><div class="sv">{{ $paidCount }}</div><div class="sl">পরিশোধিত</div></div></div>
        <div class="stat-chip" style="--accent:var(--bad)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div><div><div class="sv">{{ $pendingCount }}</div><div class="sl">বাকি</div></div></div>
    </div>

    <div class="table-card">
        <table>
            <thead><tr><th>স্টাফ</th><th>মূল বেতন</th><th>বাড়ি ভাড়া</th><th>মেডিকেল</th><th>অন্যান্য ভাতা</th><th>কর্তন</th><th>নেট পে</th><th>স্ট্যাটাস</th><th></th></tr></thead>
            <tbody>
                @forelse ($records as $r)
                    <tr wire:key="pr-{{ $r->id }}">
                        <td>{{ $r->teacher->name ?? '—' }}</td>
                        <td>৳{{ number_format($r->base_salary, 0) }}</td>
                        <td>৳{{ number_format($r->house_rent, 0) }}</td>
                        <td>৳{{ number_format($r->medical_allowance, 0) }}</td>
                        <td>৳{{ number_format($r->other_allowance, 0) }}</td>
                        <td>৳{{ number_format($r->deductions, 0) }}</td>
                        <td style="font-weight:700;">৳{{ number_format($r->net_pay, 0) }}</td>
                        <td><span class="pill {{ $r->status === 'paid' ? 'active' : 'due' }}">{{ $r->status === 'paid' ? 'পরিশোধিত' : 'বাকি' }}</span></td>
                        <td>
                            <div class="row-actions">
                                <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="openAdjust('{{ $r->id }}')">সমন্বয়</button>
                                @if ($r->status !== 'paid')
                                    <button class="btn-primary" style="padding:6px 12px;font-size:12.5px;" wire:click="markPaid('{{ $r->id }}')">পরিশোধিত করুন</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" style="text-align:center;color:var(--ink-soft);padding:30px 0;">এই মাসের পে-রোল এখনো তৈরি হয়নি — উপরের বাটনে ক্লিক করুন</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($showAdjustModal)
        <div class="modal-overlay" wire:click.self="$set('showAdjustModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>বেতন সমন্বয়</h3>
                    <button class="modal-close" wire:click="$set('showAdjustModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="field"><label>অতিরিক্ত ভাতা</label><input type="number" step="0.01" wire:model="otherAllowance"></div>
                    <div class="field"><label>কর্তন</label><input type="number" step="0.01" wire:model="deductions"></div>
                </div>
                <div class="field"><label>কর্তনের কারণ</label><input type="text" wire:model="deductionReason"></div>
                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showAdjustModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="saveAdjust" type="button">সংরক্ষণ করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>
