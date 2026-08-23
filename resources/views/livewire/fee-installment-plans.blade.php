<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">অর্থ ব্যবস্থাপনা</div>
            <h2 style="margin:0;">ফি কিস্তি প্ল্যান</h2>
        </div>
        <button class="btn-primary" wire:click="openModal" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            নতুন কিস্তি প্ল্যান
        </button>
    </div>

    <div class="info-box" style="margin-bottom:16px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
        বড় অঙ্কের ফি (যেমন ভর্তি ফি) কয়েক মাসে ভাগ করে নিতে চাইলে এখান থেকে প্ল্যান তৈরি করুন — প্রতিটা কিস্তি স্বয়ংক্রিয়ভাবে "ফি সংগ্রহ" তালিকায় আলাদা বকেয়া হিসেবে যোগ হয়ে যাবে, অভিভাবক পোর্টালেও দেখা যাবে।
    </div>

    <div class="table-card">
        <table>
            <thead><tr>
                <th>শিক্ষার্থী</th><th>ফি এর ধরন</th><th>মোট পরিমাণ</th><th>কিস্তি</th><th>পরিশোধিত</th><th>শুরু</th>
            </tr></thead>
            <tbody>
                @forelse ($plans as $plan)
                    <tr wire:key="plan-{{ $plan->id }}">
                        <td>
                            <div class="stud">
                                <div class="ini">{{ mb_substr($plan->student->name ?? '?', 0, 1) }}</div>
                                <div>
                                    <div class="name">{{ $plan->student->name ?? 'মুছে ফেলা হয়েছে' }}</div>
                                    <div class="id">{{ $plan->student->student_id_no ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $plan->fee_type }}</td>
                        <td>৳{{ number_format($plan->total_amount, 2) }}</td>
                        <td>
                            @php
                                $paidCount = $plan->installments->where('status', 'paid')->count();
                            @endphp
                            {{ $paidCount }}/{{ $plan->installments_count }} পরিশোধিত
                        </td>
                        <td>
                            @php
                                $paidAmount = $plan->installments->sum('amount_paid');
                            @endphp
                            ৳{{ number_format($paidAmount, 2) }} / ৳{{ number_format($plan->total_amount, 2) }}
                        </td>
                        <td>{{ $plan->start_month }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো কিস্তি প্ল্যান নেই</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:14px;">{{ $plans->links() }}</div>

    @if ($showModal)
        <div class="modal-overlay open" wire:click.self="$set('showModal', false)">
            <div class="modal-box" style="max-width:520px;">
                <div class="modal-head">
                    <div><h3>নতুন কিস্তি প্ল্যান</h3></div>
                    <button class="modal-close" type="button" wire:click="$set('showModal', false)">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="field">
                        <label>শিক্ষার্থী খুঁজুন</label>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="নাম বা আইডি দিয়ে খুঁজুন...">
                    </div>

                    @if ($selectedStudent)
                        <div class="info-box" style="background:rgba(108,92,231,.08);border-color:rgba(108,92,231,.3);margin-bottom:12px;">
                            নির্বাচিত: <strong>{{ $selectedStudent->name }}</strong> ({{ $selectedStudent->student_id_no }})
                        </div>
                    @elseif ($this->students->count())
                        <div style="max-height:160px;overflow-y:auto;border:1px solid var(--line);border-radius:10px;margin-bottom:12px;">
                            @foreach ($this->students as $s)
                                <div wire:click="selectStudent('{{ $s->id }}')" style="padding:8px 12px;cursor:pointer;border-bottom:1px solid var(--line);font-size:13px;" class="hover-row">
                                    {{ $s->name }} <span style="color:var(--ink-soft);">— {{ $s->student_id_no }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @error('selectedStudentId')<div class="err">{{ $message }}</div>@enderror

                    <div class="field"><label>ফি এর ধরন</label><input type="text" wire:model="feeType" placeholder="যেমন: ভর্তি ফি"></div>
                    @error('feeType')<div class="err">{{ $message }}</div>@enderror

                    <div class="field"><label>মোট পরিমাণ (৳)</label><input type="number" step="0.01" wire:model="totalAmount"></div>
                    @error('totalAmount')<div class="err">{{ $message }}</div>@enderror

                    <div class="field">
                        <label>কতগুলো কিস্তিতে ভাগ করবেন</label>
                        <select wire:model="installmentsCount">
                            @for ($i = 2; $i <= 12; $i++)
                                <option value="{{ $i }}">{{ $i }} কিস্তি</option>
                            @endfor
                        </select>
                    </div>
                    @error('installmentsCount')<div class="err">{{ $message }}</div>@enderror

                    <div class="field"><label>প্রথম কিস্তির মাস</label><input type="month" wire:model="startMonth"></div>
                    @error('startMonth')<div class="err">{{ $message }}</div>@enderror

                    <div class="field"><label>নোট (ঐচ্ছিক)</label><textarea wire:model="note" rows="2"></textarea></div>
                </div>
                <div class="modal-foot" style="justify-content:flex-end;">
                    <button class="btn-primary" type="button" wire:click="createPlan">প্ল্যান তৈরি করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>
