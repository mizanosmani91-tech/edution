@php
    $bnMonths = ['জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'];
    $dayNames = ['শনি','রবি','সোম','মঙ্গল','বুধ','বৃহঃ','শুক্র'];
    $bn = fn ($n) => strtr((string) $n, ['0'=>'০','1'=>'১','2'=>'২','3'=>'৩','4'=>'৪','5'=>'৫','6'=>'৬','7'=>'৭','8'=>'৮','9'=>'৯']);
    $typeColor = ['holiday' => 'var(--bad)', 'exam' => 'var(--gold)', 'event' => 'var(--good)', 'meeting' => 'var(--ink-soft)', 'other' => 'var(--ink-soft)'];
@endphp
<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">একাডেমিক / ক্যালেন্ডার</div>
            <h2 style="margin:0;">অ্যাকাডেমিক ক্যালেন্ডার</h2>
        </div>
        @if ($canManage)
            <button class="btn-primary" wire:click="openModal" type="button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
                নতুন ইভেন্ট / ছুটি
            </button>
        @endif
    </div>

    <div class="stat-strip">
        <div class="stat-chip" style="--accent:var(--good)">
            <div><div class="sv">{{ $bn($events->count()) }}</div><div class="sl">এই মাসের ইভেন্ট</div></div>
        </div>
        <div class="stat-chip" style="--accent:var(--bad)">
            <div><div class="sv">{{ $bn($offDayCount) }}</div><div class="sl">বন্ধের দিন</div></div>
        </div>
    </div>

    <div class="card" style="margin-bottom:16px;">
        <div class="card-head" style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
            <h3 style="margin:0;">{{ $bnMonths[$monthStart->month - 1] }} {{ $bn($monthStart->year) }}</h3>
            <div style="display:flex;gap:6px;">
                <button class="btn-ghost" wire:click="previousMonth" type="button" aria-label="আগের মাস">‹</button>
                <button class="btn-ghost" wire:click="today" type="button">আজ</button>
                <button class="btn-ghost" wire:click="nextMonth" type="button" aria-label="পরের মাস">›</button>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="info-table" style="table-layout:fixed;min-width:640px;width:100%;">
                <tr>
                    @foreach ($dayNames as $i => $name)
                        <td style="font-weight:600;text-align:center;{{ $i === 6 ? 'color:var(--bad);' : '' }}">{{ $name }}</td>
                    @endforeach
                </tr>
                @foreach ($weeks as $week)
                    <tr>
                        @foreach ($week as $day)
                            @php
                                $key = $day->toDateString();
                                $inMonth = $day->month === $monthStart->month;
                                $list = $byDay[$key] ?? [];
                                $isOff = collect($list)->contains(fn ($e) => $e->is_off_day);
                            @endphp
                            <td wire:key="day-{{ $key }}"
                                style="vertical-align:top;height:84px;padding:6px;{{ $inMonth ? '' : 'opacity:.4;' }}{{ $isOff ? 'background:color-mix(in srgb, var(--bad) 8%, transparent);' : '' }}{{ $key === now()->toDateString() ? 'outline:2px solid var(--good);outline-offset:-2px;' : '' }}">
                                <div style="font-size:12px;font-weight:600;display:flex;justify-content:space-between;">
                                    <span>{{ $bn($day->day) }}</span>
                                    @if ($canManage && $inMonth)
                                        <button type="button" wire:click="openModal('{{ $key }}')" title="এই দিনে যোগ করুন" style="opacity:.5;">+</button>
                                    @endif
                                </div>
                                @foreach (array_slice($list, 0, 2) as $e)
                                    <div style="font-size:11px;margin-top:3px;padding:1px 5px;border-radius:4px;border-left:3px solid {{ $typeColor[$e->type] ?? 'var(--ink-soft)' }};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;{{ $canManage ? 'cursor:pointer;' : '' }}"
                                         @if ($canManage) wire:click="edit('{{ $e->id }}')" @endif
                                         title="{{ $e->title }}">{{ $e->title }}</div>
                                @endforeach
                                @if (count($list) > 2)
                                    <div style="font-size:11px;color:var(--ink-soft);margin-top:2px;">+{{ $bn(count($list) - 2) }} টি</div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><div><h3>এই মাসের তালিকা</h3></div></div>
        @if ($events->isNotEmpty())
            <table class="info-table">
                <tr>
                    <td style="font-weight:600;">তারিখ</td>
                    <td style="font-weight:600;">শিরোনাম</td>
                    <td style="font-weight:600;">ধরন</td>
                    <td style="font-weight:600;">প্রাপক</td>
                    <td></td>
                </tr>
                @foreach ($events as $e)
                    <tr wire:key="ev-{{ $e->id }}">
                        <td>
                            {{ $bn($e->start_date->format('d/m/Y')) }}
                            @if (! $e->start_date->isSameDay($e->end_date))
                                – {{ $bn($e->end_date->format('d/m/Y')) }}
                            @endif
                        </td>
                        <td>
                            {{ $e->title }}
                            @if ($e->is_off_day)<span class="pill due" style="margin-left:6px;">বন্ধ</span>@endif
                        </td>
                        <td>{{ $e->type_label }}</td>
                        <td>{{ $e->audience_label }}</td>
                        <td>
                            @if ($canManage)
                                <div class="row-actions">
                                    <button wire:click="edit('{{ $e->id }}')" title="সম্পাদনা">✎</button>
                                    <button wire:click="delete('{{ $e->id }}')" wire:confirm="এটি মুছে ফেলতে চান?" title="মুছুন">
                                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m-8 0 1 12h8l1-12"/></svg>
                                    </button>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        @else
            <div class="empty-note">
                <div class="en-title">এই মাসে কোনো ইভেন্ট বা ছুটি নেই</div>
            </div>
        @endif
    </div>

    @if ($showModal)
        <div class="modal-overlay" wire:click.self="$set('showModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>{{ $editingId ? 'ইভেন্ট সম্পাদনা' : 'নতুন ইভেন্ট / ছুটি' }}</h3>
                    <button class="modal-close" wire:click="$set('showModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="field">
                    <label>শিরোনাম <span class="req">*</span></label>
                    <input type="text" wire:model="title" placeholder="যেমন: ঈদুল ফিতরের ছুটি">
                    @error('title') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>

                <div class="row-2">
                    <div class="field">
                        <label>ধরন</label>
                        <select wire:model.live="type">
                            @foreach (\App\Models\CalendarEvent::TYPES as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label class="check-pill" style="margin-top:22px;"><input type="checkbox" wire:model="isOffDay"> বন্ধের দিন</label>
                    </div>
                </div>

                <div class="row-2">
                    <div class="field">
                        <label>শুরুর তারিখ <span class="req">*</span></label>
                        <input type="date" wire:model="startDate">
                        @error('startDate') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label>শেষের তারিখ <span class="req">*</span></label>
                        <input type="date" wire:model="endDate">
                        @error('endDate') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="field">
                    <label>বিবরণ <span class="opt">— ঐচ্ছিক</span></label>
                    <textarea wire:model="description" rows="3"></textarea>
                </div>

                <div class="field">
                    <label>কাদের জন্য <span class="opt">— খালি রাখলে সকলের জন্য</span></label>
                    <div class="check-grid">
                        <label class="check-pill"><input type="checkbox" wire:model="audience" value="guardian"> অভিভাবক</label>
                        <label class="check-pill"><input type="checkbox" wire:model="audience" value="teacher"> শিক্ষক/স্টাফ</label>
                        <label class="check-pill"><input type="checkbox" wire:model="audience" value="student"> শিক্ষার্থী</label>
                    </div>
                </div>

                <div class="modal-actions" style="display:flex;gap:8px;justify-content:space-between;">
                    @if ($editingId)
                        <button class="btn-ghost" type="button" wire:click="delete('{{ $editingId }}')" wire:confirm="এটি মুছে ফেলতে চান?" style="color:var(--bad);">মুছুন</button>
                    @else
                        <span></span>
                    @endif
                    <div style="display:flex;gap:8px;">
                        <button class="btn-ghost" type="button" wire:click="$set('showModal', false)">বাতিল</button>
                        <button class="btn-primary" type="button" wire:click="save">সংরক্ষণ</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
