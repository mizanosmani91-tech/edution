<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">পরীক্ষা ও ফলাফল / প্রশ্নপত্র</div>
            <h2 style="margin:0;">প্রশ্নপত্র</h2>
            <p>শিক্ষক নিজের বিষয়ের প্রশ্ন লিখে জমা দেবেন, এডমিন রিভিউ করে অনুমোদন দিলে প্রিন্ট করা যাবে</p>
        </div>
        <button class="btn-primary" wire:click="openCreate" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            নতুন প্রশ্নপত্র
        </button>
    </div>

    <div class="table-card">
        <table>
            <thead><tr>
                <th>পরীক্ষা</th><th>শ্রেণি</th><th>বিষয়</th><th>লিখেছেন</th><th>স্ট্যাটাস</th><th>কার্যক্রম</th>
            </tr></thead>
            <tbody>
                @forelse ($papers as $paper)
                    <tr wire:key="qp-{{ $paper->id }}">
                        <td>{{ $paper->exam->name ?? '—' }}</td>
                        <td>{{ $paper->schoolClass->full_label ?? '—' }}</td>
                        <td>{{ $paper->subject->name ?? '—' }}</td>
                        <td>{{ $paper->creator->name ?? '—' }}</td>
                        <td>
                            @php
                                $statusColor = match($paper->status) { 'approved' => '#1a9d5c', 'submitted' => '#c98a1a', default => '#6B7280' };
                                $statusBg = match($paper->status) { 'approved' => 'rgba(26,157,92,.1)', 'submitted' => 'rgba(201,138,26,.1)', default => 'rgba(107,114,128,.1)' };
                            @endphp
                            <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;color:{{ $statusColor }};background:{{ $statusBg }};">
                                {{ $paper->status_label }}
                            </span>
                        </td>
                        <td style="display:flex;gap:8px;flex-wrap:wrap;">
                            @if ($paper->status !== 'approved')
                                <button class="btn-ghost" type="button" wire:click="openEdit('{{ $paper->id }}')">সম্পাদনা</button>
                            @endif
                            @if ($isAdmin && $paper->status === 'submitted')
                                <button class="btn-primary" type="button" wire:click="approve('{{ $paper->id }}')">অনুমোদন করুন</button>
                                <button class="btn-ghost" type="button" wire:click="sendBackToDraft('{{ $paper->id }}')">ফেরত পাঠান</button>
                            @endif
                            @if ($paper->status === 'approved')
                                <a class="btn-ghost" href="{{ route('question-papers.print', $paper->id) }}" target="_blank">প্রিন্ট (PDF)</a>
                                @if ($isAdmin)
                                    <button class="btn-ghost" type="button" wire:click="sendBackToDraft('{{ $paper->id }}')">খসড়ায় ফেরত</button>
                                @endif
                            @endif
                            <button class="btn-ghost" style="border-color:var(--bad);color:var(--bad);" type="button" wire:click="delete('{{ $paper->id }}')" wire:confirm="প্রশ্নপত্রটি মুছে ফেলা হবে, নিশ্চিত?">মুছুন</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;color:var(--ink-soft);padding:30px 0;">এখনো কোনো প্রশ্নপত্র নেই</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($showModal)
        <div class="modal-overlay open" wire:click.self="$set('showModal', false)">
            <div class="modal-box" style="max-width:720px;">
                <div class="modal-head">
                    <div><h3>{{ $editingId ? 'প্রশ্নপত্র সম্পাদনা' : 'নতুন প্রশ্নপত্র' }}</h3></div>
                    <button class="modal-close" type="button" wire:click="$set('showModal', false)">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                        <div class="field">
                            <label>পরীক্ষা</label>
                            <select wire:model="examId">
                                <option value="">নির্বাচন করুন</option>
                                @foreach ($exams as $e)
                                    <option value="{{ $e->id }}">{{ $e->name }} ({{ $e->academic_year }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>শ্রেণি</label>
                            <select wire:model="classId">
                                <option value="">নির্বাচন করুন</option>
                                @foreach ($classes as $c)
                                    <option value="{{ $c->id }}">{{ $c->full_label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>বিষয়</label>
                            <select wire:model="subjectId">
                                <option value="">নির্বাচন করুন</option>
                                @foreach ($subjects as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>সময়</label>
                            <input type="text" wire:model="durationText" placeholder="যেমন: ১ ঘন্টা">
                        </div>
                        <div class="field">
                            <label>পূর্ণমান</label>
                            <input type="number" wire:model="fullMarks" min="1">
                        </div>
                        <div class="field">
                            <label>শিরোনাম (ঐচ্ছিক)</label>
                            <input type="text" wire:model="title" placeholder="ফাঁকা রাখলে পরীক্ষা+বিষয়ের নাম বসবে">
                        </div>
                    </div>

                    <hr style="margin:16px 0;border:none;border-top:1px solid var(--line);">

                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                        <label style="font-weight:600;">প্রশ্নগুলো</label>
                        <button class="btn-ghost" type="button" wire:click="addItem">+ প্রশ্ন যোগ করুন</button>
                    </div>

                    @foreach ($items as $idx => $item)
                        <div style="border:1px solid var(--line);border-radius:10px;padding:12px;margin-bottom:10px;">
                            <div class="info-grid" style="grid-template-columns:2fr 1fr auto;align-items:end;margin-bottom:8px;">
                                <div class="field">
                                    <label>নির্দেশনা (যেমনঃ শব্দার্থ লিখ)</label>
                                    <input type="text" wire:model="items.{{ $idx }}.heading">
                                </div>
                                <div class="field">
                                    <label>নম্বর</label>
                                    <input type="number" step="0.5" wire:model="items.{{ $idx }}.marks">
                                </div>
                                <button class="btn-ghost" style="border-color:var(--bad);color:var(--bad);" type="button" wire:click="removeItem({{ $idx }})">মুছুন</button>
                            </div>
                            <div class="field">
                                <label>প্রশ্নের লেখা (বাংলা/আরবি)</label>
                                <textarea wire:model="items.{{ $idx }}.content" rows="3" dir="auto"></textarea>
                            </div>
                        </div>
                    @endforeach
                    @error('items') <div class="err">{{ $message }}</div> @enderror
                </div>
                <div class="modal-foot" style="justify-content:flex-end;gap:10px;">
                    <button class="btn-ghost" type="button" wire:click="save(false)">খসড়া সেভ করুন</button>
                    <button class="btn-primary" type="button" wire:click="save(true)">সেভ করে জমা দিন</button>
                </div>
            </div>
        </div>
    @endif
</div>
