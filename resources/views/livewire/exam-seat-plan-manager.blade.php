<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">পরীক্ষা ও ফলাফল / সিট প্ল্যান</div>
            <h2>পরীক্ষার সিট প্ল্যান</h2>
            <p>রুম/হল তৈরি করে স্বয়ংক্রিয়ভাবে ছাত্রদের সিট বিন্যাস করুন, প্রয়োজনে ম্যানুয়াল বদল করুন</p>
        </div>
    </div>

    <div class="cert-form-card lifecycle-page" style="margin-bottom:16px;">
        <div class="info-grid" style="grid-template-columns:1fr;">
            <div class="field">
                <label>পরীক্ষা নির্বাচন করুন</label>
                <select wire:model.live="examId">
                    <option value="">নির্বাচন করুন</option>
                    @foreach ($exams as $e)
                        <option value="{{ $e->id }}">{{ $e->name }} ({{ $e->academic_year }})</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if ($examId)
        <div class="cert-form-card lifecycle-page" style="margin-bottom:16px;">
            <h3 style="font-family:'Tiro Bangla',serif;font-size:16px;margin:0 0 14px;">রুম/হল যোগ করুন</h3>
            <form wire:submit="addRoom">
                <div class="info-grid" style="grid-template-columns:2fr 1fr auto;align-items:end;">
                    <div class="field">
                        <label>রুমের নাম</label>
                        <input type="text" wire:model="newRoomName" placeholder="যেমন: হল রুম ১ / ৩০১ নম্বর কক্ষ">
                    </div>
                    <div class="field">
                        <label>ধারণক্ষমতা</label>
                        <input type="number" wire:model="newRoomCapacity" min="1" max="500">
                    </div>
                    <button class="btn-primary" type="submit">রুম যোগ করুন</button>
                </div>
            </form>
        </div>

        <div class="cert-form-card lifecycle-page" style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <div>
                <b>মোট বসানো হয়েছে:</b> {{ $totalAssigned }} জন ছাত্র / {{ $rooms->sum('capacity') }} মোট আসন ({{ $rooms->count() }}টা রুম)
            </div>
            <div style="display:flex;gap:10px;">
                <button class="btn-primary" wire:click="generateSeatPlan" wire:confirm="পুরনো সিট বিন্যাস মুছে নতুন করে তৈরি করবেন?">স্বয়ংক্রিয়ভাবে সিট বিন্যাস তৈরি করুন</button>
                @if ($totalAssigned > 0)
                    <a class="btn-ghost" href="{{ route('exam-seat-plan.print', ['exam_id' => $examId]) }}" target="_blank">সিট প্ল্যান প্রিন্ট করুন (PDF)</a>
                @endif
            </div>
        </div>

        @forelse ($rooms as $room)
            <div class="cert-form-card lifecycle-page" style="margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                    <h3 style="font-family:'Tiro Bangla',serif;font-size:16px;margin:0;">{{ $room->room_name }} <span style="font-weight:400;font-size:12.5px;color:var(--ink-soft);">({{ $room->assignments->count() }}/{{ $room->capacity }} আসন পূর্ণ)</span></h3>
                    <button class="btn-ghost" style="border-color:var(--bad);color:var(--bad);" wire:click="deleteRoom('{{ $room->id }}')" wire:confirm="এই রুমটা ও এর সব সিট বিন্যাস মুছে ফেলা হবে, নিশ্চিত?">রুম মুছুন</button>
                </div>

                @if ($room->assignments->isEmpty())
                    <p style="color:var(--ink-soft);font-size:13px;">এখনো কোনো ছাত্র বসানো হয়নি।</p>
                @else
                    <table>
                        <thead><tr><th>সিট নং</th><th>ছাত্র</th><th>আইডি</th><th>শ্রেণি/শাখা</th><th>রুম বদলান</th></tr></thead>
                        <tbody>
                            @foreach ($room->assignments as $a)
                                <tr wire:key="assign-{{ $a->id }}">
                                    <td><b>{{ $a->seat_no }}</b></td>
                                    <td>{{ $a->student->name }}</td>
                                    <td>{{ $a->student->student_id_no }}</td>
                                    <td>{{ $a->student->schoolClass->full_label ?? '' }} {{ $a->student->section->name ?? '' }}</td>
                                    <td>
                                        <select onchange="if(this.value) { @this.call('moveStudent', '{{ $a->id }}', this.value); this.value=''; }">
                                            <option value="">অন্য রুমে সরান</option>
                                            @foreach ($rooms as $r2)
                                                @if ($r2->id !== $room->id)
                                                    <option value="{{ $r2->id }}">{{ $r2->room_name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @empty
            <p style="color:var(--ink-soft);font-size:13px;padding:20px 0;">এখনো কোনো রুম যোগ করা হয়নি।</p>
        @endforelse
    @endif
</div>
