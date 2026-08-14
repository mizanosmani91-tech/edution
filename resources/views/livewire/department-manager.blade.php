<div>
    <div class="page-head">
        <div>
            <h2>বিভাগ ব্যবস্থাপনা</h2>
            <p>প্রতিষ্ঠানের একাডেমিক বিভাগ (যেমন বিজ্ঞান, মানবিক) পরিচালনা করুন</p>
        </div>
        <button class="btn-primary" wire:click="openCreateModal" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            নতুন বিভাগ যোগ করুন
        </button>
    </div>

    @if (!$hasDepartments)
        <div class="info-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
            প্রথম বিভাগ যোগ করার সাথে সাথেই এটা ক্লাস তৈরির ফর্মে স্বয়ংক্রিয়ভাবে দেখাতে শুরু করবে — আলাদা করে কোনো সেটিংস চালু করা লাগবে না।
        </div>
    @endif

    <div class="table-card">
        <table>
            <thead><tr>
                <th>বিভাগের নাম</th><th>বাংলা নাম</th><th>ক্লাস সংখ্যা</th><th>কার্যক্রম</th>
            </tr></thead>
            <tbody>
                @forelse ($departments as $dept)
                    <tr wire:key="dept-{{ $dept->id }}">
                        <td style="font-weight:600;">{{ $dept->name }}</td>
                        <td>{{ $dept->name_bn ?? '—' }}</td>
                        <td>{{ $dept->classes_count }}</td>
                        <td>
                            <div class="row-actions">
                                <button wire:click="openEditModal('{{ $dept->id }}')" type="button" title="সম্পাদনা">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                </button>
                                <button wire:click="delete('{{ $dept->id }}')" wire:confirm="এই বিভাগ মুছে ফেলবেন?" type="button" title="মুছুন">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center;color:var(--ink-soft);padding:30px 0;">এখনো কোনো বিভাগ যোগ করা হয়নি</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($showModal)
        <div class="modal-overlay" wire:click.self="$set('showModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>{{ $editingId ? 'বিভাগ সম্পাদনা' : 'নতুন বিভাগ' }}</h3>
                    <button class="modal-close" wire:click="$set('showModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="field">
                    <label>বিভাগের নাম (ইংরেজি/সাধারণ) <span class="req">*</span></label>
                    <input type="text" wire:model="name" placeholder="যেমন: Science">
                    @error('name') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>

                <div class="field">
                    <label>বাংলা নাম <span class="opt">(ঐচ্ছিক)</span></label>
                    <input type="text" wire:model="name_bn" placeholder="যেমন: বিজ্ঞান">
                </div>

                <div class="field">
                    <label>ক্রম</label>
                    <input type="number" wire:model="display_order" min="1">
                </div>

                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="save" type="button">সংরক্ষণ করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>