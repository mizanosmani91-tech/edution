<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">আপনার নিজস্ব ড্যাশবোর্ড</div>
            <h2 style="margin:0;">অভিভাবক পোর্টাল</h2>
        </div>
    </div>

    @if ($children->isEmpty())
        <div class="settings-section" style="text-align:center;color:var(--ink-soft);padding:40px 0;">
            আপনার সাথে এখনো কোনো শিক্ষার্থী যুক্ত করা হয়নি। প্রতিষ্ঠানের অফিসে যোগাযোগ করুন।
        </div>
    @else
        {{-- মাল্টি-চাইল্ড সিলেক্টর --}}
        @if ($children->count() > 1)
            <div class="preset-chips" style="margin-bottom:16px;">
                <span class="lbl">সন্তান:</span>
                @foreach ($children as $c)
                    <button type="button" wire:click="selectChild('{{ $c->id }}')"
                        class="preset-chip" style="{{ $activeChildId === $c->id ? 'background:var(--cover-maroon);color:#fff;border-color:var(--cover-maroon);' : '' }}">
                        {{ $c->name }}
                    </button>
                @endforeach
            </div>
        @endif

        <div class="tabs-bar">
            <button type="button" class="tab-btn {{ $activeTab === 'overview' ? 'active' : '' }}" wire:click="setTab('overview')">ওভারভিউ</button>
            <button type="button" class="tab-btn {{ $activeTab === 'fees' ? 'active' : '' }}" wire:click="setTab('fees')">ফি</button>
            <button type="button" class="tab-btn {{ $activeTab === 'homework' ? 'active' : '' }}" wire:click="setTab('homework')">হোমওয়ার্ক</button>
            <button type="button" class="tab-btn {{ $activeTab === 'messages' ? 'active' : '' }}" wire:click="setTab('messages')">বার্তা @if($unreadCount > 0)<span class="cnt">{{ $unreadCount }}</span>@endif</button>
            <button type="button" class="tab-btn {{ $activeTab === 'notices' ? 'active' : '' }}" wire:click="setTab('notices')">নোটিশ</button>
            <button type="button" class="tab-btn {{ $activeTab === 'leave' ? 'active' : '' }}" wire:click="setTab('leave')">ছুটির আবেদন</button>
            <button type="button" class="tab-btn {{ $activeTab === 'profile' ? 'active' : '' }}" wire:click="setTab('profile')">প্রোফাইল</button>
        </div>

        @if ($child)

            {{-- ================= ওভারভিউ ================= --}}
            @if ($activeTab === 'overview')
                <div class="settings-section" style="margin-bottom:20px;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">
                        <div>
                            <h3>{{ $child->name }}</h3>
                            <p class="sub">{{ $child->schoolClass?->name ?? '—' }}@if($child->section), {{ $child->section->name }} শাখা @endif @if($child->student_id_no) • আইডি: {{ $child->student_id_no }} @endif</p>
                        </div>
                        <a href="{{ route('students.profile', $child->id) }}" class="btn-ghost">বিস্তারিত প্রোফাইল দেখুন</a>
                    </div>
                </div>

                <div class="kpi-grid" style="margin-bottom:20px;">
                    <div class="stat-chip">
                        <div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="m8.5 12 2.3 2.3L16 9.7"/></svg></div>
                        <div><div class="sv">{{ $attendanceSummary['present'] }}/{{ $attendanceSummary['total'] }}</div><div class="sl">গত ৩০ দিনের উপস্থিতি</div></div>
                    </div>
                    <div class="stat-chip">
                        <div class="sic" style="background:color-mix(in srgb, var(--bad) 14%, white);"><svg viewBox="0 0 24 24" fill="none" stroke="var(--bad)" stroke-width="1.7"><rect x="3.5" y="7" width="17" height="12" rx="2.5"/><path d="M3.5 11h17"/></svg></div>
                        <div><div class="sv">৳{{ number_format($totalDue, 0) }}</div><div class="sl">মোট বকেয়া ফি</div></div>
                    </div>
                    <div class="stat-chip">
                        <div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5h16v11H8l-4 4V5Z"/></svg></div>
                        <div><div class="sv">{{ $unreadCount }}</div><div class="sl">অপঠিত বার্তা</div></div>
                    </div>
                    <div class="stat-chip">
                        <div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 5v14M5 12h14"/></svg></div>
                        <div><div class="sv">{{ $leaveRequests->where('status','pending')->count() }}</div><div class="sl">অপেক্ষমাণ ছুটির আবেদন</div></div>
                    </div>
                </div>

                <div class="settings-section">
                    <h3>সাম্প্রতিক নোটিশ</h3>
                    @forelse ($notices->take(3) as $notice)
                        <div style="padding:10px 0;border-bottom:1px dashed var(--line);">
                            <div style="font-weight:700;font-size:13.5px;">{{ $notice->is_urgent ? '🔴 ' : '' }}{{ $notice->title }}</div>
                            <div style="font-size:12px;color:var(--ink-soft);">{{ $notice->publish_at?->format('d M, Y') }}</div>
                        </div>
                    @empty
                        <p class="sub">কোনো নোটিশ নেই।</p>
                    @endforelse
                </div>
            @endif

            {{-- ================= ফি ================= --}}
            @if ($activeTab === 'fees')
                <div class="table-card">
                    <table>
                        <thead><tr><th>মাস/ধরন</th><th>বকেয়া</th><th>পরিশোধিত</th><th>স্ট্যাটাস</th><th>কার্যক্রম</th></tr></thead>
                        <tbody>
                            @forelse ($feeCollections as $fee)
                                <tr wire:key="fee-{{ $fee->id }}">
                                    <td style="font-weight:600;">{{ $fee->due_month }} — {{ $fee->fee_type }}</td>
                                    <td>৳{{ number_format($fee->due_amount, 2) }}</td>
                                    <td>৳{{ number_format($fee->amount_paid, 2) }}</td>
                                    <td>
                                        <span class="pill {{ $fee->status === 'paid' ? 'active' : ($fee->status === 'partial' ? 'day' : 'due') }}">
                                            {{ match($fee->status) { 'paid' => 'পরিশোধিত', 'partial' => 'আংশিক', 'overdue' => 'মেয়াদোত্তীর্ণ', default => 'বকেয়া' } }}
                                        </span>
                                        @if ($fee->guardian_claim_status === 'pending')
                                            <span class="pill day" title="অফিসের যাচাইয়ের অপেক্ষায়">যাচাইয়ের অপেক্ষায়</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="row-actions">
                                            @if ($fee->status === 'paid')
                                                <a href="{{ route('fee-collections.receipt', $fee->id) }}" target="_blank" title="রশিদ দেখুন">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h9l3 3v17H6z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg>
                                                </a>
                                            @elseif ($fee->guardian_claim_status !== 'pending')
                                                <button wire:click="openPayModal('{{ $fee->id }}')" type="button" class="btn-primary" style="padding:6px 12px;font-size:12px;">পেমেন্ট জমা দিন</button>
                                            @else
                                                <span class="sub" style="font-size:11.5px;">অফিস নিশ্চিত করলে হালনাগাদ হবে</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" style="text-align:center;color:var(--ink-soft);padding:30px 0;">এই সন্তানের কোনো ফি রেকর্ড নেই</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="info-box" style="margin-top:14px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
                    "পেমেন্ট জমা দিন" চাপলে bKash/Nagad/ব্যাংক/নগদ যেভাবে পরিশোধ করেছেন তার তথ্য জমা দিতে পারবেন — প্রতিষ্ঠানের অফিস যাচাই করে নিশ্চিত করার পর তা বকেয়া থেকে বাদ যাবে।
                </div>

                @if ($payingFeeId)
                    <div class="modal-overlay" wire:click.self="closePayModal">
                        <div class="modal-box">
                            <div class="modal-head">
                                <h3>পেমেন্টের তথ্য জমা দিন</h3>
                                <button class="modal-close" wire:click="closePayModal" type="button">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div class="field">
                                <label>পরিমাণ <span class="req">*</span></label>
                                <input type="number" step="0.01" wire:model="claimAmount">
                                @error('claimAmount') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                            </div>
                            <div class="field">
                                <label>মাধ্যম <span class="req">*</span></label>
                                <select wire:model="claimMethod">
                                    <option value="bkash">বিকাশ</option>
                                    <option value="nagad">নগদ</option>
                                    <option value="bank_transfer">ব্যাংক ট্রান্সফার</option>
                                    <option value="cash">নগদ (হাতে হাতে)</option>
                                </select>
                            </div>
                            <div class="field">
                                <label>ট্রানজেকশন আইডি/রেফারেন্স <span class="opt">(ঐচ্ছিক)</span></label>
                                <input type="text" wire:model="claimRef" placeholder="যেমন: বিকাশ TrxID">
                            </div>
                            <div class="field">
                                <label>নোট <span class="opt">(ঐচ্ছিক)</span></label>
                                <textarea wire:model="claimNote" rows="2"></textarea>
                            </div>
                            <div class="modal-foot">
                                <button class="btn-ghost" wire:click="closePayModal" type="button">বাতিল</button>
                                <button class="btn-primary" wire:click="submitPaymentClaim" type="button">জমা দিন</button>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            {{-- ================= হোমওয়ার্ক ================= --}}
            @if ($activeTab === 'homework')
                <div class="table-card">
                    <table>
                        <thead><tr><th>শিরোনাম</th><th>বিষয়/শিক্ষক</th><th>জমার শেষ তারিখ</th><th>অবস্থা</th></tr></thead>
                        <tbody>
                            @forelse ($homeworks as $row)
                                <tr wire:key="hw-{{ $row['homework']->id }}">
                                    <td style="font-weight:600;">
                                        {{ $row['homework']->title }}
                                        @if ($row['homework']->description)
                                            <div class="sub" style="margin:4px 0 0;font-weight:400;">{{ \Illuminate\Support\Str::limit($row['homework']->description, 100) }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $row['homework']->subject?->name ?? '—' }} @if($row['homework']->teacher) — {{ $row['homework']->teacher->name }} @endif</td>
                                    <td>{{ $row['homework']->due_date->format('d M, Y') }}</td>
                                    <td>
                                        <span class="pill {{ match($row['status']) { 'done' => 'active', 'partial' => 'day', 'not_done' => 'due', default => 'inactive' } }}">
                                            {{ match($row['status']) { 'done' => 'সম্পন্ন হয়েছে', 'partial' => 'আংশিক সম্পন্ন', 'not_done' => 'সম্পন্ন হয়নি', default => 'এখনো যাচাই করা হয়নি' } }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো হোমওয়ার্ক নেই</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- ================= বার্তা ================= --}}
            @if ($activeTab === 'messages')
                <div class="settings-section">
                    <h3>{{ $child->name }}-এর সাথে সম্পর্কিত শিক্ষকদের সাথে যোগাযোগ করুন</h3>
                    <p class="sub">নিচের যেকোনো একজনকে বার্তা পাঠাতে ক্লিক করুন — সরাসরি চ্যাট পেজে নিয়ে যাবে।</p>

                    <div style="display:flex;flex-direction:column;gap:10px;margin-top:10px;">
                        {{-- ক্লাস শিক্ষক --}}
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:12px 14px;border:1px solid var(--line);border-radius:12px;">
                            <div>
                                <div style="font-weight:700;font-size:13.5px;">{{ $classTeacherName ?? 'ক্লাস শিক্ষক নির্ধারিত নেই' }}</div>
                                <div class="sub" style="margin:0;">ক্লাস শিক্ষক</div>
                            </div>
                            @if ($classTeacherUser)
                                <button type="button" class="btn-primary" style="padding:7px 14px;font-size:12.5px;" wire:click="startChat('{{ $classTeacherUser->id }}')">বার্তা পাঠান</button>
                            @else
                                <span class="pill day">পোর্টাল অ্যাকাউন্ট নেই</span>
                            @endif
                        </div>

                        {{-- বিষয় শিক্ষকরা --}}
                        @foreach ($subjectContacts as $row)
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:12px 14px;border:1px solid var(--line);border-radius:12px;">
                                <div>
                                    <div style="font-weight:700;font-size:13.5px;">{{ $row['teacher']->name }}</div>
                                    <div class="sub" style="margin:0;">বিষয় শিক্ষক — {{ $row['subjects'] ?: '—' }}</div>
                                </div>
                                @if ($row['user'])
                                    <button type="button" class="btn-primary" style="padding:7px 14px;font-size:12.5px;" wire:click="startChat('{{ $row['user']->id }}')">বার্তা পাঠান</button>
                                @else
                                    <span class="pill day">পোর্টাল অ্যাকাউন্ট নেই</span>
                                @endif
                            </div>
                        @endforeach

                        {{-- প্রধান শিক্ষক --}}
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:12px 14px;border:1px solid var(--line);border-radius:12px;">
                            <div>
                                <div style="font-weight:700;font-size:13.5px;">{{ $headmasterName ?? 'প্রধান শিক্ষক নির্ধারিত নেই' }}</div>
                                <div class="sub" style="margin:0;">প্রধান শিক্ষক</div>
                            </div>
                            @if ($headmasterUser)
                                <button type="button" class="btn-primary" style="padding:7px 14px;font-size:12.5px;" wire:click="startChat('{{ $headmasterUser->id }}')">বার্তা পাঠান</button>
                            @else
                                <span class="pill day">পোর্টাল অ্যাকাউন্ট নেই</span>
                            @endif
                        </div>
                    </div>

                    <div style="margin-top:16px;">
                        <a href="{{ route('chat.index') }}" class="btn-ghost">সব চ্যাট দেখুন</a>
                    </div>
                </div>
            @endif

            {{-- ================= নোটিশ ================= --}}
            @if ($activeTab === 'notices')
                <div class="table-card">
                    <table>
                        <thead><tr><th>শিরোনাম</th><th>তারিখ</th><th>ক্যাটাগরি</th></tr></thead>
                        <tbody>
                            @forelse ($notices as $notice)
                                <tr wire:key="notice-{{ $notice->id }}">
                                    <td style="font-weight:600;">
                                        @if ($notice->is_pinned) 📌 @endif
                                        @if ($notice->is_urgent) 🔴 @endif
                                        {{ $notice->title }}
                                        <div class="sub" style="margin:4px 0 0;font-weight:400;">{{ \Illuminate\Support\Str::limit($notice->body, 120) }}</div>
                                    </td>
                                    <td>{{ $notice->publish_at?->format('d M, Y') }}</td>
                                    <td>{{ $notice->category ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো নোটিশ নেই</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- ================= ছুটির আবেদন ================= --}}
            @if ($activeTab === 'leave')
                <div class="settings-section" style="margin-bottom:20px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                        <div>
                            <h3>{{ $child->name }}-এর ছুটির আবেদন</h3>
                            <p class="sub">আগাম বা পূর্ববর্তী তারিখের জন্যও আবেদন করা যাবে (যেমন — ছাত্র অসুস্থ থাকায় কয়েকদিন অনুপস্থিত ছিল)।</p>
                        </div>
                        <button type="button" class="btn-primary" wire:click="openLeaveModal">+ নতুন আবেদন</button>
                    </div>
                </div>

                <div class="table-card">
                    <table>
                        <thead><tr><th>ধরন</th><th>মেয়াদ</th><th>কারণ</th><th>স্ট্যাটাস</th></tr></thead>
                        <tbody>
                            @forelse ($leaveRequests as $lr)
                                <tr wire:key="leave-{{ $lr->id }}">
                                    <td>{{ match($lr->leave_type) { 'sick' => 'অসুস্থতা', 'personal' => 'ব্যক্তিগত', 'family' => 'পারিবারিক', default => 'সাধারণ' } }}</td>
                                    <td>{{ $lr->date_from->format('d M') }} – {{ $lr->date_to->format('d M') }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($lr->reason, 40) }}</td>
                                    <td>
                                        <span class="pill {{ match($lr->status) { 'approved' => 'active', 'rejected' => 'due', default => 'day' } }}">
                                            {{ match($lr->status) { 'approved' => 'অনুমোদিত', 'rejected' => 'প্রত্যাখ্যাত', default => 'অপেক্ষমাণ' } }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো ছুটির আবেদন নেই</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($showLeaveModal)
                    <div class="modal-overlay" wire:click.self="$set('showLeaveModal', false)">
                        <div class="modal-box">
                            <div class="modal-head">
                                <h3>নতুন ছুটির আবেদন</h3>
                                <button class="modal-close" wire:click="$set('showLeaveModal', false)" type="button">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div class="field">
                                <label>ধরন</label>
                                <select wire:model="leaveType">
                                    <option value="casual">সাধারণ</option>
                                    <option value="sick">অসুস্থতা</option>
                                    <option value="personal">ব্যক্তিগত</option>
                                    <option value="family">পারিবারিক</option>
                                    <option value="other">অন্যান্য</option>
                                </select>
                            </div>
                            <div class="grid2">
                                <div class="field">
                                    <label>শুরু <span class="req">*</span></label>
                                    <input type="date" wire:model="leaveFrom">
                                    @error('leaveFrom') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                                </div>
                                <div class="field">
                                    <label>শেষ <span class="req">*</span></label>
                                    <input type="date" wire:model="leaveTo">
                                    @error('leaveTo') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div class="field">
                                <label>কারণ <span class="req">*</span></label>
                                <textarea wire:model="leaveReason" rows="3" placeholder="ছুটির কারণ লিখুন..."></textarea>
                                @error('leaveReason') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                            </div>
                            <div class="modal-foot">
                                <button class="btn-ghost" wire:click="$set('showLeaveModal', false)" type="button">বাতিল</button>
                                <button class="btn-primary" wire:click="submitLeaveRequest" type="button">জমা দিন</button>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

        @endif

        {{-- ================= প্রোফাইল (child-independent) ================= --}}
        @if ($activeTab === 'profile')
            <div class="settings-section" style="margin-bottom:20px;">
                <h3>আমার প্রোফাইল</h3>
                <p class="sub">{{ auth()->user()->name }} — {{ auth()->user()->email }}</p>
                <div class="field" style="max-width:320px;">
                    <label>মোবাইল নম্বর</label>
                    <input type="text" wire:model="profilePhone" placeholder="যেমন: 01XXXXXXXXX">
                    @error('profilePhone') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <button type="button" class="btn-primary" wire:click="saveProfile" style="margin-top:10px;">সংরক্ষণ করুন</button>
            </div>

            <div class="settings-section">
                <h3>যুক্ত সন্তানসমূহ</h3>
                @foreach ($children as $c)
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px dashed var(--line);">
                        <div>
                            <div style="font-weight:700;font-size:13.5px;">{{ $c->name }}</div>
                            <div class="sub" style="margin:0;">{{ $c->schoolClass?->name ?? '—' }}@if($c->section), {{ $c->section->name }} শাখা @endif</div>
                        </div>
                        <a href="{{ route('students.profile', $c->id) }}" class="btn-ghost" style="padding:6px 12px;font-size:12px;">প্রোফাইল</a>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>
