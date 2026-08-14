<div>
    <div class="page-head">
        <div>
            <h2>ক্লাস ও সেকশন ব্যবস্থাপনা</h2>
            <p>আপনার প্রতিষ্ঠানের সব শ্রেণি ও শাখা এখানে সাজান</p>
        </div>
        <button class="btn-primary" wire:click="openClassModal" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
            নতুন ক্লাস যোগ করুন
        </button>
    </div>

    @if (!$hasDepartments)
        <div class="info-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
            এই প্রতিষ্ঠানে এখনো কোনো বিভাগ নেই। <a href="{{ route('academic.departments') }}" style="color:var(--color-maroon);text-decoration:underline;">বিভাগ যোগ করলে</a> (যেমন সাধারণ/হিফয) প্রতিটা ক্লাসের সাথে বিভাগ যুক্ত করার অপশন এখানে আসবে।
        </div>
    @endif

    @forelse ($classes as $class)
        <div class="class-card" wire:key="class-{{ $class->id }}">
            <div class="class-card-head">
                <div>
                    <div class="cname">{{ $class->full_label }}</div>
                    @if ($class->department)
                        <div class="cdept">বিভাগ: {{ $class->department->name }}</div>
                    @elseif ($hasDepartments)
                        <div class="cdept" style="color:var(--bad);display:flex;align-items:center;gap:6px;">
                            ⚠ বিভাগ নির্ধারণ করা হয়নি
                            <select onchange="if(this.value) @this.call('quickAssignDepartment', '{{ $class->id }}', this.value)" style="padding:2px 6px;font-size:11.5px;border-radius:6px;border:1px solid var(--line);">
                                <option value="">বিভাগ বাছুন</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
                <div class="class-card-actions">
                    <button wire:click="deleteClass('{{ $class->id }}')" wire:confirm="এই ক্লাস মুছে ফেলবেন? এতে থাকা সব শাখাও মুছে যাবে।" type="button" title="মুছুন">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6"/></svg>
                    </button>
                </div>
            </div>

            <div class="section-chips">
                @foreach ($class->sections as $section)
                    <div class="section-chip" wire:key="section-{{ $section->id }}">
                        <span>{{ $section->name }} শাখা</span>
                        <span class="scount">({{ $section->students_count }} জন)</span>
                        <button wire:click="deleteSection('{{ $section->id }}')" wire:confirm="এই শাখা মুছে ফেলবেন?" type="button">
                            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </div>
                @endforeach

                <button class="add-section-chip" wire:click="openSectionModal('{{ $class->id }}')" type="button">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                    শাখা যোগ করুন
                </button>
            </div>
        </div>
    @empty
        <div class="table-card" style="padding:40px 0;text-align:center;color:var(--ink-soft);">
            এখনো কোনো ক্লাস তৈরি হয়নি — উপরের বাটনে ক্লিক করে প্রথম ক্লাস যোগ করুন
        </div>
    @endforelse

    {{-- CLASS MODAL --}}
    @if ($showClassModal)
        <div class="modal-overlay" wire:click.self="$set('showClassModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>নতুন ক্লাস যোগ করুন</h3>
                    <button class="modal-close" wire:click="$set('showClassModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="field">
                    <label>ক্লাসের নাম <span class="req">*</span></label>
                    <input type="text" wire:model="className" placeholder="যেমন: ৯ম শ্রেণি">
                    @error('className') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    <div class="preset-chips">
                        <span class="lbl">সাধারণ:</span>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'প্রি-প্লে')">প্রি-প্লে</button>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'নার্সারি')">নার্সারি</button>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'প্লে')">প্লে</button>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'কেজি')">কেজি</button>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'প্রথম শ্রেণি')">প্রথম শ্রেণি</button>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'দ্বিতীয় শ্রেণি')">দ্বিতীয় শ্রেণি</button>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'তৃতীয় শ্রেণি')">তৃতীয় শ্রেণি</button>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'চতুর্থ শ্রেণি')">চতুর্থ শ্রেণি</button>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'পঞ্চম শ্রেণি')">পঞ্চম শ্রেণি</button>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'ষষ্ঠ শ্রেণি')">ষষ্ঠ শ্রেণি</button>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'সপ্তম শ্রেণি')">সপ্তম শ্রেণি</button>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'অষ্টম শ্রেণি')">অষ্টম শ্রেণি</button>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'নবম শ্রেণি')">নবম শ্রেণি</button>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'দশম শ্রেণি')">দশম শ্রেণি</button>
                    </div>
                    <div class="preset-chips">
                        <span class="lbl">মাদরাসা:</span>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'ইবতেদায়ী ১ম')">ইবতেদায়ী ১ম</button>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'ইবতেদায়ী ২য়')">ইবতেদায়ী ২য়</button>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'ইবতেদায়ী ৩য়')">ইবতেদায়ী ৩য়</button>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'ইবতেদায়ী ৪র্থ')">ইবতেদায়ী ৪র্থ</button>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'ইবতেদায়ী ৫ম')">ইবতেদায়ী ৫ম</button>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'দাখিল ৬ষ্ঠ')">দাখিল ৬ষ্ঠ</button>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'দাখিল ৭ম')">দাখিল ৭ম</button>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'দাখিল ৮ম')">দাখিল ৮ম</button>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'দাখিল ৯ম')">দাখিল ৯ম</button>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'দাখিল ১০ম')">দাখিল ১০ম</button>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'আলিম ১ম বর্ষ')">আলিম ১ম বর্ষ</button>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'আলিম ২য় বর্ষ')">আলিম ২য় বর্ষ</button>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'ফাজিল')">ফাজিল</button>
                        <button type="button" class="preset-chip" wire:click="$set('className', 'কামিল')">কামিল</button>
                    </div>
                </div>

                @if ($hasDepartments)
                    <div class="field">
                        <label>বিভাগ <span class="req">*</span></label>
                        <select wire:model="classDepartmentId">
                            <option value="">বিভাগ নির্বাচন করুন</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        @error('classDepartmentId') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showClassModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="saveClass" type="button">সংরক্ষণ করুন</button>
                </div>
            </div>
        </div>
    @endif

    {{-- SECTION MODAL --}}
    @if ($showSectionModal)
        <div class="modal-overlay" wire:click.self="$set('showSectionModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>নতুন শাখা যোগ করুন</h3>
                    <button class="modal-close" wire:click="$set('showSectionModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="field">
                    <label>শাখার নাম <span class="req">*</span></label>
                    <input type="text" wire:model="sectionName" placeholder="যেমন: এ">
                    @error('sectionName') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                    <div class="preset-chips">
                        <span class="lbl">দ্রুত যোগ:</span>
                        @foreach (['A','B','C','D','ক','খ','গ','ঘ'] as $__sec)
                            <button type="button" class="preset-chip" wire:click="$set('sectionName', '{{ $__sec }}')">{{ $__sec }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="field">
                    <label>ধারণক্ষমতা</label>
                    <input type="number" wire:model="sectionCapacity" min="1">
                </div>

                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showSectionModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="saveSection" type="button">সংরক্ষণ করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>