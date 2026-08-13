<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">লাইব্রেরি / বই তালিকা</div>
            <h2>বই তালিকা</h2>
        </div>
        <button class="btn-primary" wire:click="openModal" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            নতুন বই যোগ করুন
        </button>
    </div>

    <div class="filter-card">
        <div class="f-field f-search">
            <label>খুঁজুন</label>
            <div class="shell">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="বইয়ের নাম লিখুন…">
            </div>
        </div>
    </div>

    <div class="table-card">
        <table>
            <thead><tr><th>শিরোনাম</th><th>লেখক</th><th>ক্যাটাগরি</th><th>মোট কপি</th><th>উপলব্ধ</th><th>কার্যক্রম</th></tr></thead>
            <tbody>
                @forelse ($books as $book)
                    <tr wire:key="book-{{ $book->id }}">
                        <td>{{ $book->title }}</td>
                        <td>{{ $book->author ?? '—' }}</td>
                        <td>{{ $book->category ?? '—' }}</td>
                        <td>{{ $book->total_copies }}</td>
                        <td>
                            <span class="pill {{ $book->available_copies > 0 ? 'active' : 'due' }}">{{ $book->available_copies }}</span>
                        </td>
                        <td>
                            <div class="row-actions">
                                <button wire:click="openModal('{{ $book->id }}')" title="সম্পাদনা">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                </button>
                                <button wire:click="delete('{{ $book->id }}')" wire:confirm="বইটি মুছে ফেলতে চান?" title="মুছুন">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m-8 0 1 12h8l1-12"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;color:var(--ink-soft);padding:30px 0;">এখনো কোনো বই যোগ করা হয়নি</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($showModal)
        <div class="modal-overlay" wire:click.self="$set('showModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>{{ $editingId ? 'বই সম্পাদনা' : 'নতুন বই যোগ করুন' }}</h3>
                    <button class="modal-close" wire:click="$set('showModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="field">
                    <label>শিরোনাম <span class="req">*</span></label>
                    <input type="text" wire:model="title">
                    @error('title') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="row-2">
                    <div class="field"><label>লেখক</label><input type="text" wire:model="author"></div>
                    <div class="field"><label>ISBN</label><input type="text" wire:model="isbn"></div>
                </div>
                <div class="row-2">
                    <div class="field"><label>ক্যাটাগরি</label><input type="text" wire:model="category"></div>
                    <div class="field">
                        <label>মোট কপি <span class="req">*</span></label>
                        <input type="number" min="1" wire:model="totalCopies">
                        @error('totalCopies') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="save" type="button">সংরক্ষণ করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>
