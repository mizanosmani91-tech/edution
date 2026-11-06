<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">সেটিংস / ব্যবহারকারী</div>
            <h2 style="margin:0;">ব্যবহারকারী ও লগইন</h2>
            <p class="sub" style="margin:4px 0 0;">প্রতিষ্ঠানের সবার লগইন এখানে। বন্ধ করলে ওই ব্যক্তি আর ঢুকতে পারবে না; ইতিহাস থেকে যায়।</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button class="btn-ghost" type="button" wire:click="openStudentModal">শিক্ষার্থীর লগইন তৈরি</button>
            <button class="btn-primary" type="button" wire:click="openAdminModal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
                নতুন অ্যাডমিন
            </button>
        </div>
    </div>

    @if ($notice)
        <div class="info-box" style="background:rgba(16,185,129,.1);border-color:rgba(16,185,129,.3);color:var(--good);margin-bottom:12px;">{{ $notice }}</div>
    @endif
    @if ($errorNotice)
        <div class="info-box" style="background:#FCE4E4;border-color:#D9534F;margin-bottom:12px;">{{ $errorNotice }}</div>
    @endif

    <div class="select-card" style="margin-bottom:16px;display:flex;gap:8px;flex-wrap:wrap;">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="নাম বা ইমেইল খুঁজুন" style="max-width:260px;">
        <select wire:model.live="roleFilter" style="max-width:180px;">
            <option value="">সব রোল</option>
            @foreach ($roles as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="table-card">
        <table>
            <thead><tr><th>নাম</th><th>লগইন ইমেইল</th><th>রোল</th><th>অবস্থা</th><th></th></tr></thead>
            <tbody>
                @forelse ($users as $u)
                    <tr wire:key="user-{{ $u->id }}">
                        <td>
                            {{ $u->name }}
                            @if ($u->id === $currentId)<span class="cc-sub">(আপনি)</span>@endif
                            @if ($u->must_change_password)<div class="cc-sub">প্রথম লগইনে পাসওয়ার্ড বদলাতে হবে</div>@endif
                        </td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $roles[$u->role] ?? $u->role }}</td>
                        <td>
                            @if ($u->is_active === false)
                                <span class="pill due">বন্ধ</span>
                            @else
                                <span class="pill active">চালু</span>
                            @endif
                        </td>
                        <td>
                            @if ($u->id !== $currentId)
                                <div class="row-actions">
                                    <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" type="button"
                                            wire:click="resetPassword('{{ $u->id }}')"
                                            wire:confirm="{{ $u->name }} এর জন্য নতুন অস্থায়ী পাসওয়ার্ড তৈরি করবেন? আগের পাসওয়ার্ড আর কাজ করবে না।">পাসওয়ার্ড রিসেট</button>
                                    <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" type="button"
                                            wire:click="toggleActive('{{ $u->id }}')"
                                            wire:confirm="{{ $u->is_active === false ? 'লগইন চালু করবেন?' : 'এই ব্যক্তির লগইন বন্ধ করবেন? সে আর ঢুকতে পারবে না।' }}">
                                        {{ $u->is_active === false ? 'চালু করুন' : 'বন্ধ করুন' }}
                                    </button>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো ব্যবহারকারী পাওয়া যায়নি</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:14px;">{{ $users->links() }}</div>

    {{-- নতুন অ্যাডমিন --}}
    @if ($showAdminModal)
        <div class="modal-overlay" wire:click.self="$set('showAdminModal', false)">
            <div class="modal-box">
                <div class="modal-head"><h3>নতুন অ্যাডমিন</h3></div>
                <div class="field">
                    <label>নাম <span class="req">*</span></label>
                    <input type="text" wire:model="adminName">
                    @error('adminName') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="field">
                    <label>ইমেইল (লগইনের জন্য) <span class="req">*</span></label>
                    <input type="email" wire:model="adminEmail">
                    @error('adminEmail') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <p class="hint">তৈরির পর একটা অস্থায়ী পাসওয়ার্ড একবার দেখানো হবে। অ্যাডমিন সব কিছু দেখতে ও বদলাতে পারেন — শুধু বিশ্বস্ত ব্যক্তিকে দিন।</p>
                <div class="modal-actions" style="display:flex;gap:8px;justify-content:flex-end;">
                    <button class="btn-ghost" type="button" wire:click="$set('showAdminModal', false)">বাতিল</button>
                    <button class="btn-primary" type="button" wire:click="createAdmin">তৈরি করুন</button>
                </div>
            </div>
        </div>
    @endif

    {{-- শিক্ষার্থীর লগইন --}}
    @if ($showStudentModal)
        <div class="modal-overlay" wire:click.self="$set('showStudentModal', false)">
            <div class="modal-box">
                <div class="modal-head"><h3>শিক্ষার্থীর লগইন তৈরি</h3></div>
                <div class="field">
                    <label>শিক্ষার্থী খুঁজুন (নাম বা আইডি)</label>
                    <input type="text" wire:model.live.debounce.300ms="studentSearch" placeholder="যেমন: রহিম বা STU-12345">
                </div>
                <div class="field">
                    <label>শিক্ষার্থী নির্বাচন <span class="req">*</span> <span class="opt">— যাদের এখনো লগইন নেই</span></label>
                    <select wire:model="studentId" size="6" style="height:auto;">
                        @foreach ($candidates as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} — {{ $c->student_id_no }}</option>
                        @endforeach
                    </select>
                    @error('studentId') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="modal-actions" style="display:flex;gap:8px;justify-content:flex-end;">
                    <button class="btn-ghost" type="button" wire:click="$set('showStudentModal', false)">বাতিল</button>
                    <button class="btn-primary" type="button" wire:click="createStudentLogin">লগইন তৈরি করুন</button>
                </div>
            </div>
        </div>
    @endif

    {{-- অস্থায়ী পাসওয়ার্ড (একবারই) --}}
    @if ($revealed)
        <div class="modal-overlay">
            <div class="modal-box">
                <div class="modal-head"><h3>লগইন তথ্য</h3></div>
                <div class="info-box" style="background:#FFF4E5;border-color:#F0B429;margin-bottom:12px;">
                    এই পাসওয়ার্ড <strong>আর কখনো দেখা যাবে না</strong>। এখনই ব্যক্তিকে জানান বা লিখে রাখুন। প্রথম লগইনে তাকে নতুন পাসওয়ার্ড দিতে হবে।
                </div>
                <table class="info-table">
                    <tr><td>নাম</td><td>{{ $revealed['name'] }}</td></tr>
                    <tr><td>লগইন ইমেইল</td><td><strong>{{ $revealed['email'] }}</strong></td></tr>
                    <tr><td>অস্থায়ী পাসওয়ার্ড</td><td><strong style="font-family:monospace;font-size:16px;">{{ $revealed['password'] }}</strong></td></tr>
                </table>
                @if ($smsResult)<p class="hint" style="margin-top:8px;">{{ $smsResult }}</p>@endif
                <div class="modal-actions" style="display:flex;gap:8px;justify-content:flex-end;margin-top:12px;">
                    @if (! empty($revealed['phone']))
                        <button class="btn-ghost" type="button" wire:click="sendCredentialsSms">SMS পাঠান ({{ $revealed['phone'] }})</button>
                    @endif
                    <button class="btn-primary" type="button" wire:click="closeReveal">বন্ধ করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>
