<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">ইনভেন্টরি / আইটেম তালিকা</div>
            <h2>ইনভেন্টরি/অ্যাসেট</h2>
            <p>ফার্নিচার, ল্যাব যন্ত্রপাতি, ইলেকট্রনিক্স ইত্যাদির স্টক ও ইস্যু ট্র্যাকিং</p>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('inventory-issues.index') }}" class="btn-ghost">ইস্যু ও রিটার্ন</a>
            <button class="btn-primary" wire:click="openModal" type="button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
                নতুন আইটেম
            </button>
        </div>
    </div>

    <div class="filter-card">
        <div class="f-field f-search">
            <label>খুঁজুন</label>
            <div class="shell">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="আইটেমের নাম লিখুন…">
            </div>
        </div>
        <div class="f-field">
            <label>ক্যাটাগরি</label>
            <select wire:model.live="categoryFilter">
                <option value="">সকল ক্যাটাগরি</option>
                @foreach ($categories as $c)
                    <option value="{{ $c }}">{{ $c }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="table-card">
        <table>
            <thead><tr><th>নাম</th><th>ক্যাটাগরি</th><th>ট্যাগ</th><th>মোট</th><th>উপলব্ধ</th><th>অবস্থা</th><th>অবস্থান</th><th>কার্যক্রম</th></tr></thead>
            <tbody>
                @forelse ($items as $item)
                    <tr wire:key="item-{{ $item->id }}">
                        <td style="font-weight:600;">{{ $item->name }}</td>
                        <td>{{ $item->category ?? '—' }}</td>
                        <td style="font-size:12px;">{{ $item->asset_tag ?? '—' }}</td>
                        <td>{{ $item->quantity_total }} {{ $item->unit }}</td>
                        <td>
                            <span class="pill {{ $item->quantity_available > 0 ? 'active' : 'due' }}">{{ $item->quantity_available }}</span>
                        </td>
                        <td>
                            <span class="pill {{ match($item->condition) { 'good' => 'active', 'fair' => 'day', default => 'due' } }}">
                                {{ match($item->condition) { 'good' => 'ভালো', 'fair' => 'মাঝারি', 'damaged' => 'ক্ষতিগ্রস্ত', 'lost' => 'হারানো', default => $item->condition } }}
                            </span>
                        </td>
                        <td style="font-size:12.5px;">{{ $item->location ?? '—' }}</td>
                        <td>
                            <div class="row-actions">
                                <button wire:click="openModal('{{ $item->id }}')" title="সম্পাদনা">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                </button>
                                <button wire:click="delete('{{ $item->id }}')" wire:confirm="আইটেমটি মুছে ফেলতে চান?" title="মুছুন">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m-8 0 1 12h8l1-12"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="text-align:center;color:var(--ink-soft);padding:30px 0;">এখনো কোনো আইটেম যোগ করা হয়নি</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($showModal)
        <div class="modal-overlay" wire:click.self="$set('showModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>{{ $editingId ? 'আইটেম সম্পাদনা' : 'নতুন আইটেম' }}</h3>
                    <button class="modal-close" wire:click="$set('showModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="field">
                    <label>নাম <span class="req">*</span></label>
                    <input type="text" wire:model="name" placeholder="যেমন: বেঞ্চ, প্রজেক্টর, মাইক্রোস্কোপ">
                    @error('name') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="row-2">
                    <div class="field"><label>ক্যাটাগরি</label><input type="text" wire:model="category" placeholder="যেমন: ফার্নিচার, ল্যাব যন্ত্রপাতি"></div>
                    <div class="field"><label>অ্যাসেট ট্যাগ/কোড <span class="opt">(ঐচ্ছিক)</span></label><input type="text" wire:model="assetTag"></div>
                </div>
                <div class="row-2">
                    <div class="field">
                        <label>মোট পরিমাণ <span class="req">*</span></label>
                        <input type="number" min="1" wire:model="quantityTotal">
                        @error('quantityTotal') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    </div>
                    <div class="field"><label>একক</label><input type="text" wire:model="unit" placeholder="পিস / সেট / বক্স"></div>
                </div>
                <div class="row-2">
                    <div class="field"><label>ক্রয়ের তারিখ <span class="opt">(ঐচ্ছিক)</span></label><input type="date" wire:model="purchaseDate"></div>
                    <div class="field"><label>ক্রয়মূল্য <span class="opt">(ঐচ্ছিক)</span></label><input type="number" step="0.01" wire:model="purchasePrice"></div>
                </div>
                <div class="row-2">
                    <div class="field">
                        <label>অবস্থা</label>
                        <select wire:model="condition">
                            <option value="good">ভালো</option>
                            <option value="fair">মাঝারি</option>
                            <option value="damaged">ক্ষতিগ্রস্ত</option>
                            <option value="lost">হারানো</option>
                        </select>
                    </div>
                    <div class="field"><label>অবস্থান <span class="opt">(ঐচ্ছিক)</span></label><input type="text" wire:model="location" placeholder="যেমন: ল্যাব রুম ২"></div>
                </div>
                <div class="field"><label>মন্তব্য <span class="opt">(ঐচ্ছিক)</span></label><textarea wire:model="remarks" rows="2"></textarea></div>
                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="save" type="button">সংরক্ষণ করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>
