<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">অর্থ ব্যবস্থাপনা / ফি স্ট্রাকচার</div>
            <h2>ফি স্ট্রাকচার সেটআপ</h2>
            <p>প্রতিটা শ্রেণির জন্য কোন ফি কত টাকা ও কত ঘনঘন ধার্য হবে তা নির্ধারণ করুন</p>
        </div>
        <button class="btn-primary" wire:click="openModal" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            নতুন ফি টেমপ্লেট
        </button>
    </div>

    <div class="table-card">
        <table>
            <thead><tr><th>ফি'র ধরন</th><th>শ্রেণি</th><th>পরিমাণ</th><th>ফ্রিকোয়েন্সি</th><th>স্ট্যাটাস</th><th>কার্যক্রম</th></tr></thead>
            <tbody>
                @forelse ($structures as $fs)
                    <tr wire:key="fs-{{ $fs->id }}">
                        <td>{{ $fs->fee_type }}</td>
                        <td>{{ $fs->schoolClass->full_label ?? 'সকল শ্রেণি' }}</td>
                        <td>৳{{ number_format($fs->amount, 0) }}</td>
                        <td>{{ match($fs->frequency) { 'monthly' => 'মাসিক', 'termly' => 'টার্মভিত্তিক', 'yearly' => 'বাৎসরিক', default => 'একবার' } }}</td>
                        <td>
                            <span class="pill {{ $fs->is_active ? 'active' : 'inactive' }}">{{ $fs->is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}</span>
                        </td>
                        <td>
                            <div class="row-actions">
                                <button wire:click="openModal('{{ $fs->id }}')" title="সম্পাদনা">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                </button>
                                <button wire:click="toggleActive('{{ $fs->id }}')" title="সক্রিয়/নিষ্ক্রিয় করুন">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 6 9 17l-5-5"/></svg>
                                </button>
                                <button wire:click="delete('{{ $fs->id }}')" wire:confirm="এই ফি টেমপ্লেট মুছে ফেলতে চান?" title="মুছুন">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m-8 0 1 12h8l1-12"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;color:var(--ink-soft);padding:30px 0;">এখনো কোনো ফি স্ট্রাকচার তৈরি করা হয়নি</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($showModal)
        <div class="modal-overlay" wire:click.self="$set('showModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>{{ $editingId ? 'ফি টেমপ্লেট সম্পাদনা' : 'নতুন ফি টেমপ্লেট' }}</h3>
                    <button class="modal-close" wire:click="$set('showModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="field">
                    <label>ফি'র ধরন <span class="req">*</span></label>
                    <input type="text" wire:model="feeType" placeholder="যেমন: মাসিক বেতন, ভর্তি ফি">
                    @error('feeType') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>

                <div class="field">
                    <label>শ্রেণি <span class="opt">— খালি রাখলে সব শ্রেণির জন্য প্রযোজ্য</span></label>
                    <select wire:model="classId">
                        <option value="">সকল শ্রেণি</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->full_label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="row-2">
                    <div class="field">
                        <label>পরিমাণ (৳) <span class="req">*</span></label>
                        <input type="number" step="0.01" wire:model="amount">
                        @error('amount') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label>ফ্রিকোয়েন্সি</label>
                        <select wire:model="frequency">
                            <option value="monthly">মাসিক</option>
                            <option value="termly">টার্মভিত্তিক</option>
                            <option value="yearly">বাৎসরিক</option>
                            <option value="one_time">একবার</option>
                        </select>
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
