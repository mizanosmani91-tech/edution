<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">হোস্টেল</div>
            <h2>হোস্টেল ব্যবস্থাপনা</h2>
        </div>
        @if ($tab === 'rooms')
            <button class="btn-primary" wire:click="openRoomModal" type="button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
                নতুন রুম
            </button>
        @else
            <button class="btn-primary" wire:click="openAssignModal" type="button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
                সিট বরাদ্দ করুন
            </button>
        @endif
    </div>

    <div class="tabs-bar">
        <button type="button" class="tab-btn {{ $tab === 'rooms' ? 'active' : '' }}" wire:click="$set('tab','rooms')">রুম/সিট বরাদ্দ</button>
        <button type="button" class="tab-btn {{ $tab === 'fees' ? 'active' : '' }}" wire:click="$set('tab','fees')">হোস্টেল ফি</button>
    </div>

    @if ($tab === 'rooms')
        <div class="table-card">
            <table>
                <thead><tr><th>রুম নং</th><th>ধরন</th><th>ধারণক্ষমতা</th><th>বর্তমান বাসিন্দা</th><th>মাসিক ফি</th><th></th></tr></thead>
                <tbody>
                    @forelse ($rooms as $room)
                        <tr wire:key="room-{{ $room->id }}">
                            <td>{{ $room->room_no }}</td>
                            <td>{{ $room->room_type ?? '—' }}</td>
                            <td>{{ $room->capacity }}</td>
                            <td>
                                <span class="pill {{ $room->residents_count >= $room->capacity ? 'due' : 'active' }}">{{ $room->residents_count }}/{{ $room->capacity }}</span>
                            </td>
                            <td>৳{{ number_format($room->monthly_fee, 0) }}</td>
                            <td>
                                <div class="row-actions">
                                    <button wire:click="openRoomModal('{{ $room->id }}')" title="সম্পাদনা">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                    </button>
                                    <button wire:click="deleteRoom('{{ $room->id }}')" wire:confirm="রুমটি মুছে ফেলতে চান?" title="মুছুন">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m-8 0 1 12h8l1-12"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;color:var(--ink-soft);padding:30px 0;">এখনো কোনো রুম যোগ করা হয়নি</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div class="table-card">
            <table>
                <thead><tr><th>শিক্ষার্থী</th><th>রুম</th><th>চেক-ইন তারিখ</th><th>মাসিক ফি</th><th></th></tr></thead>
                <tbody>
                    @forelse ($residents as $r)
                        <tr wire:key="res-{{ $r->id }}">
                            <td>{{ $r->student->name }} — {{ $r->student->student_id_no }}</td>
                            <td>{{ $r->room->room_no }}</td>
                            <td>{{ $r->check_in_date->format('d M, Y') }}</td>
                            <td>৳{{ number_format($r->room->monthly_fee, 0) }}</td>
                            <td><button wire:click="checkout('{{ $r->id }}')" wire:confirm="এই শিক্ষার্থীকে চেক-আউট করতে চান?" title="চেক-আউট">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 6 6 18M6 6l12 12"/></svg>
                            </button></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো শিক্ষার্থী হোস্টেলে নেই</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="table-foot"><div style="font-size:12px;color:var(--ink-soft);">হোস্টেল ফি সংগ্রহের জন্য "ফি সংগ্রহ" মডিউলে fee_type = হোস্টেল ফি ব্যবহার করুন</div></div>
        </div>
    @endif

    @if ($showRoomModal)
        <div class="modal-overlay" wire:click.self="$set('showRoomModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>{{ $editingRoomId ? 'রুম সম্পাদনা' : 'নতুন রুম' }}</h3>
                    <button class="modal-close" wire:click="$set('showRoomModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="field"><label>রুম নং <span class="req">*</span></label><input type="text" wire:model="roomNo">
                    @error('roomNo') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="row-2">
                    <div class="field"><label>ধরন</label><input type="text" wire:model="roomType" placeholder="সাধারণ / এসি / ডাবল"></div>
                    <div class="field"><label>ধারণক্ষমতা <span class="req">*</span></label><input type="number" min="1" wire:model="capacity"></div>
                </div>
                <div class="field"><label>মাসিক ফি (৳)</label><input type="number" step="0.01" wire:model="monthlyFee"></div>
                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showRoomModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="saveRoom" type="button">সংরক্ষণ করুন</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showAssignModal)
        <div class="modal-overlay" wire:click.self="$set('showAssignModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>সিট বরাদ্দ করুন</h3>
                    <button class="modal-close" wire:click="$set('showAssignModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="field">
                    <label>শিক্ষার্থী <span class="req">*</span></label>
                    <select wire:model="studentId">
                        <option value="">নির্বাচন করুন</option>
                        @foreach ($students as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} — {{ $s->student_id_no }}</option>
                        @endforeach
                    </select>
                    @error('studentId') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="field">
                    <label>রুম <span class="req">*</span></label>
                    <select wire:model="roomId">
                        <option value="">নির্বাচন করুন</option>
                        @foreach ($allRooms as $r)
                            <option value="{{ $r->id }}">{{ $r->room_no }}</option>
                        @endforeach
                    </select>
                    @error('roomId') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showAssignModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="assign" type="button">বরাদ্দ করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>
