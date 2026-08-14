<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">পরীক্ষা ও ফলাফল / Result Weighting</div>
            <h2>Result Weighting</h2>
            <p>একটা পরীক্ষার ফলাফলে অন্য পরীক্ষার নম্বর কীভাবে যোগ হবে তা নিয়ন্ত্রণ করুন (যেমনঃ বার্ষিক পরীক্ষায় ক্লাস টেস্টের নম্বর একটা অংশ হিসেবে যোগ করা)</p>
        </div>
        <button class="btn-primary" wire:click="openModal" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            নতুন নিয়ম
        </button>
    </div>

    <div class="alert-note" style="margin-bottom:16px;">
        <b>Scale:</b> উৎস পরীক্ষার প্রাপ্ত শতাংশ, "কনভার্টেড ম্যাক্স মার্কস" দিয়ে স্কেল করে লক্ষ্য পরীক্ষার নম্বরে যোগ হবে।
        <b>Percentage:</b> উৎস পরীক্ষা সরাসরি নির্দিষ্ট শতাংশ ওজন হিসেবে চূড়ান্ত নম্বরে যোগ হবে।
        একই Group Key থাকা একাধিক Scale-নিয়মের গড় নেওয়া হয়।
    </div>

    <div class="table-card">
        <table>
            <thead><tr><th>লক্ষ্য পরীক্ষা</th><th>উৎস পরীক্ষা</th><th>শ্রেণি</th><th>বিষয়</th><th>ধরন</th><th>মান</th><th></th></tr></thead>
            <tbody>
                @forelse ($weightings as $w)
                    <tr wire:key="rw-{{ $w->id }}">
                        <td>{{ $w->targetExam->name ?? '—' }}</td>
                        <td>{{ $w->sourceExam->name ?? '—' }}</td>
                        <td>{{ $w->schoolClass->full_label ?? 'সকল শ্রেণি' }}</td>
                        <td>{{ $w->subject->name ?? 'সকল বিষয়' }}</td>
                        <td><span class="pill day">{{ $w->contribution_type === 'scale' ? 'Scale' : 'Percentage' }}</span></td>
                        <td>{{ $w->contribution_type === 'scale' ? $w->converted_max_marks.' নম্বরে স্কেল' : $w->weight_percentage.'%' }}</td>
                        <td>
                            <div class="row-actions">
                                <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="edit('{{ $w->id }}')">সম্পাদনা</button>
                                <button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" wire:click="delete('{{ $w->id }}')" wire:confirm="মুছে ফেলতে চান?">মুছুন</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো weighting নিয়ম যুক্ত করা হয়নি</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($showModal)
        <div class="modal-overlay" wire:click.self="$set('showModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>{{ $editingId ? 'নিয়ম সম্পাদনা' : 'নতুন Weighting নিয়ম' }}</h3>
                    <button class="modal-close" wire:click="$set('showModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="field">
                        <label>লক্ষ্য পরীক্ষা (যেখানে যোগ হবে) <span class="req">*</span></label>
                        <select wire:model="targetExamId">
                            <option value="">নির্বাচন করুন</option>
                            @foreach ($exams as $e)
                                <option value="{{ $e->id }}">{{ $e->name }}</option>
                            @endforeach
                        </select>
                        @error('targetExamId') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    </div>
                    <div class="field">
                        <label>উৎস পরীক্ষা (যেখান থেকে আসবে) <span class="req">*</span></label>
                        <select wire:model="sourceExamId">
                            <option value="">নির্বাচন করুন</option>
                            @foreach ($exams as $e)
                                <option value="{{ $e->id }}">{{ $e->name }}</option>
                            @endforeach
                        </select>
                        @error('sourceExamId') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="field">
                        <label>শ্রেণি (ঐচ্ছিক)</label>
                        <select wire:model="classId">
                            <option value="">সকল শ্রেণি</option>
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->full_label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>বিষয় (ঐচ্ছিক)</label>
                        <select wire:model="subjectId">
                            <option value="">সকল বিষয়</option>
                            @foreach ($subjects as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="field">
                    <label>ধরন</label>
                    <select wire:model.live="contributionType">
                        <option value="percentage">Percentage</option>
                        <option value="scale">Scale</option>
                    </select>
                </div>
                @if ($contributionType === 'scale')
                    <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                        <div class="field">
                            <label>কনভার্টেড ম্যাক্স মার্কস <span class="req">*</span></label>
                            <input type="number" step="0.01" wire:model="convertedMaxMarks">
                            @error('convertedMaxMarks') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                        </div>
                        <div class="field"><label>Group Key (ঐচ্ছিক)</label><input type="text" wire:model="groupKey" placeholder="একাধিক নিয়ম গড় করতে চাইলে একই নাম দিন"></div>
                    </div>
                @else
                    <div class="field">
                        <label>ওজন (%) <span class="req">*</span></label>
                        <input type="number" step="0.01" min="0" max="100" wire:model="weightPercentage">
                        @error('weightPercentage') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    </div>
                @endif
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;margin-top:8px;">
                    <input type="checkbox" wire:model="requireSourcePass"> উৎস পরীক্ষায় ফেল করলে এই weighting থেকে পাশ ধরা যাবে না
                </label>
                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="save" type="button">সংরক্ষণ করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>
