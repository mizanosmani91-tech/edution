<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">যোগাযোগ / নোটিশ বোর্ড</div>
            <h2 style="margin:0;">নোটিশ ও ঘোষণা</h2>
        </div>
        <button class="btn-primary" wire:click="openModal" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            নতুন নোটিশ তৈরি করুন
        </button>
    </div>

    <div class="stat-strip">
        <div class="stat-chip" style="--accent:var(--good)">
            <div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5h16v11H8l-4 4V5Z"/></svg></div>
            <div><div class="sv">{{ $publishedThisMonth }}</div><div class="sl">এই মাসে প্রকাশিত</div></div>
        </div>
        <div class="stat-chip" style="--accent:var(--bad)">
            <div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg></div>
            <div><div class="sv">{{ $urgentCount }}</div><div class="sl">জরুরি নোটিশ</div></div>
        </div>
        <div class="stat-chip" style="--accent:var(--gold)">
            <div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M1.5 12S5 5 12 5s10.5 7 10.5 7-3.5 7-10.5 7S1.5 12 1.5 12Z"/><circle cx="12" cy="12" r="3"/></svg></div>
            <div><div class="sv">{{ number_format($totalViews) }}</div><div class="sl">মোট ভিউ</div></div>
        </div>
    </div>

    @if ($pinned->isNotEmpty())
        <div class="card" style="margin-bottom:16px;">
            <div class="card-head"><div><h3>পিন করা নোটিশ</h3></div></div>
            <div class="class-chip-list">
                @foreach ($pinned as $notice)
                    <div class="class-chip" wire:key="pinned-{{ $notice->id }}">
                        <div style="width:100%;">
                            <div style="display:flex;justify-content:space-between;align-items:start;gap:10px;">
                                <div class="cc-num">
                                    @if ($notice->is_urgent)<span class="pill due" style="margin-right:6px;">জরুরি</span>@endif
                                    {{ $notice->title }}
                                </div>
                                <button wire:click="togglePin('{{ $notice->id }}')" title="আনপিন করুন" style="flex-shrink:0;">
                                    <svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor" stroke="none"><path d="M12 2 9 9l-6 1 4.5 4L6 21l6-3.5L18 21l-1.5-7L21 10l-6-1-3-7Z"/></svg>
                                </button>
                            </div>
                            <div class="cc-sub">{{ \Illuminate\Support\Str::limit($notice->body, 120) }}</div>
                            <div class="cc-sub" style="margin-top:4px;">{{ $notice->publish_at->format('d M, Y') }} — {{ $notice->audience_label }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-head"><div><h3>সাম্প্রতিক নোটিশ</h3></div></div>
        @if ($recent->isNotEmpty())
            <table class="info-table">
                <tr>
                    <td style="font-weight:600;">শিরোনাম</td>
                    <td style="font-weight:600;">ক্যাটাগরি</td>
                    <td style="font-weight:600;">প্রাপক</td>
                    <td style="font-weight:600;">প্রকাশের তারিখ</td>
                    <td style="font-weight:600;"></td>
                </tr>
                @foreach ($recent as $notice)
                    <tr wire:key="recent-{{ $notice->id }}">
                        <td>
                            @if ($notice->is_urgent)<span class="pill due" style="margin-right:6px;">জরুরি</span>@endif
                            {{ $notice->title }}
                        </td>
                        <td>{{ match($notice->category) { 'academic' => 'একাডেমিক', 'finance' => 'অর্থ', 'event' => 'ইভেন্ট', 'urgent' => 'জরুরি', default => 'সাধারণ' } }}</td>
                        <td>{{ $notice->audience_label }}</td>
                        <td>{{ $notice->publish_at->format('d M, Y') }}</td>
                        <td>
                            <div class="row-actions">
                                <button wire:click="togglePin('{{ $notice->id }}')" title="নোটিশ বোর্ডে পিন করুন">
                                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2 9 9l-6 1 4.5 4L6 21l6-3.5L18 21l-1.5-7L21 10l-6-1-3-7Z"/></svg>
                                </button>
                                <button wire:click="delete('{{ $notice->id }}')" wire:confirm="নোটিশটি মুছে ফেলতে চান?" title="মুছুন">
                                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m-8 0 1 12h8l1-12"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </table>
        @else
            <div class="empty-note">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5h16v11H8l-4 4V5Z"/></svg>
                <div class="en-title">এখনো কোনো নোটিশ প্রকাশিত হয়নি</div>
            </div>
        @endif
    </div>

    {{-- নতুন নোটিশ মোডাল --}}
    @if ($showModal)
        <div class="modal-overlay" wire:click.self="$set('showModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>নতুন নোটিশ তৈরি করুন</h3>
                    <button class="modal-close" wire:click="$set('showModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="field">
                    <label>শিরোনাম <span class="req">*</span></label>
                    <input type="text" wire:model="title" placeholder="নোটিশের শিরোনাম">
                    @error('title') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>

                <div class="field">
                    <label>বিস্তারিত <span class="req">*</span></label>
                    <textarea wire:model="body" rows="4" placeholder="নোটিশের বিস্তারিত লিখুন…"></textarea>
                    @error('body') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>

                <div class="row-2">
                    <div class="field">
                        <label>ক্যাটাগরি</label>
                        <select wire:model="category">
                            <option value="general">সাধারণ</option>
                            <option value="academic">একাডেমিক</option>
                            <option value="finance">অর্থ</option>
                            <option value="event">ইভেন্ট</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>প্রকাশের তারিখ</label>
                        <input type="datetime-local" wire:model="publishAt">
                    </div>
                </div>

                <div class="field">
                    <label>প্রাপক (একাধিক নির্বাচন করা যাবে) <span class="opt">— খালি রাখলে সকলের জন্য</span></label>
                    <div class="check-grid">
                        <label class="check-pill"><input type="checkbox" wire:model="audience" value="guardian"> অভিভাবক</label>
                        <label class="check-pill"><input type="checkbox" wire:model="audience" value="teacher"> শিক্ষক/স্টাফ</label>
                        <label class="check-pill"><input type="checkbox" wire:model="audience" value="student"> শিক্ষার্থী</label>
                    </div>
                </div>

                <div class="field">
                    <label>মেয়াদ শেষ (ঐচ্ছিক)</label>
                    <input type="datetime-local" wire:model="expiresAt">
                </div>

                <div class="switch-row">
                    <div class="switch-label">নোটিশ বোর্ডে পিন করুন</div>
                    <label class="switch"><input type="checkbox" wire:model="isPinned"><span class="switch-track"></span></label>
                </div>
                <div class="switch-row">
                    <div class="switch-label">জরুরি নোটিশ হিসেবে চিহ্নিত করুন</div>
                    <label class="switch"><input type="checkbox" wire:model="isUrgent"><span class="switch-track"></span></label>
                </div>

                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="submit" type="button">নোটিশ প্রকাশ করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>
