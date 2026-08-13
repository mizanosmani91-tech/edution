<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">যোগাযোগ / অভিযোগ বাক্স</div>
            <h2>Complaint / Suggestion</h2>
        </div>
        <button class="btn-primary" wire:click="openModal" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            নতুন অভিযোগ/পরামর্শ
        </button>
    </div>

    <div class="stat-strip">
        <div class="stat-chip" style="--accent:var(--bad)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg></div><div><div class="sv">{{ $openCount }}</div><div class="sl">নতুন</div></div></div>
        <div class="stat-chip" style="--accent:var(--gold)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div><div><div class="sv">{{ $inProgressCount }}</div><div class="sl">প্রক্রিয়াধীন</div></div></div>
        <div class="stat-chip" style="--accent:var(--good)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20 6 9 17l-5-5"/></svg></div><div><div class="sv">{{ $resolvedCount }}</div><div class="sl">সমাধান হয়েছে</div></div></div>
    </div>

    <div class="tabs-bar">
        <button type="button" class="tab-btn {{ $tab === 'open' ? 'active' : '' }}" wire:click="$set('tab','open')">নতুন</button>
        <button type="button" class="tab-btn {{ $tab === 'in_progress' ? 'active' : '' }}" wire:click="$set('tab','in_progress')">প্রক্রিয়াধীন</button>
        <button type="button" class="tab-btn {{ $tab === 'resolved' ? 'active' : '' }}" wire:click="$set('tab','resolved')">সমাধান হয়েছে</button>
        <button type="button" class="tab-btn {{ $tab === 'all' ? 'active' : '' }}" wire:click="$set('tab','all')">সব</button>
    </div>

    <div class="table-card">
        <table>
            <thead><tr><th>বিষয়</th><th>ক্যাটাগরি</th><th>জমাদাতা</th><th>তারিখ</th><th>স্ট্যাটাস</th><th></th></tr></thead>
            <tbody>
                @forelse ($complaints as $c)
                    <tr wire:key="cx-{{ $c->id }}">
                        <td>{{ $c->subject }}</td>
                        <td>{{ match($c->category) { 'academic' => 'একাডেমিক', 'financial' => 'আর্থিক', 'staff' => 'স্টাফ সংক্রান্ত', 'facility' => 'সুযোগ-সুবিধা', default => 'সাধারণ' } }}</td>
                        <td>{{ $c->submittedBy->name ?? 'অজ্ঞাত' }}</td>
                        <td>{{ $c->created_at->format('d M, Y') }}</td>
                        <td>
                            <span class="pill {{ $c->status === 'resolved' ? 'active' : ($c->status === 'in_progress' ? 'day' : 'due') }}">
                                {{ match($c->status) { 'resolved' => 'সমাধান হয়েছে', 'in_progress' => 'প্রক্রিয়াধীন', default => 'নতুন' } }}
                            </span>
                        </td>
                        <td>
                            @if ($c->status !== 'resolved')
                                <div class="row-actions">
                                    @if ($c->status === 'open')
                                        <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="markInProgress('{{ $c->id }}')">গ্রহণ করুন</button>
                                    @endif
                                    <button class="btn-primary" style="padding:6px 12px;font-size:12.5px;" wire:click="openRespond('{{ $c->id }}')">উত্তর দিন</button>
                                </div>
                            @else
                                <span style="font-size:12px;color:var(--ink-soft);">{{ \Illuminate\Support\Str::limit($c->response, 40) }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো অভিযোগ/পরামর্শ পাওয়া যায়নি</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($showModal)
        <div class="modal-overlay" wire:click.self="$set('showModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>নতুন অভিযোগ/পরামর্শ</h3>
                    <button class="modal-close" wire:click="$set('showModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="field">
                    <label>ক্যাটাগরি</label>
                    <select wire:model="category">
                        <option value="general">সাধারণ</option>
                        <option value="academic">একাডেমিক</option>
                        <option value="financial">আর্থিক</option>
                        <option value="staff">স্টাফ সংক্রান্ত</option>
                        <option value="facility">সুযোগ-সুবিধা</option>
                    </select>
                </div>
                <div class="field">
                    <label>বিষয় <span class="req">*</span></label>
                    <input type="text" wire:model="subject">
                    @error('subject') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="field">
                    <label>বিস্তারিত <span class="req">*</span></label>
                    <textarea wire:model="description" rows="4"></textarea>
                    @error('description') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="submit" type="button">জমা দিন</button>
                </div>
            </div>
        </div>
    @endif

    @if ($respondingId)
        <div class="modal-overlay" wire:click.self="$set('respondingId', null)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>উত্তর দিন ও সমাধান করুন</h3>
                    <button class="modal-close" wire:click="$set('respondingId', null)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="field">
                    <label>উত্তর <span class="req">*</span></label>
                    <textarea wire:model="responseText" rows="4"></textarea>
                    @error('responseText') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('respondingId', null)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="resolve" type="button">সমাধান হিসেবে চিহ্নিত করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>
