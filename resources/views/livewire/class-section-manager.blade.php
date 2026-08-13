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
            বিভাগ (Department) ফিচার এখন বন্ধ আছে। সেটিংস থেকে চালু করলে প্রতিটা ক্লাসের সাথে বিভাগ যুক্ত করা যাবে।
        </div>
    @endif

    @forelse ($classes as $class)
        <div class="class-card" wire:key="class-{{ $class->id }}">
            <div class="class-card-head">
                <div>
                    <div class="cname">{{ $class->full_label }}</div>
                    @if ($class->department)
                        <div class="cdept">বিভাগ: {{ $class->department->name }}</div>
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
                </div>

                @if ($hasDepartments)
                    <div class="field">
                        <label>বিভাগ <span class="opt">(ঐচ্ছিক)</span></label>
                        <select wire:model="classDepartmentId">
                            <option value="">কোনো বিভাগ না</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
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