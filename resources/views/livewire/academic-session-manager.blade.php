<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">একাডেমিক / সেশন</div>
            <h2>একাডেমিক সেশন</h2>
        </div>
        <button class="btn-primary" wire:click="openModal" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            নতুন সেশন
        </button>
    </div>

    <div class="table-card">
        <table>
            <thead><tr><th>নাম</th><th>শুরু</th><th>শেষ</th><th>স্ট্যাটাস</th><th></th></tr></thead>
            <tbody>
                @forelse ($sessions as $s)
                    <tr wire:key="as-{{ $s->id }}">
                        <td>{{ $s->name }}</td>
                        <td>{{ $s->start_date->format('d M, Y') }}</td>
                        <td>{{ $s->end_date->format('d M, Y') }}</td>
                        <td><span class="pill {{ $s->is_current ? 'active' : 'day' }}">{{ $s->is_current ? 'চলমান' : 'নিষ্ক্রিয়' }}</span></td>
                        <td>
                            <div class="row-actions">
                                @if (! $s->is_current)
                                    <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="setCurrent('{{ $s->id }}')">চলমান করুন</button>
                                @endif
                                <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="edit('{{ $s->id }}')">সম্পাদনা</button>
                                <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="delete('{{ $s->id }}')" wire:confirm="মুছে ফেলতে চান?">মুছুন</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো একাডেমিক সেশন তৈরি করা হয়নি</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($showModal)
        <div class="modal-overlay" wire:click.self="$set('showModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>{{ $editingId ? 'সেশন সম্পাদনা' : 'নতুন একাডেমিক সেশন' }}</h3>
                    <button class="modal-close" wire:click="$set('showModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="field">
                    <label>সেশনের নাম <span class="req">*</span></label>
                    <input type="text" wire:model="name" placeholder="যেমনঃ ২০২৬ শিক্ষাবর্ষ">
                    @error('name') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="field"><label>শুরুর তারিখ</label><input type="date" wire:model="startDate"></div>
                    <div class="field"><label>শেষের তারিখ</label><input type="date" wire:model="endDate"></div>
                </div>
                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="save" type="button">সংরক্ষণ করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>
