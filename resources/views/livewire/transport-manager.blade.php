<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">পরিবহন</div>
            <h2>পরিবহন ব্যবস্থাপনা</h2>
        </div>
        @if ($tab === 'routes')
            <button class="btn-primary" wire:click="openRouteModal" type="button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
                নতুন রুট
            </button>
        @else
            <button class="btn-primary" wire:click="openAssignModal" type="button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
                শিক্ষার্থী সংযুক্ত করুন
            </button>
        @endif
    </div>

    <div class="tabs-bar">
        <button type="button" class="tab-btn {{ $tab === 'routes' ? 'active' : '' }}" wire:click="$set('tab','routes')">রুট ও গাড়ি</button>
        <button type="button" class="tab-btn {{ $tab === 'assignments' ? 'active' : '' }}" wire:click="$set('tab','assignments')">ছাত্র-পরিবহন সংযুক্তি</button>
    </div>

    @if ($tab === 'routes')
        <div class="table-card">
            <table>
                <thead><tr><th>রুটের নাম</th><th>গাড়ি নং</th><th>ড্রাইভার</th><th>ধারণক্ষমতা</th><th>মাসিক ফি</th><th>সংযুক্ত শিক্ষার্থী</th><th></th></tr></thead>
                <tbody>
                    @forelse ($routes as $route)
                        <tr wire:key="route-{{ $route->id }}">
                            <td>{{ $route->route_name }}</td>
                            <td>{{ $route->vehicle_no ?? '—' }}</td>
                            <td>{{ $route->driver_name ?? '—' }} @if($route->driver_phone) ({{ $route->driver_phone }}) @endif</td>
                            <td>{{ $route->capacity }}</td>
                            <td>৳{{ number_format($route->monthly_fee, 0) }}</td>
                            <td>{{ $route->assignments_count }}</td>
                            <td>
                                <div class="row-actions">
                                    <button wire:click="openRouteModal('{{ $route->id }}')" title="সম্পাদনা">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                    </button>
                                    <button wire:click="deleteRoute('{{ $route->id }}')" wire:confirm="রুটটি মুছে ফেলতে চান?" title="মুছুন">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2m-8 0 1 12h8l1-12"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align:center;color:var(--ink-soft);padding:30px 0;">এখনো কোনো রুট যোগ করা হয়নি</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div class="table-card">
            <table>
                <thead><tr><th>শিক্ষার্থী</th><th>রুট</th><th>সংযুক্তির তারিখ</th><th></th></tr></thead>
                <tbody>
                    @forelse ($assignments as $a)
                        <tr wire:key="assign-{{ $a->id }}">
                            <td>{{ $a->student->name }} — {{ $a->student->student_id_no }}</td>
                            <td>{{ $a->route->route_name }}</td>
                            <td>{{ $a->assigned_at->format('d M, Y') }}</td>
                            <td><button wire:click="unassign('{{ $a->id }}')" wire:confirm="সংযুক্তি বাতিল করতে চান?" title="বাতিল করুন">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 6 6 18M6 6l12 12"/></svg>
                            </button></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো শিক্ষার্থী এখনো সংযুক্ত করা হয়নি</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    @if ($showRouteModal)
        <div class="modal-overlay" wire:click.self="$set('showRouteModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>{{ $editingRouteId ? 'রুট সম্পাদনা' : 'নতুন রুট' }}</h3>
                    <button class="modal-close" wire:click="$set('showRouteModal', false)" type="button">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="field"><label>রুটের নাম <span class="req">*</span></label><input type="text" wire:model="routeName">
                    @error('routeName') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="row-2">
                    <div class="field"><label>গাড়ি নম্বর</label><input type="text" wire:model="vehicleNo"></div>
                    <div class="field"><label>ধারণক্ষমতা</label><input type="number" min="0" wire:model="capacity"></div>
                </div>
                <div class="row-2">
                    <div class="field"><label>ড্রাইভারের নাম</label><input type="text" wire:model="driverName"></div>
                    <div class="field"><label>ড্রাইভারের মোবাইল</label><input type="text" wire:model="driverPhone"></div>
                </div>
                <div class="field"><label>মাসিক ফি (৳)</label><input type="number" step="0.01" wire:model="monthlyFee"></div>
                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showRouteModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="saveRoute" type="button">সংরক্ষণ করুন</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showAssignModal)
        <div class="modal-overlay" wire:click.self="$set('showAssignModal', false)">
            <div class="modal-box">
                <div class="modal-head">
                    <h3>শিক্ষার্থী পরিবহনে সংযুক্ত করুন</h3>
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
                    <label>রুট <span class="req">*</span></label>
                    <select wire:model="routeId">
                        <option value="">নির্বাচন করুন</option>
                        @foreach ($allRoutes as $r)
                            <option value="{{ $r->id }}">{{ $r->route_name }}</option>
                        @endforeach
                    </select>
                    @error('routeId') <p class="hint" style="color:var(--bad)">{{ $message }}</p> @enderror
                </div>
                <div class="modal-foot">
                    <button class="btn-ghost" wire:click="$set('showAssignModal', false)" type="button">বাতিল</button>
                    <button class="btn-primary" wire:click="assign" type="button">সংযুক্ত করুন</button>
                </div>
            </div>
        </div>
    @endif
</div>
