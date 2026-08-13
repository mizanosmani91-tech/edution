<div>
    <div class="page-head">
        <div>
            <h2>বিষয় ও সিলেবাস</h2>
            <p>প্রতিষ্ঠানের সব বিষয় এখানে যোগ করুন</p>
        </div>
        <button class="btn-primary" wire:click="openCreateModal" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            নতুন বিষয় যোগ করুন
        </button>
    </div>

    <div class="table-card">
        <table>
            <thead><tr><th>বিষয়ের নাম</th><th>কোড</th><th>সিলেবাস নোট</th><th>কার্যক্রম</th></tr></thead>
            <tbody>
                @forelse ($subjects as $subject)
                    <tr wire:key="subject-{{ $subject->id }}">
                        <td style="font-weight:600;">{{ $subject->name }}</td>
                        <td>{{ $subject->code ?? '—' }}</td>
                        <td style="max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $subject->syllabus_note ?? '—' }}</td>
                        <td>
                            <div class="row-actions">
                                <button wire:click="openEditModal('{{ $subject->id }}')" type="button" title="সম্পাদনা">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                </button>
                                <button wire:click="delete('{{ $subject->id }}')" wire:confirm="এই বিষয় মুছে ফেলবেন?" type="button" title="মুছুন">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center;color:var(--ink-soft);padding:30px 0;">এখনো কোনো বিষয় যোগ করা হয়নি</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($showModal)
        <div class="modal-overlay" wire:click.self="$set('showModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>{{ $editingId ? 'বিষয় সম্পাদনা' : 'নতুন বিষয়' }}</h3>
                    <button class="modal-close" wire:click="$set('showModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="field">
                    <label>বিষয়ের নাম <span class="req">*</span></label>
                    <input type="text" wire:model="name" placeholder="যেমন: বাংলা">
                    @error('name') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>

                <div class="field">
                    <label>কোড <span class="opt">(ঐচ্ছিক)</span></label>
                    <input type="text" wire:model="code" placeholder="যেমন: BAN101">
                </div>

                <div class="field">
                    <label>সিলেবাস নোট <span class="opt">(ঐচ্ছিক)</span></label>
                    <textarea wire:model="syllabus_note" placeholder="বিষয়ের সংক্ষিপ্ত সিলেবাস বিবরণ"></textarea>
                </div>

                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="save" type="button">সংরক্ষণ করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>