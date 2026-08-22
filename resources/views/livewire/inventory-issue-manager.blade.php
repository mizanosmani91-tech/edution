<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">ইনভেন্টরি / ইস্যু ও রিটার্ন</div>
            <h2>ইস্যু ও রিটার্ন</h2>
            <p>কোন আইটেম কার কাছে/কোথায় গেল তার হিসাব রাখুন</p>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('inventory.index') }}" class="btn-ghost">আইটেম তালিকা</a>
            <button class="btn-primary" wire:click="openModal" type="button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
                নতুন ইস্যু
            </button>
        </div>
    </div>

    <div class="tabs-bar">
        <button type="button" class="tab-btn {{ $tab === 'issued' ? 'active' : '' }}" wire:click="$set('tab','issued')">ইস্যুকৃত</button>
        <button type="button" class="tab-btn {{ $tab === 'overdue' ? 'active' : '' }}" wire:click="$set('tab','overdue')">ফেরতের মেয়াদ পার হয়েছে</button>
        <button type="button" class="tab-btn {{ $tab === 'returned' ? 'active' : '' }}" wire:click="$set('tab','returned')">ফেরত দেওয়া</button>
        <button type="button" class="tab-btn {{ $tab === 'lost' ? 'active' : '' }}" wire:click="$set('tab','lost')">হারানো</button>
    </div>

    <div class="table-card">
        <table>
            <thead><tr><th>আইটেম</th><th>পরিমাণ</th><th>কার কাছে/কোথায়</th><th>ইস্যুর তারিখ</th><th>ফেরতের তারিখ</th><th>কার্যক্রম</th></tr></thead>
            <tbody>
                @forelse ($issues as $issue)
                    <tr wire:key="issue-{{ $issue->id }}">
                        <td style="font-weight:600;">{{ $issue->item->name ?? '—' }}</td>
                        <td>{{ $issue->quantity }} {{ $issue->item->unit ?? '' }}</td>
                        <td>{{ $issue->issued_to }}</td>
                        <td>{{ $issue->issued_at->format('d M, Y') }}</td>
                        <td>{{ $issue->expected_return_at?->format('d M, Y') ?? '—' }}</td>
                        <td>
                            @if ($issue->status === 'issued')
                                <div class="row-actions">
                                    <button class="btn-ghost" style="padding:6px 10px;font-size:12px;" wire:click="markReturned('{{ $issue->id }}')">ফেরত নিন</button>
                                    <button class="btn-ghost" style="padding:6px 10px;font-size:12px;" wire:click="markLost('{{ $issue->id }}')" wire:confirm="এই আইটেমটা হারিয়ে গেছে বলে চিহ্নিত করবেন?">হারিয়ে গেছে</button>
                                </div>
                            @elseif ($issue->status === 'returned')
                                <span class="pill active">ফেরত দেওয়া হয়েছে</span>
                            @else
                                <span class="pill due">হারানো</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো রেকর্ড পাওয়া যায়নি</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($showModal)
        <div class="modal-overlay" wire:click.self="$set('showModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>নতুন ইস্যু</h3>
                    <button class="modal-close" wire:click="$set('showModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="field">
                    <label>আইটেম <span class="req">*</span></label>
                    <select wire:model="itemId">
                        <option value="">নির্বাচন করুন</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->quantity_available }} উপলব্ধ)</option>
                        @endforeach
                    </select>
                    @error('itemId') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="row-2">
                    <div class="field">
                        <label>পরিমাণ <span class="req">*</span></label>
                        <input type="number" min="1" wire:model="quantity">
                        @error('quantity') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    </div>
                    <div class="field"><label>প্রত্যাশিত ফেরতের তারিখ <span class="opt">(ঐচ্ছিক)</span></label><input type="date" wire:model="expectedReturnAt"></div>
                </div>
                <div class="field">
                    <label>কার কাছে/কোথায় যাচ্ছে <span class="req">*</span></label>
                    <input type="text" wire:model="issuedTo" placeholder="যেমন: রহিম স্যার / ক্লাস ৮ম রুম">
                    @error('issuedTo') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="issue" type="button">ইস্যু করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>
