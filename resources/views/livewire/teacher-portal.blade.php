<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">আপনার নিজস্ব ড্যাশবোর্ড</div>
            <h2 style="margin:0;">শিক্ষক পোর্টাল</h2>
        </div>
    </div>

    <div class="tabs-bar">
        <button type="button" class="tab-btn {{ $activeTab === 'overview' ? 'active' : '' }}" wire:click="setTab('overview')">ওভারভিউ</button>
        <button type="button" class="tab-btn {{ $activeTab === 'leave' ? 'active' : '' }}" wire:click="setTab('leave')">ছুটির আবেদন</button>
        <button type="button" class="tab-btn {{ $activeTab === 'profile' ? 'active' : '' }}" wire:click="setTab('profile')">প্রোফাইল</button>
    </div>

    {{-- ================= ওভারভিউ ================= --}}
    @if ($activeTab === 'overview')

        {{-- চেক-ইন/চেক-আউট কার্ড --}}
        <div class="settings-section" style="margin-bottom:20px;">
            <h3>আজকের হাজিরা</h3>
            <p class="sub">
                @if ($institution->hasGeofence())
                    প্রতিষ্ঠানের নির্ধারিত এলাকার মধ্যে থেকেই চেক-ইন/চেক-আউট করা যাবে ({{ $institution->geofence_radius_meters }} মিটারের মধ্যে)
                @else
                    এডমিন এখনো প্রতিষ্ঠানের অবস্থান সেট করেননি — লোকেশন যাচাই ছাড়াই চেক-ইন/চেক-আউট করা যাচ্ছে
                @endif
            </p>

            @if ($checkInError)
                <div class="info-box" style="background:rgba(239,68,68,.08);border-color:rgba(239,68,68,.3);color:var(--bad);margin-bottom:14px;">{{ $checkInError }}</div>
            @endif
            @if ($checkInSuccess)
                <div class="info-box" style="background:rgba(16,185,129,.1);border-color:rgba(16,185,129,.3);color:var(--good);margin-bottom:14px;">{{ $checkInSuccess }}</div>
            @endif

            <div style="display:flex; gap:24px; flex-wrap:wrap; align-items:center;">
                <div>
                    <div style="font-size:12px;color:var(--ink-soft);">চেক-ইন</div>
                    <div style="font-size:16px;font-weight:700;">{{ $todayAttendance?->check_in ? $todayAttendance->check_in->format('h:i A') : '—' }}</div>
                </div>
                <div>
                    <div style="font-size:12px;color:var(--ink-soft);">চেক-আউট</div>
                    <div style="font-size:16px;font-weight:700;">{{ $todayAttendance?->check_out ? $todayAttendance->check_out->format('h:i A') : '—' }}</div>
                </div>
                <div style="flex:1;"></div>
                <button type="button" id="teacherCheckInBtn" class="btn-primary" {{ $todayAttendance?->check_in ? 'disabled' : '' }}>চেক-ইন করুন</button>
                <button type="button" id="teacherCheckOutBtn" class="btn-ghost" {{ (! $todayAttendance?->check_in || $todayAttendance?->check_out) ? 'disabled' : '' }}>চেক-আউট করুন</button>
            </div>
        </div>

        <h2 class="mb-3 font-medium text-gray-900" style="margin-top:24px;">আজকের রুটিন</h2>
        <div class="mb-6 space-y-2">
            @forelse ($todayRoutine as $period)
                <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-3">
                    <span class="font-medium text-gray-900">{{ $period->subject->name }}</span>
                    <span class="text-sm text-gray-500">{{ $period->start_time }}–{{ $period->end_time }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">আজ কোনো ক্লাস নেই।</p>
            @endforelse
        </div>

        <h2 class="mb-3 font-medium text-gray-900">মার্ক এন্ট্রি বাকি (unpublished exam)</h2>
        <div class="space-y-2">
            @forelse ($examSubjects as $es)
                <a href="{{ route('marks-entry.index') }}" class="flex items-center justify-between rounded-lg border border-gray-200 bg-white p-3">
                    <div>
                        <p class="font-medium text-gray-900">{{ $es->exam->name }}</p>
                        <p class="text-sm text-gray-500">{{ $es->schoolClass->full_label }}</p>
                    </div>
                    <span class="text-blue-600">→</span>
                </a>
            @empty
                <p class="text-sm text-gray-500">কোনো পেন্ডিং মার্ক এন্ট্রি নেই।</p>
            @endforelse
        </div>

        <script>
            (function () {
                function getLocation(cb) {
                    if (!navigator.geolocation) { cb(null, null); return; }
                    navigator.geolocation.getCurrentPosition(
                        (pos) => cb(pos.coords.latitude, pos.coords.longitude),
                        () => cb(null, null)
                    );
                }

                const inBtn = document.getElementById('teacherCheckInBtn');
                const outBtn = document.getElementById('teacherCheckOutBtn');

                if (inBtn) {
                    inBtn.addEventListener('click', function () {
                        inBtn.disabled = true;
                        getLocation((lat, lng) => {
                            @this.call('checkIn', lat ? String(lat) : null, lng ? String(lng) : null)
                                .catch(() => { inBtn.disabled = false; });
                        });
                    });
                }

                if (outBtn) {
                    outBtn.addEventListener('click', function () {
                        outBtn.disabled = true;
                        getLocation((lat, lng) => {
                            @this.call('checkOut', lat ? String(lat) : null, lng ? String(lng) : null)
                                .catch(() => { outBtn.disabled = false; });
                        });
                    });
                }
            })();
        </script>
    @endif

    {{-- ================= ছুটির আবেদন ================= --}}
    @if ($activeTab === 'leave')
        <div class="page-head">
            <div></div>
            <button class="btn-primary" wire:click="openLeaveModal" type="button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
                নতুন আবেদন
            </button>
        </div>

        <div class="table-card">
            <table>
                <thead><tr><th>ধরন</th><th>তারিখ</th><th>কারণ</th><th>স্ট্যাটাস</th></tr></thead>
                <tbody>
                    @forelse ($myLeaves as $leave)
                        <tr>
                            <td>{{ match($leave->leave_type) { 'sick' => 'অসুস্থতাজনিত', 'personal' => 'ব্যক্তিগত', 'maternity_paternity' => 'প্রসূতি/পিতৃত্বকালীন', 'family' => 'পারিবারিক', 'other' => 'অন্যান্য', default => 'নৈমিত্তিক' } }}</td>
                            <td>{{ $leave->date_from->format('d M') }} – {{ $leave->date_to->format('d M, Y') }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($leave->reason, 40) }}</td>
                            <td>
                                <span class="pill {{ $leave->status === 'approved' ? 'active' : ($leave->status === 'rejected' ? 'due' : 'day') }}">
                                    {{ match($leave->status) { 'approved' => 'অনুমোদিত', 'rejected' => 'প্রত্যাখ্যাত', default => 'অপেক্ষমাণ' } }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align:center;color:var(--ink-soft);">কোনো আবেদন নেই।</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($showLeaveModal)
            <div class="modal-overlay" wire:click.self="$set('showLeaveModal', false)">
                <div class="modal-box">
                    <div class="modal-head">
                        <h3>নতুন ছুটির আবেদন</h3>
                        <button class="modal-close" wire:click="$set('showLeaveModal', false)" type="button">✕</button>
                    </div>

                    <div class="field">
                        <label>ছুটির ধরন</label>
                        <select wire:model="leaveType">
                            <option value="casual">নৈমিত্তিক</option>
                            <option value="sick">অসুস্থতাজনিত</option>
                            <option value="personal">ব্যক্তিগত</option>
                            <option value="maternity_paternity">প্রসূতি/পিতৃত্বকালীন</option>
                            <option value="family">পারিবারিক</option>
                            <option value="other">অন্যান্য</option>
                        </select>
                    </div>
                    <div class="grid2">
                        <div class="field"><label>শুরুর তারিখ</label><input type="date" wire:model="leaveDateFrom">@error('leaveDateFrom')<div class="err">{{ $message }}</div>@enderror</div>
                        <div class="field"><label>শেষ তারিখ</label><input type="date" wire:model="leaveDateTo">@error('leaveDateTo')<div class="err">{{ $message }}</div>@enderror</div>
                    </div>
                    <div class="field">
                        <label>কারণ</label>
                        <textarea wire:model="leaveReason" rows="3"></textarea>
                        @error('leaveReason')<div class="err">{{ $message }}</div>@enderror
                    </div>

                    <div class="modal-foot">
                        <button class="btn-ghost" wire:click="$set('showLeaveModal', false)" type="button">বাতিল</button>
                        <button class="btn-primary" wire:click="submitLeave" type="button">জমা দিন</button>
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- ================= প্রোফাইল ================= --}}
    @if ($activeTab === 'profile')
        <div class="settings-section">
            <h3>প্রোফাইল ছবি</h3>
            <livewire:photo-upload
                model="App\Models\Teacher"
                :model-id="auth()->user()->teacher_id"
                category="teacher-photos"
                :current-url="$teacher?->photo_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($teacher->photo_path) : null"
                key="teacher-photo-upload"
            />
        </div>

        <div class="settings-section">
            <h3>যোগাযোগের তথ্য</h3>
            <div class="field" style="max-width:320px;">
                <label>মোবাইল নম্বর</label>
                <input type="text" wire:model="profilePhone" placeholder="01XXXXXXXXX">
                @error('profilePhone')<div class="err">{{ $message }}</div>@enderror
            </div>
            <button class="btn-primary" wire:click="saveProfile" type="button" style="margin-top:10px;">সংরক্ষণ করুন</button>
        </div>
    @endif
</div>
