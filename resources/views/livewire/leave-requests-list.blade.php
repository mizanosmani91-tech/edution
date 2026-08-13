<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">ছুটির আবেদন ও অনুমোদন</div>
            <h2 style="margin:0;">ছুটির আবেদন</h2>
        </div>
        <button class="btn-primary" wire:click="openModal" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            নতুন আবেদন
        </button>
    </div>

    <div class="stat-strip">
        <div class="stat-chip" style="--accent:var(--bad)">
            <div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg></div>
            <div><div class="sv">{{ $pendingCount }}</div><div class="sl">অপেক্ষমাণ আবেদন</div></div>
        </div>
        <div class="stat-chip" style="--accent:var(--good)">
            <div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20 6 9 17l-5-5"/></svg></div>
            <div><div class="sv">{{ $approvedThisMonth }}</div><div class="sl">এই মাসে অনুমোদিত</div></div>
        </div>
    </div>

    <div class="tabs-bar">
        <button type="button" class="tab-btn {{ $tab === 'pending' ? 'active' : '' }}" wire:click="$set('tab','pending')">অপেক্ষমাণ আবেদন</button>
        <button type="button" class="tab-btn {{ $tab === 'all' ? 'active' : '' }}" wire:click="$set('tab','all')">সকল আবেদন</button>
    </div>

    <div class="table-card">
        <table>
            <thead><tr>
                <th>আবেদনকারী</th><th>ধরন</th><th>তারিখ</th><th>মোট দিন</th><th>কারণ</th><th>স্ট্যাটাস</th><th>কার্যক্রম</th>
            </tr></thead>
            <tbody>
                @forelse ($leaves as $leave)
                    <tr wire:key="leave-{{ $leave->id }}">
                        <td>
                            <div class="stud">
                                <div class="ini">{{ mb_substr($leave->applicant_name, 0, 1) }}</div>
                                <div>
                                    <div class="name">{{ $leave->applicant_name }}</div>
                                    <div class="id">{{ $leave->applicant_type === 'teacher' ? 'শিক্ষক/স্টাফ' : 'শিক্ষার্থী' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ match($leave->leave_type) { 'sick' => 'অসুস্থতাজনিত', 'personal' => 'ব্যক্তিগত', 'maternity_paternity' => 'প্রসূতি/পিতৃত্বকালীন', 'family' => 'পারিবারিক', 'other' => 'অন্যান্য', default => 'নৈমিত্তিক' } }}</td>
                        <td>{{ $leave->date_from->format('d M') }} – {{ $leave->date_to->format('d M, Y') }}</td>
                        <td>{{ $leave->total_days }} দিন</td>
                        <td>{{ \Illuminate\Support\Str::limit($leave->reason, 40) }}</td>
                        <td>
                            <span class="pill {{ $leave->status === 'approved' ? 'active' : ($leave->status === 'rejected' ? 'due' : 'day') }}">
                                {{ match($leave->status) { 'approved' => 'অনুমোদিত', 'rejected' => 'প্রত্যাখ্যাত', default => 'অপেক্ষমাণ' } }}
                            </span>
                        </td>
                        <td>
                            @if ($leave->status === 'pending')
                                <div class="row-actions">
                                    <button wire:click="approve('{{ $leave->id }}')" class="btn-primary" style="padding:6px 12px;font-size:12.5px;">অনুমোদন</button>
                                    <button wire:click="reject('{{ $leave->id }}')" class="btn-ghost" style="padding:6px 12px;font-size:12.5px;">প্রত্যাখ্যান</button>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো আবেদন পাওয়া যায়নি</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- নতুন আবেদন মোডাল --}}
    @if ($showModal)
        <div class="modal-overlay" wire:click.self="$set('showModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>নতুন ছুটির আবেদন</h3>
                    <button class="modal-close" wire:click="$set('showModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="field">
                    <label>শিক্ষার্থী অথবা স্টাফের পক্ষে আবেদন করুন <span class="req">*</span></label>
                    <select wire:model="applicantType">
                        <option value="student">শিক্ষার্থী</option>
                        <option value="teacher">শিক্ষক/স্টাফ</option>
                    </select>
                </div>

                @if ($applicantType === 'student')
                    <div class="field">
                        <label>শিক্ষার্থী নির্বাচন করুন <span class="req">*</span></label>
                        <select wire:model="studentId">
                            <option value="">নাম নির্বাচন করুন</option>
                            @foreach ($students as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} — {{ $s->student_id_no }}</option>
                            @endforeach
                        </select>
                        @error('studentId') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    </div>
                @else
                    <div class="field">
                        <label>শিক্ষক/স্টাফ নির্বাচন করুন <span class="req">*</span></label>
                        <select wire:model="teacherId">
                            <option value="">নাম নির্বাচন করুন</option>
                            @foreach ($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} — {{ $t->designation }}</option>
                            @endforeach
                        </select>
                        @error('teacherId') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div class="field">
                    <label>ছুটির ধরন</label>
                    <select wire:model="leaveType">
                        <option value="casual">নৈমিত্তিক ছুটি</option>
                        <option value="sick">অসুস্থতাজনিত ছুটি</option>
                        <option value="personal">ব্যক্তিগত ছুটি</option>
                        <option value="maternity_paternity">প্রসূতি/পিতৃত্বকালীন ছুটি</option>
                        <option value="family">পারিবারিক কারণ</option>
                        <option value="other">অন্যান্য</option>
                    </select>
                </div>

                <div class="row-2">
                    <div class="field">
                        <label>শুরুর তারিখ <span class="req">*</span></label>
                        <input type="date" wire:model="dateFrom">
                        @error('dateFrom') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label>শেষ তারিখ <span class="req">*</span></label>
                        <input type="date" wire:model="dateTo">
                        @error('dateTo') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="field">
                    <label>কারণ <span class="req">*</span></label>
                    <textarea wire:model="reason" rows="3" placeholder="ছুটির কারণ লিখুন…"></textarea>
                    @error('reason') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>

                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="submit" type="button">আবেদন জমা দিন</button>
                </div>
            </div>
        </div>
    @endif
</div>
