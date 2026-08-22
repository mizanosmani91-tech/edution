<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">একাডেমিক / হিফজ অগ্রগতি</div>
            <h2>হিফজ/কুরআন অগ্রগতি</h2>
            <p>প্রতিদিনের সবক (নতুন পড়া), সবকি (সাম্প্রতিক রিভিশন) ও মঞ্জিল (পুরাতন রিভিশন) এন্ট্রি করুন</p>
        </div>
    </div>

    <div class="select-card">
        <div class="f-field">
            <label>শ্রেণি/মারহালা</label>
            <select wire:model.live="classId">
                <option value="">নির্বাচন করুন</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->full_label }}</option>
                @endforeach
            </select>
        </div>
        <div class="f-field">
            <label>শাখা</label>
            <select wire:model.live="sectionId" @if(!$classId) disabled @endif>
                <option value="">সব শাখা</option>
                @foreach ($sections as $section)
                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="f-field">
            <label>তারিখ</label>
            <input type="date" wire:model.live="date">
        </div>
    </div>

    @if ($classId)
        <div class="table-card" style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th style="min-width:130px;">ছাত্র</th>
                        <th style="min-width:110px;">সবক (পারা)</th>
                        <th style="min-width:130px;">সবক (রেঞ্জ)</th>
                        <th style="min-width:100px;">সবক মান</th>
                        <th style="min-width:130px;">সবকি (রেঞ্জ)</th>
                        <th style="min-width:100px;">সবকি মান</th>
                        <th style="min-width:130px;">মঞ্জিল (রেঞ্জ)</th>
                        <th style="min-width:100px;">মঞ্জিল মান</th>
                        <th style="min-width:150px;">মন্তব্য</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        <tr wire:key="hifz-{{ $student->id }}">
                            <td style="font-weight:600;">{{ $student->name }}<div class="sub" style="font-size:11px;">{{ $student->student_id_no }}</div></td>
                            <td><input type="text" wire:model="rows.{{ $student->id }}.sabak_para" placeholder="যেমন: পারা ৫" style="font-size:12px;width:100%;"></td>
                            <td><input type="text" wire:model="rows.{{ $student->id }}.sabak_range" placeholder="পৃ. ১০-১২" style="font-size:12px;width:100%;"></td>
                            <td>
                                <select wire:model="rows.{{ $student->id }}.sabak_quality" style="font-size:12px;width:100%;">
                                    <option value="">—</option>
                                    <option value="excellent">খুব ভালো</option>
                                    <option value="good">ভালো</option>
                                    <option value="weak">দুর্বল</option>
                                </select>
                            </td>
                            <td><input type="text" wire:model="rows.{{ $student->id }}.sabqi_range" placeholder="যেমন: পারা ৩-৪" style="font-size:12px;width:100%;"></td>
                            <td>
                                <select wire:model="rows.{{ $student->id }}.sabqi_quality" style="font-size:12px;width:100%;">
                                    <option value="">—</option>
                                    <option value="excellent">খুব ভালো</option>
                                    <option value="good">ভালো</option>
                                    <option value="weak">দুর্বল</option>
                                </select>
                            </td>
                            <td><input type="text" wire:model="rows.{{ $student->id }}.manzil_range" placeholder="যেমন: পারা ১-২" style="font-size:12px;width:100%;"></td>
                            <td>
                                <select wire:model="rows.{{ $student->id }}.manzil_quality" style="font-size:12px;width:100%;">
                                    <option value="">—</option>
                                    <option value="excellent">খুব ভালো</option>
                                    <option value="good">ভালো</option>
                                    <option value="weak">দুর্বল</option>
                                </select>
                            </td>
                            <td><input type="text" wire:model="rows.{{ $student->id }}.remarks" style="font-size:12px;width:100%;"></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" style="text-align:center;color:var(--ink-soft);padding:30px 0;">এই শ্রেণিতে কোনো সক্রিয় শিক্ষার্থী নেই</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="att-save-bar">
            <div class="info">মোট <b>{{ $students->count() }}</b> জন শিক্ষার্থী — শুধু যে সারিতে কিছু লেখা থাকবে সেটাই সংরক্ষণ হবে</div>
            <button type="button" class="btn-primary" wire:click="save">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>
                সংরক্ষণ করুন
            </button>
        </div>

        @if ($saved)
            <div style="position:fixed;bottom:90px;left:50%;transform:translateX(-50%);background:var(--ink);color:#fff;padding:10px 18px;border-radius:10px;font-size:13px;z-index:100;">হিফজ অগ্রগতি সংরক্ষিত হয়েছে</div>
        @endif
    @else
        <div class="roll-card">
            <div style="text-align:center;color:var(--ink-soft);padding:40px 20px;">শুরু করতে একটা শ্রেণি/মারহালা নির্বাচন করুন</div>
        </div>
    @endif
</div>
