<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">অর্থ ব্যবস্থাপনা / আয়-ব্যয় রিপোর্ট</div>
            <h2>আয়-ব্যয় রিপোর্ট</h2>
            <p>নির্দিষ্ট সময়ে মোট ফি আদায় ও খরচের তুলনামূলক চিত্র</p>
        </div>
    </div>

    <div class="select-card">
        <div class="f-field">
            <label>শুরুর তারিখ</label>
            <input type="date" wire:model.live="from">
        </div>
        <div class="f-field">
            <label>শেষ তারিখ</label>
            <input type="date" wire:model.live="to">
        </div>
    </div>

    <div class="stat-strip">
        <div class="stat-chip" style="--accent:var(--good)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3v12m0 0-4-4m4 4 4-4"/><path d="M4 19h16"/></svg></div><div><div class="sv">৳{{ number_format($totalIncome) }}</div><div class="sl">মোট আয়</div></div></div>
        <div class="stat-chip" style="--accent:var(--bad)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 21V9m0 0 4 4m-4-4-4 4"/><path d="M4 5h16"/></svg></div><div><div class="sv">৳{{ number_format($totalExpense) }}</div><div class="sl">মোট খরচ</div></div></div>
        <div class="stat-chip" style="--accent:var(--gold)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3.5" y="7" width="17" height="12" rx="2.5"/></svg></div><div><div class="sv">৳{{ number_format($net) }}</div><div class="sl">নীট (আয় − খরচ)</div></div></div>
    </div>

    <div class="grid2col">
        <div class="card">
            <div class="card-head"><div><h3>আয়ের উৎসভিত্তিক বিভাজন</h3></div></div>
            @if ($incomeByType->isNotEmpty())
                @foreach ($incomeByType as $type => $amount)
                    <div class="fee-line"><span>{{ $type }}</span><span>৳{{ number_format($amount) }}</span></div>
                @endforeach
            @else
                <div class="empty-note"><div class="en-sub">এই সময়ে কোনো আয়ের রেকর্ড নেই।</div></div>
            @endif
        </div>
        <div class="card">
            <div class="card-head"><div><h3>খরচের ক্যাটাগরিভিত্তিক বিভাজন</h3></div></div>
            @if ($expenseByCategory->isNotEmpty())
                @foreach ($expenseByCategory as $category => $amount)
                    <div class="fee-line"><span>{{ $category }}</span><span>৳{{ number_format($amount) }}</span></div>
                @endforeach
            @else
                <div class="empty-note"><div class="en-sub">এই সময়ে কোনো খরচের রেকর্ড নেই।</div></div>
            @endif
        </div>
    </div>
</div>
