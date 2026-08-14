<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">ভর্তি / {{ $view === 'test' ? 'পরীক্ষা-ইন্টারভিউ' : ($view === 'waiting' ? 'Waiting List' : 'আবেদন তালিকা') }}</div>
            <h2>{{ $view === 'test' ? 'ভর্তি পরীক্ষা / ইন্টারভিউ' : ($view === 'waiting' ? 'Waiting List' : 'ভর্তি আবেদন তালিকা') }}</h2>
        </div>
        @if ($view === 'all')
            <button class="btn-primary" wire:click="openModal" type="button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
                নতুন আবেদন
            </button>
        @endif
    </div>

    <div class="select-card" style="margin-bottom:16px;display:flex;gap:12px;flex-wrap:wrap;">
        @if ($view === 'all')
            <select wire:model.live="statusFilter" style="max-width:200px;">
                <option value="">সকল স্ট্যাটাস</option>
                <option value="pending">পর্যালোচনাধীন</option>
                <option value="test_scheduled">পরীক্ষা নির্ধারিত</option>
                <option value="shortlisted">শর্টলিস্টেড</option>
                <option value="waiting">অপেক্ষমাণ তালিকা</option>
                <option value="accepted">গৃহীত</option>
                <option value="rejected">বাতিল</option>
            </select>
        @endif
        <select wire:model.live="classFilter" style="max-width:220px;">
            <option value="">সকল শ্রেণি</option>
            @foreach ($classes as $c)
                <option value="{{ $c->id }}">{{ $c->full_label }}</option>
            @endforeach
        </select>
    </div>

    <div class="table-card">
        <table>
            <thead><tr><th>নাম</th><th>অভিভাবকের ফোন</th><th>আবেদনকৃত শ্রেণি</th><th>স্ট্যাটাস</th>@if($view==='test')<th>পরীক্ষার তারিখ</th><th>স্কোর</th>@endif<th></th></tr></thead>
            <tbody>
                @forelse ($applications as $a)
                    <tr wire:key="ap-{{ $a->id }}">
                        <td>{{ $a->applicant_name }}</td>
                        <td>{{ $a->guardian_phone }}</td>
                        <td>{{ $a->applyingClass->full_label ?? '—' }}</td>
                        <td><span class="pill {{ $a->status === 'accepted' ? 'active' : ($a->status === 'rejected' ? 'due' : 'day') }}">{{ $a->status_label }}</span></td>
                        @if ($view === 'test')
                            <td>{{ $a->test_date?->format('d M, Y') ?? '—' }}</td>
                            <td>{{ $a->test_score ?? '—' }}</td>
                        @endif
                        <td>
                            <div class="row-actions">
                                @if ($view === 'test')
                                    <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="openTestModal('{{ $a->id }}')">স্কোর/নোট</button>
                                    <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="setStatus('{{ $a->id }}', 'shortlisted')">শর্টলিস্ট</button>
                                    <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="setStatus('{{ $a->id }}', 'waiting')">অপেক্ষমাণ</button>
                                @endif
                                @if (! $a->converted_student_id && $a->status !== 'rejected')
                                    <button class="btn-primary" style="padding:6px 12px;font-size:12.5px;" wire:click="convertToStudent('{{ $a->id }}')" wire:confirm="এই আবেদনকারীকে শিক্ষার্থী হিসেবে ভর্তি করতে চান?">ভর্তি করুন</button>
                                @elseif ($a->converted_student_id)
                                    <span class="pill active" style="font-size:11px;">ভর্তি সম্পন্ন</span>
                                @endif
                                @if ($a->status !== 'rejected' && ! $a->converted_student_id)
                                    <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="setStatus('{{ $a->id }}', 'rejected')">বাতিল</button>
                                @endif
                                <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="delete('{{ $a->id }}')" wire:confirm="মুছে ফেলতে চান?">মুছুন</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো আবেদন পাওয়া যায়নি</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:14px;">{{ $applications->links() }}</div>

    @if ($showModal)
        <div class="modal-overlay" wire:click.self="$set('showModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>নতুন ভর্তি আবেদন</h3>
                    <button class="modal-close" wire:click="$set('showModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="field">
                    <label>আবেদনকারীর নাম <span class="req">*</span></label>
                    <input type="text" wire:model="applicantName">
                    @error('applicantName') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="field"><label>অভিভাবকের নাম</label><input type="text" wire:model="guardianName"></div>
                    <div class="field">
                        <label>অভিভাবকের ফোন <span class="req">*</span></label>
                        <input type="text" wire:model="guardianPhone">
                        @error('guardianPhone') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="field"><label>জন্ম তারিখ</label><input type="date" wire:model="dateOfBirth"></div>
                    <div class="field">
                        <label>লিঙ্গ</label>
                        <select wire:model="gender">
                            <option value="">নির্বাচন করুন</option>
                            <option value="male">ছেলে</option>
                            <option value="female">মেয়ে</option>
                        </select>
                    </div>
                </div>
                <div class="field">
                    <label>আবেদনকৃত শ্রেণি</label>
                    <select wire:model="applyingClassId">
                        <option value="">নির্বাচন করুন</option>
                        @foreach ($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->full_label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field"><label>পূর্ববর্তী বিদ্যালয়</label><input type="text" wire:model="previousSchool"></div>
                <div class="field"><label>ঠিকানা</label><textarea wire:model="address" rows="2"></textarea></div>
                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="save" type="button">সংরক্ষণ করুন</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showTestModal)
        <div class="modal-overlay" wire:click.self="$set('showTestModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>পরীক্ষা / ইন্টারভিউ তথ্য</h3>
                    <button class="modal-close" wire:click="$set('showTestModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="field"><label>পরীক্ষার তারিখ</label><input type="date" wire:model="testDate"></div>
                    <div class="field"><label>সময়</label><input type="time" wire:model="testTime"></div>
                </div>
                <div class="field"><label>প্রাপ্ত নম্বর</label><input type="number" step="0.01" wire:model="testScore"></div>
                <div class="field"><label>ইন্টারভিউ নোট</label><textarea wire:model="interviewNotes" rows="3"></textarea></div>
                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showTestModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="saveTest" type="button">সংরক্ষণ করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>
