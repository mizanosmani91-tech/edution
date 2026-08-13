<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">অর্থ ব্যবস্থাপনা / খরচ ও ব্যয়</div>
            <h2>খরচ/ব্যয় ট্র্যাকিং</h2>
            <p>প্রতিষ্ঠানের নিয়মিত খরচ রেকর্ড রাখুন</p>
        </div>
        <button class="btn-primary" wire:click="openModal" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            নতুন খরচ যোগ করুন
        </button>
    </div>

    <div class="stat-strip">
        <div class="stat-chip" style="--accent:var(--bad)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3.5" y="7" width="17" height="12" rx="2.5"/></svg></div><div><div class="sv">৳{{ number_format($thisMonthTotal) }}</div><div class="sl">এই মাসের মোট খরচ</div></div></div>
    </div>

    <div class="table-card">
        <table>
            <thead><tr><th>তারিখ</th><th>ক্যাটাগরি</th><th>বিবরণ</th><th>পরিমাণ</th><th></th></tr></thead>
            <tbody>
                @forelse ($expenses as $expense)
                    <tr wire:key="exp-{{ $expense->id }}">
                        <td>{{ $expense->date->format('d M, Y') }}</td>
                        <td>{{ $expense->category }}</td>
                        <td>{{ $expense->description ?? '—' }}</td>
                        <td>৳{{ number_format($expense->amount, 0) }}</td>
                        <td>
                            <button wire:click="delete('{{ $expense->id }}')" wire:confirm="এই খরচের এন্ট্রি মুছে ফেলতে চান?" title="মুছুন">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m-8 0 1 12h8l1-12"/></svg>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;color:var(--ink-soft);padding:30px 0;">এখনো কোনো খরচ যোগ করা হয়নি</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="table-foot">
            <div class="pager">{{ $expenses->links() }}</div>
        </div>
    </div>

    @if ($showModal)
        <div class="modal-overlay" wire:click.self="$set('showModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>নতুন খরচ যোগ করুন</h3>
                    <button class="modal-close" wire:click="$set('showModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="field">
                    <label>ক্যাটাগরি <span class="req">*</span></label>
                    <input type="text" wire:model="category" placeholder="যেমন: বেতন, ইউটিলিটি, রক্ষণাবেক্ষণ">
                    @error('category') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>

                <div class="row-2">
                    <div class="field">
                        <label>পরিমাণ (৳) <span class="req">*</span></label>
                        <input type="number" step="0.01" wire:model="amount">
                        @error('amount') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label>তারিখ <span class="req">*</span></label>
                        <input type="date" wire:model="date">
                        @error('date') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="field">
                    <label>বিবরণ</label>
                    <textarea wire:model="description" rows="3" placeholder="ঐচ্ছিক..."></textarea>
                </div>

                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="save" type="button">সংরক্ষণ করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>
