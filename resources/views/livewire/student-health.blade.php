<div class="lifecycle-page">
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">শিক্ষার্থী / স্বাস্থ্য তথ্য</div>
            <h2>শিক্ষার্থী স্বাস্থ্য তথ্য</h2>
        </div>
    </div>

    <div class="lc-grid">
        <aside class="picker-card">
            <div class="picker-filters">
                <select wire:model.live="classId">
                    <option value="">সকল শ্রেণি</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->full_label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="picker-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" wire:model.live.debounce.400ms="search" placeholder="নাম বা আইডি লিখুন…">
            </div>
            <div class="stud-list">
                @forelse ($students as $student)
                    <div class="stud-row {{ $selectedStudentId === $student->id ? 'selected-preview' : '' }}" wire:click="selectStudent('{{ $student->id }}')" wire:key="pick-{{ $student->id }}">
                        <div class="ini">{{ mb_substr($student->name, 0, 1) }}</div>
                        <div class="info">
                            <div class="nm">{{ $student->name }}</div>
                            <div class="ds">{{ $student->student_id_no }}</div>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center;color:var(--ink-soft);padding:20px 0;font-size:13px;">কোনো শিক্ষার্থী পাওয়া যায়নি</div>
                @endforelse
            </div>
        </aside>

        <div>
            @if ($selected)
                @if ($savedMessage)
                    <div class="alert-note" style="margin-bottom:14px;">{{ $savedMessage }}</div>
                @endif
                <div class="cert-form-card">
                    <h3 style="font-family:'Tiro Bangla',serif;font-size:16px;margin:0 0 14px;">{{ $selected->name }} — স্বাস্থ্য তথ্য</h3>
                    <div class="info-grid" style="grid-template-columns:1fr 1fr 1fr;">
                        <div class="field"><label>উচ্চতা (সে.মি.)</label><input type="number" step="0.1" wire:model="heightCm"></div>
                        <div class="field"><label>ওজন (কেজি)</label><input type="number" step="0.1" wire:model="weightKg"></div>
                        <div class="field">
                            <label>রক্তের গ্রুপ</label>
                            <select wire:model="bloodGroup">
                                <option value="">নির্বাচন করুন</option>
                                @foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                                    <option value="{{ $bg }}">{{ $bg }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="field"><label>অ্যালার্জি</label><textarea wire:model="allergies" rows="2" placeholder="যেমনঃ ধুলাবালি, নির্দিষ্ট খাবার ইত্যাদি"></textarea></div>
                    <div class="field"><label>দীর্ঘমেয়াদী রোগ/অবস্থা</label><textarea wire:model="chronicConditions" rows="2" placeholder="যেমনঃ হাঁপানি, ডায়াবেটিস ইত্যাদি"></textarea></div>
                    <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                        <div class="field"><label>জরুরি যোগাযোগ (নাম)</label><input type="text" wire:model="emergencyContactName"></div>
                        <div class="field"><label>জরুরি যোগাযোগ (ফোন)</label><input type="text" wire:model="emergencyContactPhone"></div>
                    </div>
                    <div class="field"><label>সর্বশেষ স্বাস্থ্য পরীক্ষার তারিখ</label><input type="date" wire:model="lastCheckupDate"></div>
                    <div class="field"><label>অতিরিক্ত নোট</label><textarea wire:model="notes" rows="2"></textarea></div>
                    <button class="btn-primary" wire:click="save" type="button">সংরক্ষণ করুন</button>
                </div>
            @else
                <div class="picker-card" style="text-align:center;color:var(--ink-soft);padding:40px 20px;">স্বাস্থ্য তথ্য দেখতে/সম্পাদনা করতে বাম পাশ থেকে একজন শিক্ষার্থী নির্বাচন করুন</div>
            @endif
        </div>
    </div>
</div>
