<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">শিক্ষার্থী / প্রমোশন</div>
            <h2>শিক্ষার্থী প্রমোশন</h2>
        </div>
    </div>

    @if ($message)
        <div class="alert-note" style="margin-bottom:16px;">{{ $message }}</div>
    @endif

    <div class="select-card" style="margin-bottom:16px;">
        <div class="info-grid" style="grid-template-columns:repeat(4,1fr);gap:14px;">
            <div class="field" style="margin:0;">
                <label>বর্তমান ক্লাস</label>
                <select wire:model.live="fromClassId">
                    <option value="">ক্লাস নির্বাচন করুন</option>
                    @foreach ($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->full_label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="margin:0;">
                <label>বর্তমান শাখা</label>
                <select wire:model.live="fromSectionId">
                    <option value="">সব শাখা</option>
                    @foreach ($fromSections as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="margin:0;">
                <label>নতুন ক্লাস <span class="req">*</span></label>
                <select wire:model="toClassId">
                    <option value="">ক্লাস নির্বাচন করুন</option>
                    @foreach ($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->full_label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field" style="margin:0;">
                <label>নতুন শাখা</label>
                <select wire:model="toSectionId">
                    <option value="">নির্বাচন করুন</option>
                    @foreach ($toSections as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="list-head">
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;">
                <input type="checkbox" wire:model.live="selectAll"> সবাই নির্বাচন করুন
            </label>
            <span style="font-size:12.5px;color:var(--ink-soft);">{{ count($selected) }} জন নির্বাচিত / মোট {{ $students->count() }} জন</span>
        </div>
        <table>
            <thead><tr><th style="width:40px;"></th><th>নাম</th><th>আইডি নং</th><th>শাখা</th></tr></thead>
            <tbody>
                @forelse ($students as $st)
                    <tr wire:key="pr-{{ $st->id }}">
                        <td><input type="checkbox" value="{{ $st->id }}" wire:model.live="selected"></td>
                        <td>{{ $st->name }}</td>
                        <td>{{ $st->student_id_no }}</td>
                        <td>{{ $st->section->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center;color:var(--ink-soft);padding:30px 0;">ক্লাস নির্বাচন করলে শিক্ষার্থী তালিকা দেখাবে</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="att-save-bar">
        <span style="font-size:12.5px;color:var(--ink-soft);">নির্বাচিত শিক্ষার্থীদের নতুন ক্লাস/শাখায় স্থানান্তর করা হবে</span>
        <button class="btn-primary" wire:click="promote" type="button">প্রমোট করুন</button>
    </div>
</div>
