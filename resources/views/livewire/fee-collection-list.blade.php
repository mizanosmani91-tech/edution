<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">অর্থ ব্যবস্থাপনা / ফি সংগ্রহ</div>
            <h2 style="margin:0;">ফি সংগ্রহ ও লেজার</h2>
        </div>
        <a href="{{ route('import.fees') }}" class="btn-ghost">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12M7 10l5 5 5-5"/><path d="M5 21h14"/></svg>
            পুরাতন ফি হিস্টোরি ইমপোর্ট
        </a>
    </div>

    <div class="filter-card">
        <div class="f-field">
            <label>স্ট্যাটাস</label>
            <select wire:model.live="statusFilter">
                <option value="">সব</option>
                <option value="due">বকেয়া</option>
                <option value="partial">আংশিক</option>
                <option value="paid">পরিশোধিত</option>
                <option value="overdue">ওভারডিউ</option>
            </select>
        </div>
        <div class="f-field">
            <label>মাস</label>
            <input type="month" wire:model.live="monthFilter">
        </div>
        <button class="f-reset" wire:click="$set('monthFilter','')">রিসেট</button>
    </div>

    <div class="table-card">
        <table>
            <thead><tr>
                <th>শিক্ষার্থী</th><th>মাস/ধরন</th><th>বাকি</th><th>স্ট্যাটাস</th><th>কার্যক্রম</th>
            </tr></thead>
            <tbody>
                @forelse ($fees as $fee)
                    <tr wire:key="fee-{{ $fee->id }}">
                        <td>
                            <div class="stud">
                                <div class="ini">{{ mb_substr($fee->student->name, 0, 1) }}</div>
                                <div>
                                    <div class="name">{{ $fee->student->name }}</div>
                                    <div class="id">{{ $fee->student->student_id_no }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $fee->due_month }} — {{ $fee->fee_type }}</td>
                        <td>৳{{ number_format($fee->due_amount, 2) }}</td>
                        <td>
                            <span class="pill {{ $fee->status === 'paid' ? 'active' : ($fee->status === 'partial' ? 'day' : 'due') }}">
                                {{ match($fee->status) { 'paid' => 'পরিশোধিত', 'partial' => 'আংশিক', 'overdue' => 'ওভারডিউ', default => 'বকেয়া' } }}
                            </span>
                        </td>
                        <td>
                            <div class="row-actions">
                                @if ($fee->status !== 'paid')
                                    <button wire:click="openPayModal('{{ $fee->id }}')" class="btn-primary" style="padding:6px 12px;font-size:12.5px;">পেমেন্ট নিন</button>
                                @else
                                    <a href="{{ route('fee-collections.receipt', $fee) }}" target="_blank" title="রশিদ দেখুন">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h9l3 3v17H6z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো রেকর্ড পাওয়া যায়নি</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="table-foot">
            <div>মোট {{ $fees->total() }} টি রেকর্ডের মধ্যে {{ $fees->firstItem() ?? 0 }}–{{ $fees->lastItem() ?? 0 }} টি দেখানো হচ্ছে</div>
            <div class="pager">{{ $fees->links() }}</div>
        </div>
    </div>

    {{-- পেমেন্ট এন্ট্রি মোডাল --}}
    @if ($payingId)
        <div class="modal-overlay" wire:click.self="$set('payingId', null)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>পেমেন্ট এন্ট্রি</h3>
                    <button class="modal-close" wire:click="$set('payingId', null)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="field">
                    <label>পরিমাণ <span class="req">*</span></label>
                    <input type="number" step="0.01" wire:model="payAmount" placeholder="৳">
                    @error('payAmount') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>

                <div class="field">
                    <label>পেমেন্ট মাধ্যম</label>
                    <select wire:model="payMethod">
                        <option value="cash">নগদ</option>
                        <option value="bkash">বিকাশ</option>
                        <option value="nagad">নগদ (মোবাইল ব্যাংকিং)</option>
                        <option value="bank_transfer">ব্যাংক ট্রান্সফার</option>
                    </select>
                </div>

                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('payingId', null)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="recordPayment" type="button">সেভ করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>
