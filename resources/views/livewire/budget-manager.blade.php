<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">ফি/অর্থ / বাজেট পরিকল্পনা</div>
            <h2>বাজেট পরিকল্পনা</h2>
            <p>প্রতিটা খরচ-ক্যাটাগরির জন্য মাসিক বাজেট বেঁধে দিন, প্রকৃত খরচের সাথে তুলনা করে দেখুন</p>
        </div>
    </div>

    <div class="select-card" style="margin-bottom:16px;">
        <div class="f-field">
            <label>মাস</label>
            <input type="month" wire:model.live="periodMonth" style="max-width:200px;">
        </div>
    </div>

    <div class="stat-strip" style="margin-bottom:16px;">
        <div class="stat-chip" style="--accent:var(--admin)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3.5" y="7" width="17" height="12" rx="2.5"/><path d="M3.5 11h17"/></svg></div><div><div class="sv">৳{{ number_format($totalPlanned, 0) }}</div><div class="sl">মোট বরাদ্দকৃত বাজেট</div></div></div>
        <div class="stat-chip" style="--accent:{{ $totalSpent > $totalPlanned && $totalPlanned > 0 ? 'var(--bad)' : 'var(--good)' }}"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div><div><div class="sv">৳{{ number_format($totalSpent, 0) }}</div><div class="sl">এই মাসে খরচ হয়েছে</div></div></div>
    </div>

    <div class="table-card">
        <table>
            <thead><tr><th>ক্যাটাগরি</th><th>বাজেট বরাদ্দ</th><th>খরচ হয়েছে</th><th style="min-width:160px;">অগ্রগতি</th></tr></thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td style="font-weight:600;">{{ $row['category'] }}</td>
                        <td><input type="number" step="0.01" min="0" wire:model="planned.{{ $row['category'] }}" style="max-width:120px;font-size:13px;" placeholder="৳০"></td>
                        <td>৳{{ number_format($row['spent'], 0) }}</td>
                        <td>
                            <div style="background:var(--paper-deep);border-radius:8px;height:8px;overflow:hidden;">
                                <div style="width:{{ min(100, $row['percent']) }}%;height:100%;background:{{ $row['over'] ? 'var(--bad)' : 'var(--good)' }};"></div>
                            </div>
                            <span style="font-size:11px;color:var(--ink-soft);">{{ $row['percent'] }}% @if($row['over']) — বাজেট ছাড়িয়ে গেছে @endif</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center;color:var(--ink-soft);padding:24px 0;">এখনো কোনো ক্যাটাগরি নেই — নিচে থেকে যোগ করুন</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="select-card" style="margin-top:14px;">
        <div class="f-field f-search" style="flex:1;">
            <label>নতুন ক্যাটাগরি যোগ করুন</label>
            <div class="shell">
                <input type="text" wire:model="newCategory" placeholder="যেমন: স্টেশনারি, মেরামত" wire:keydown.enter="addCategory">
            </div>
        </div>
        <button class="btn-ghost" wire:click="addCategory" type="button" style="align-self:flex-end;">যোগ করুন</button>
    </div>

    <div class="att-save-bar">
        <div class="info">মাস: <b>{{ \Carbon\Carbon::createFromFormat('Y-m', $periodMonth)->translatedFormat('F Y') }}</b></div>
        <button type="button" class="btn-primary" wire:click="save">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>
            বাজেট সংরক্ষণ করুন
        </button>
    </div>

    @if ($saved)
        <div style="position:fixed;bottom:90px;left:50%;transform:translateX(-50%);background:var(--ink);color:#fff;padding:10px 18px;border-radius:10px;font-size:13px;z-index:100;">বাজেট সংরক্ষিত হয়েছে</div>
    @endif
</div>
