<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">শিক্ষক ও স্টাফ / Performance</div>
            <h2>Performance / মূল্যায়ন</h2>
        </div>
        <button class="btn-primary" wire:click="openModal" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            নতুন মূল্যায়ন
        </button>
    </div>

    <div class="table-card">
        <table>
            <thead><tr><th>স্টাফ</th><th>মেয়াদ</th><th>তারিখ</th><th>শিক্ষাদান</th><th>সময়ানুবর্তিতা</th><th>শৃঙ্খলা</th><th>সহযোগিতা</th><th>গড় স্কোর</th><th></th></tr></thead>
            <tbody>
                @forelse ($reviews as $r)
                    <tr wire:key="pv-{{ $r->id }}">
                        <td>{{ $r->teacher->name ?? '—' }}</td>
                        <td>{{ $r->review_period }}</td>
                        <td>{{ $r->review_date->format('d M, Y') }}</td>
                        <td>{{ $r->teaching_quality }}/৫</td>
                        <td>{{ $r->punctuality }}/৫</td>
                        <td>{{ $r->discipline }}/৫</td>
                        <td>{{ $r->cooperation }}/৫</td>
                        <td><span class="pill {{ $r->overall_score >= 4 ? 'active' : ($r->overall_score >= 2.5 ? 'day' : 'due') }}">{{ $r->overall_score }}/৫</span></td>
                        <td><button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="delete('{{ $r->id }}')" wire:confirm="মুছে ফেলতে চান?">মুছুন</button></td>
                    </tr>
                @empty
                    <tr><td colspan="9" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো মূল্যায়ন রেকর্ড পাওয়া যায়নি</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:14px;">{{ $reviews->links() }}</div>

    @if ($showModal)
        <div class="modal-overlay" wire:click.self="$set('showModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>নতুন Performance মূল্যায়ন</h3>
                    <button class="modal-close" wire:click="$set('showModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="field">
                    <label>স্টাফ <span class="req">*</span></label>
                    <select wire:model="teacherId">
                        <option value="">নির্বাচন করুন</option>
                        @foreach ($teachers as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                    @error('teacherId') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="field">
                        <label>মূল্যায়ন মেয়াদ <span class="req">*</span></label>
                        <input type="text" wire:model="reviewPeriod" placeholder="যেমনঃ ২০২৬ - প্রথম প্রান্তিক">
                        @error('reviewPeriod') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label>তারিখ <span class="req">*</span></label>
                        <input type="date" wire:model="reviewDate">
                    </div>
                </div>
                <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="field"><label>শিক্ষাদানের মান (১-৫)</label><input type="number" min="1" max="5" wire:model="teachingQuality"></div>
                    <div class="field"><label>সময়ানুবর্তিতা (১-৫)</label><input type="number" min="1" max="5" wire:model="punctuality"></div>
                    <div class="field"><label>শৃঙ্খলা (১-৫)</label><input type="number" min="1" max="5" wire:model="discipline"></div>
                    <div class="field"><label>সহযোগিতা (১-৫)</label><input type="number" min="1" max="5" wire:model="cooperation"></div>
                </div>
                <div class="field"><label>শক্তির জায়গা</label><textarea wire:model="strengths" rows="2"></textarea></div>
                <div class="field"><label>উন্নতির জায়গা</label><textarea wire:model="improvementAreas" rows="2"></textarea></div>
                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="save" type="button">সংরক্ষণ করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>
