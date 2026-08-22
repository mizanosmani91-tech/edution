<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">সাপোর্ট</div>
            <h2>সাপোর্ট টিকেট</h2>
        </div>
        <button class="btn-primary" wire:click="openModal" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            নতুন টিকেট
        </button>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1.3fr; gap:16px; align-items:start;">
        <div class="table-card">
            <table>
                <thead><tr><th>বিষয়</th><th>অগ্রাধিকার</th><th>স্ট্যাটাস</th><th>তারিখ</th></tr></thead>
                <tbody>
                    @forelse ($tickets as $t)
                        <tr wire:key="tk-{{ $t->id }}" wire:click="loadTicket('{{ $t->id }}')" style="cursor:pointer; {{ $activeTicket && $activeTicket->id === $t->id ? 'background:rgba(245,158,11,.06);' : '' }}">
                            <td>{{ $t->subject }}</td>
                            <td>{{ match($t->priority) { 'high' => 'জরুরি', 'low' => 'সাধারণ', default => 'মধ্যম' } }}</td>
                            <td><span class="pill {{ $t->status === 'resolved' ? 'active' : 'due' }}">{{ $t->status === 'resolved' ? 'সমাধান হয়েছে' : 'খোলা' }}</span></td>
                            <td>{{ $t->created_at->format('d M, Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো টিকেট নেই</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card">
            @if ($activeTicket)
                <h3>{{ $activeTicket->subject }}</h3>
                <p class="sub">অগ্রাধিকার: {{ match($activeTicket->priority) { 'high' => 'জরুরি', 'low' => 'সাধারণ', default => 'মধ্যম' } }} • স্ট্যাটাস: {{ $activeTicket->status === 'resolved' ? 'সমাধান হয়েছে' : 'খোলা' }}</p>

                <div style="display:flex; flex-direction:column; gap:10px; max-height:280px; overflow-y:auto; margin-bottom:14px;">
                    @foreach ($activeTicket->messages as $m)
                        <div style="background:{{ $m->sender_type === 'superadmin' ? 'rgba(245,158,11,.1)' : 'var(--paper-deep)' }}; border-radius:10px; padding:10px 12px; font-size:12.5px;">
                            <div style="font-weight:700; font-size:11.5px; margin-bottom:3px;">{{ $m->sender_name }} {{ $m->sender_type === 'superadmin' ? '(সাপোর্ট)' : '' }}</div>
                            {{ $m->body }}
                            <div style="font-size:10px; color:var(--ink-soft); margin-top:5px;">{{ $m->created_at->format('d M, Y — h:i A') }}</div>
                        </div>
                    @endforeach
                </div>

                @if ($activeTicket->status !== 'resolved')
                    <div class="field"><label>উত্তর লিখুন</label><textarea wire:model="replyBody" rows="3"></textarea></div>
                    <button class="btn-primary" wire:click="sendReply" type="button">উত্তর পাঠান</button>
                @endif
            @else
                <p style="color:var(--ink-soft); font-size:13px;">একটা টিকেট নির্বাচন করুন থ্রেড দেখতে</p>
            @endif
        </div>
    </div>

    @if ($showModal)
        <div class="modal-overlay open" wire:click.self="$set('showModal', false)">
            <div class="modal-box" style="max-width:520px;">
                <div class="modal-head">
                    <div><h3>নতুন সাপোর্ট টিকেট</h3></div>
                    <button class="modal-close" type="button" wire:click="$set('showModal', false)">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="field"><label>বিষয়</label><input type="text" wire:model="subject"></div>
                    @error('subject')<div class="err">{{ $message }}</div>@enderror
                    <div class="field"><label>অগ্রাধিকার</label>
                        <select wire:model="priority">
                            <option value="low">সাধারণ</option>
                            <option value="med">মধ্যম</option>
                            <option value="high">জরুরি</option>
                        </select>
                    </div>
                    <div class="field"><label>বিস্তারিত</label><textarea wire:model="body" rows="4"></textarea></div>
                    @error('body')<div class="err">{{ $message }}</div>@enderror
                </div>
                <div class="modal-foot" style="justify-content:flex-end;">
                    <button class="btn-primary" type="button" wire:click="submit">টিকেট জমা দিন</button>
                </div>
            </div>
        </div>
    @endif
</div>
