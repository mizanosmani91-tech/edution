<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">অর্থ ব্যবস্থাপনা / বৃত্তি ও মওকুফ</div>
            <h2>বৃত্তি ও মওকুফ</h2>
        </div>
        <button class="btn-primary" wire:click="openModal" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            নতুন বৃত্তি/মওকুফ
        </button>
    </div>

    <div class="alert-note" style="margin-bottom:16px;">
        এখানে বৃত্তি/মওকুফের রেকর্ড রাখা যাবে। বর্তমানে এটা ফি সংগ্রহ পেজে স্বয়ংক্রিয়ভাবে বকেয়া কমায় না — ফি সংগ্রহের সময় ম্যানুয়ালি এই তথ্য বিবেচনা করে বকেয়ার পরিমাণ সমন্বয় করতে হবে। ভবিষ্যতে চাইলে স্বয়ংক্রিয় প্রয়োগও যুক্ত করে দেওয়া যাবে।
    </div>

    <div class="select-card" style="margin-bottom:16px;">
        <select wire:model.live="statusFilter" style="max-width:200px;">
            <option value="active">সক্রিয়</option>
            <option value="revoked">বাতিলকৃত</option>
            <option value="">সব</option>
        </select>
    </div>

    <div class="table-card">
        <table>
            <thead><tr><th>শিক্ষার্থী</th><th>ধরন</th><th>পরিমাণ</th><th>কারণ</th><th>মেয়াদ</th><th>স্ট্যাটাস</th><th></th></tr></thead>
            <tbody>
                @forelse ($scholarships as $s)
                    <tr wire:key="sc-{{ $s->id }}">
                        <td>{{ $s->student->name ?? '—' }}</td>
                        <td>{{ $s->type_label }}</td>
                        <td>{{ $s->discount_mode === 'percentage' ? $s->discount_value.'%' : '৳'.number_format($s->discount_value, 0) }}</td>
                        <td style="max-width:220px;">{{ \Illuminate\Support\Str::limit($s->reason, 40) }}</td>
                        <td>{{ $s->valid_from?->format('d M, Y') ?? '—' }}@if($s->valid_to) – {{ $s->valid_to->format('d M, Y') }}@endif</td>
                        <td><span class="pill {{ $s->status === 'active' ? 'active' : 'due' }}">{{ $s->status === 'active' ? 'সক্রিয়' : 'বাতিল' }}</span></td>
                        <td>
                            <div class="row-actions">
                                @if ($s->status === 'active')
                                    <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="edit('{{ $s->id }}')">সম্পাদনা</button>
                                    <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="revoke('{{ $s->id }}')" wire:confirm="বাতিল করতে চান?">বাতিল করুন</button>
                                @endif
                                <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="delete('{{ $s->id }}')" wire:confirm="মুছে ফেলতে চান?">মুছুন</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো বৃত্তি/মওকুফ রেকর্ড পাওয়া যায়নি</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:14px;">{{ $scholarships->links() }}</div>

    @if ($showModal)
        <div class="modal-overlay" wire:click.self="$set('showModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>{{ $editingId ? 'সম্পাদনা' : 'নতুন বৃত্তি/মওকুফ' }}</h3>
                    <button class="modal-close" wire:click="$set('showModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="field">
                    <label>শিক্ষার্থী <span class="req">*</span></label>
                    <select wire:model="studentId">
                        <option value="">নির্বাচন করুন</option>
                        @foreach ($students as $st)
                            <option value="{{ $st->id }}">{{ $st->name }} ({{ $st->student_id_no }})</option>
                        @endforeach
                    </select>
                    @error('studentId') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="field">
                        <label>ধরন</label>
                        <select wire:model="type">
                            <option value="scholarship">বৃত্তি</option>
                            <option value="waiver">মওকুফ</option>
                            <option value="discount">ছাড়</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>হিসাবের ধরন</label>
                        <select wire:model="discountMode">
                            <option value="percentage">শতাংশ (%)</option>
                            <option value="fixed_amount">নির্দিষ্ট পরিমাণ (৳)</option>
                        </select>
                    </div>
                </div>
                <div class="field">
                    <label>পরিমাণ <span class="req">*</span></label>
                    <input type="number" step="0.01" wire:model="discountValue">
                    @error('discountValue') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="field"><label>কারণ</label><textarea wire:model="reason" rows="2"></textarea></div>
                <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="field"><label>শুরুর তারিখ</label><input type="date" wire:model="validFrom"></div>
                    <div class="field"><label>শেষের তারিখ (ঐচ্ছিক)</label><input type="date" wire:model="validTo"></div>
                </div>
                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="save" type="button">সংরক্ষণ করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>
