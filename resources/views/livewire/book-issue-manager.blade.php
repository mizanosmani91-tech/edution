<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">লাইব্রেরি / ইস্যু ও রিটার্ন</div>
            <h2>বই ইস্যু ও রিটার্ন</h2>
            <p>বই ইস্যু ও ফেরত দেওয়ার হিসাব রাখুন — মেয়াদ পেরিয়ে গেলে স্বয়ংক্রিয়ভাবে জরিমানা হিসাব হবে</p>
        </div>
        <button class="btn-primary" wire:click="openModal" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            নতুন ইস্যু
        </button>
    </div>

    <div class="tabs-bar">
        <button type="button" class="tab-btn {{ $tab === 'issued' ? 'active' : '' }}" wire:click="$set('tab','issued')">ইস্যুকৃত</button>
        <button type="button" class="tab-btn {{ $tab === 'overdue' ? 'active' : '' }}" wire:click="$set('tab','overdue')">মেয়াদোত্তীর্ণ (জরিমানা)</button>
        <button type="button" class="tab-btn {{ $tab === 'returned' ? 'active' : '' }}" wire:click="$set('tab','returned')">ফেরত দেওয়া</button>
    </div>

    <div class="table-card">
        <table>
            <thead><tr><th>বই</th><th>গ্রহীতা</th><th>ইস্যুর তারিখ</th><th>ফেরতের তারিখ</th><th>জরিমানা</th><th>কার্যক্রম</th></tr></thead>
            <tbody>
                @forelse ($issues as $issue)
                    <tr wire:key="issue-{{ $issue->id }}">
                        <td>{{ $issue->book->title }}</td>
                        <td>{{ $issue->borrower_name }}</td>
                        <td>{{ $issue->issued_at->format('d M, Y') }}</td>
                        <td>{{ $issue->due_date->format('d M, Y') }}</td>
                        <td>
                            @if ($issue->fine_amount > 0)
                                <span class="pill due">৳{{ number_format($issue->fine_amount, 0) }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if ($issue->status !== 'returned')
                                <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="markReturned('{{ $issue->id }}')">ফেরত নিন</button>
                            @else
                                <span class="pill active">ফেরত দেওয়া হয়েছে</span>
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
                    <h3>নতুন বই ইস্যু</h3>
                    <button class="modal-close" wire:click="$set('showModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="field">
                    <label>বই <span class="req">*</span></label>
                    <select wire:model="bookId">
                        <option value="">নির্বাচন করুন</option>
                        @foreach ($books as $book)
                            <option value="{{ $book->id }}">{{ $book->title }} ({{ $book->available_copies }} উপলব্ধ)</option>
                        @endforeach
                    </select>
                    @error('bookId') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="field">
                    <label>শিক্ষার্থী <span class="req">*</span></label>
                    <select wire:model="studentId">
                        <option value="">নির্বাচন করুন</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}">{{ $student->name }} — {{ $student->student_id_no }}</option>
                        @endforeach
                    </select>
                    @error('studentId') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="field">
                    <label>ফেরতের তারিখ <span class="req">*</span></label>
                    <input type="date" wire:model="dueDate">
                </div>
                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="issue" type="button">ইস্যু করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>
