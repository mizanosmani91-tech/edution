<div x-data="{ settingsOpen: false, honorModal: null }">
    {{-- STICKY QUICK ACTIONS --}}
    <div class="quick-bar-sticky">
        <div class="quick-bar-scroll">
            <a href="{{ route('students.index') }}" class="quick-chip">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3.3"/><path d="M3 20c1-3.6 3.4-5.4 6-5.4s5 1.8 6 5.4"/><path d="M17 8h4M19 6v4"/></svg>
                নতুন শিক্ষার্থী ভর্তি
            </a>
            <a href="{{ route('attendance.index') }}" class="quick-chip">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="m8.5 12 2.3 2.3L16 9.7"/></svg>
                হাজিরা নিন
            </a>
            <a href="{{ route('fees.index') }}" class="quick-chip">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3.5" y="7" width="17" height="12" rx="2.5"/><path d="M3.5 11h17"/><circle cx="16.5" cy="15" r="1.4"/></svg>
                ফি সংগ্রহ
            </a>
            <a href="{{ route('teachers.index') }}" class="quick-chip">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M8 20h8"/></svg>
                শিক্ষক যোগ করুন
            </a>
            <a href="{{ route('leave-requests.index') }}" class="quick-chip">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                ছুটির আবেদন
            </a>
            <a href="{{ route('routine.index') }}" class="quick-chip">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M3 10h18M8 2v4M16 2v4"/></svg>
                রুটিন দেখুন
            </a>
            <a href="{{ route('admit-cards.class') }}" class="quick-chip">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="6" width="16" height="12" rx="2"/><circle cx="9" cy="12" r="2"/><path d="M14 10h4M14 14h4"/></svg>
                প্রবেশপত্র
            </a>
            <a href="{{ route('chat.index') }}" class="quick-chip">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h13l3 4-3 4H4z"/><path d="M6 13v6"/></svg>
                নোটিশ পাঠান
            </a>
        </div>
    </div>

    {{-- WELCOME + PROMO HERO --}}
    <div class="dash-hero">
        <div class="welcome-card">
            <h2>স্বাগতম, {{ auth()->user()->name }}! 👋</h2>
            <p>{{ auth()->user()->institution?->name ?? 'EDUTION' }}-এর সার্বিক পরিস্থিতি এক নজরে দেখুন — আজকের হাজিরা, ফি আদায়, পরীক্ষার ফলাফল সব এখানে।</p>
            <a href="{{ route('students.index') }}" class="btn-primary" style="align-self:flex-start;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><circle cx="9" cy="8" r="3.3"/><path d="M3 20c1-3.6 3.4-5.4 6-5.4s5 1.8 6 5.4"/><path d="M17 8h4M19 6v4"/></svg>
                নতুন শিক্ষার্থী ভর্তি
            </a>
        </div>
        <div class="promo-card">
            <div class="pc-text">
                <h3>{{ __('শেখার পরিধি বাড়ান') }}</h3>
                <p>{{ __('অনলাইন MCQ পরীক্ষা, হোমওয়ার্ক ট্র্যাকিং ও লার্নিং ম্যাটেরিয়াল দিয়ে শিক্ষার্থীদের আরও এগিয়ে রাখুন।') }}</p>
                <a href="{{ route('quizzes.index') }}" class="btn-primary">
                    এখনই দেখুন
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
                </a>
            </div>
            <svg class="promo-illustration" viewBox="0 0 120 120" fill="none">
                <circle cx="60" cy="60" r="52" fill="color-mix(in srgb, var(--gold) 18%, white)"/>
                <rect x="34" y="46" width="52" height="38" rx="4" fill="var(--card)" stroke="var(--cover-maroon)" stroke-width="2.5"/>
                <path d="M34 54h52" stroke="var(--cover-maroon)" stroke-width="2.5"/>
                <path d="M60 30 30 44l30 14 30-14-30-14Z" fill="var(--cover-maroon)"/>
                <path d="M42 50v14c0 5 8 9 18 9s18-4 18-9V50" stroke="var(--gold)" stroke-width="2.5" fill="none"/>
                <circle cx="88" cy="34" r="6" fill="var(--good)"/>
                <circle cx="30" cy="80" r="5" fill="var(--teacher)"/>
            </svg>
        </div>
    </div>

    {{-- উইজেট সেটিংস --}}
    <div class="dash-toolbar">
        <button type="button" class="widget-gear" @click="settingsOpen = !settingsOpen">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="3"/><path d="M19.4 13.5a7.7 7.7 0 0 0 0-3l1.9-1.4-2-3.4-2.2.8a7.6 7.6 0 0 0-2.6-1.5L14 2.5h-4l-.5 2.5a7.6 7.6 0 0 0-2.6 1.5l-2.2-.8-2 3.4L4.6 10.5a7.7 7.7 0 0 0 0 3L2.7 14.9l2 3.4 2.2-.8c.77.66 1.65 1.17 2.6 1.5l.5 2.5h4l.5-2.5a7.6 7.6 0 0 0 2.6-1.5l2.2.8 2-3.4-1.9-1.4Z"/></svg>
            উইজেট সাজান
        </button>
        <div class="widget-panel" x-show="settingsOpen" @click.outside="settingsOpen = false" x-cloak style="display:none;">
            @foreach ([
                'honors' => 'মাসের সেরা',
                'kpi' => 'KPI কার্ড',
                'attendance_donut' => 'আজকের হাজিরা (ডোনাট)',
                'class_attendance' => 'ক্লাসভিত্তিক হাজিরা',
                'attendance_trend' => 'হাজিরার প্রবণতা (১৪ দিন)',
                'fee_chart' => 'ফি আদায় বনাম বকেয়া',
                'exam_chart' => 'পরীক্ষার ফলাফল বিভাজন',
                'defaulters' => 'ফি বকেয়া তালিকা',
            ] as $key => $label)
                <label>
                    <input type="checkbox" wire:click="toggleWidget('{{ $key }}')" @checked($widgets[$key] ?? true)>
                    {{ $label }}
                </label>
            @endforeach
        </div>
    </div>

    {{-- মাসের সেরা --}}
    @if ($widgets['honors'] ?? true)
        <div class="honor-grid">
            @if ($honorStudent && $honorStudent->student)
                <div class="honor-card" style="--accent:var(--admin)" @click="honorModal = 'student'">
                    <div class="medal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="6"/><path d="m9 13-2 8 5-3 5 3-2-8"/></svg></div>
                    <div>
                        <div class="hc-label">মাসের সেরা শিক্ষার্থী</div>
                        <div class="hc-name">{{ $honorStudent->student->name }}</div>
                        <div class="hc-sub">{{ $honorStudent->student->schoolClass?->name }} · স্কোর {{ round($honorStudent->score) }}</div>
                    </div>
                </div>
            @else
                <div class="honor-empty">এই মাসের জন্য পর্যাপ্ত হাজিরা ডেটা এখনো নেই</div>
            @endif

            @if ($honorTeacher && $honorTeacher->teacher)
                <div class="honor-card" style="--accent:var(--teacher)" @click="honorModal = 'teacher'">
                    <div class="medal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="6"/><path d="m9 13-2 8 5-3 5 3-2-8"/></svg></div>
                    <div>
                        <div class="hc-label">মাসের সেরা শিক্ষক/স্টাফ</div>
                        <div class="hc-name">{{ $honorTeacher->teacher->name }}</div>
                        <div class="hc-sub">{{ $honorTeacher->teacher->designation }} · স্কোর {{ round($honorTeacher->score) }}</div>
                    </div>
                </div>
            @else
                <div class="honor-empty">এই মাসের জন্য পর্যাপ্ত হাজিরা ডেটা এখনো নেই</div>
            @endif
        </div>

        {{-- মোডাল: শিক্ষার্থী --}}
        @if ($honorStudent && $honorStudent->student)
            <div class="honor-modal-backdrop" x-show="honorModal === 'student'" x-cloak @click.self="honorModal = null" style="display:none;">
                <div class="honor-modal">
                    <div class="medal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="6"/><path d="m9 13-2 8 5-3 5 3-2-8"/></svg></div>
                    <h3>{{ $honorStudent->student->name }}</h3>
                    <div class="role">{{ $honorStudent->student->schoolClass?->name }} · মাসের সেরা শিক্ষার্থী</div>
                    <div class="metric-row"><span>হাজিরা</span><span class="v">{{ $honorStudent->metrics['attendance_pct'] ?? '—' }}%</span></div>
                    <div class="metric-row"><span>গড় পরীক্ষার নম্বর</span><span class="v">{{ $honorStudent->metrics['avg_marks'] ?? '—' }}{{ isset($honorStudent->metrics['avg_marks']) ? '%' : '' }}</span></div>
                    <div class="metric-row"><span>সামগ্রিক স্কোর</span><span class="v">{{ round($honorStudent->score) }}</span></div>
                    <button type="button" class="close-btn" @click="honorModal = null">বন্ধ করুন</button>
                </div>
            </div>
        @endif

        {{-- মোডাল: শিক্ষক --}}
        @if ($honorTeacher && $honorTeacher->teacher)
            <div class="honor-modal-backdrop" x-show="honorModal === 'teacher'" x-cloak @click.self="honorModal = null" style="display:none;">
                <div class="honor-modal">
                    <div class="medal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="6"/><path d="m9 13-2 8 5-3 5 3-2-8"/></svg></div>
                    <h3>{{ $honorTeacher->teacher->name }}</h3>
                    <div class="role">{{ $honorTeacher->teacher->designation }} · মাসের সেরা শিক্ষক/স্টাফ</div>
                    <div class="metric-row"><span>হাজিরা</span><span class="v">{{ $honorTeacher->metrics['attendance_pct'] ?? '—' }}%</span></div>
                    <div class="metric-row"><span>পারফরম্যান্স রিভিউ (৫ এ)</span><span class="v">{{ $honorTeacher->metrics['review_score'] ?? '—' }}</span></div>
                    <div class="metric-row"><span>সামগ্রিক স্কোর</span><span class="v">{{ round($honorTeacher->score) }}</span></div>
                    <button type="button" class="close-btn" @click="honorModal = null">বন্ধ করুন</button>
                </div>
            </div>
        @endif
    @endif

    {{-- KPI CARDS --}}
    @if ($widgets['kpi'] ?? true)
        <div class="kpi-grid-v2">
            <div class="kpi-card-v2" style="--accent:var(--admin)">
                <div>
                    <div class="kpi-v2-label">মোট শিক্ষার্থী</div>
                    <div class="kpi-v2-value">{{ number_format($totalStudents) }}</div>
                </div>
                <div class="kpi-v2-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="4"/><path d="M4 20c1.5-4.5 5-6.5 8-6.5s6.5 2 8 6.5"/></svg></div>
            </div>

            <div class="kpi-card-v2" style="--accent:var(--teacher)">
                <div>
                    <div class="kpi-v2-label">মোট শিক্ষক</div>
                    <div class="kpi-v2-value">{{ number_format($totalTeachers) }}</div>
                </div>
                <div class="kpi-v2-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M8 20h8"/></svg></div>
            </div>

            <div class="kpi-card-v2" style="--accent:var(--good)">
                <div>
                    <div class="kpi-v2-label">আজকের উপস্থিতি</div>
                    <div class="kpi-v2-value">{{ $attendanceRate !== null ? $attendanceRate . '%' : '—' }}</div>
                </div>
                <div class="kpi-v2-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="m8.5 12 2.3 2.3L16 9.7"/></svg></div>
            </div>

            <div class="kpi-card-v2" style="--accent:var(--gold)">
                <div>
                    <div class="kpi-v2-label">এই মাসের কালেকশন</div>
                    <div class="kpi-v2-value">৳{{ number_format($monthCollection) }}</div>
                    @if ($totalDue > 0)
                        <div class="kpi-v2-sub down">বকেয়া ৳{{ number_format($totalDue) }}</div>
                    @endif
                </div>
                <div class="kpi-v2-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3.5" y="7" width="17" height="12" rx="2.5"/><path d="M3.5 11h17"/><circle cx="16.5" cy="15" r="1.4"/></svg></div>
            </div>
        </div>
    @endif

    {{-- আজকের হাজিরা ডোনাট (শিক্ষার্থী + শিক্ষক) --}}
    @if ($widgets['attendance_donut'] ?? true)
        <div class="charts-grid">
            <div class="card">
                <div class="card-head">
                    <div><h3>{{ __('আজকের শিক্ষার্থী হাজিরা') }}</h3><p>{{ __('উপস্থিত/অনুপস্থিত/দেরি/ছুটি — মাউস নিলে বিস্তারিত') }}</p></div>
                </div>
                <div class="chart-box small" style="position:relative;">
                    <canvas id="studentAttendanceDonut"></canvas>
                    <div class="donut-center"><div class="big">{{ $attendanceRate !== null ? $attendanceRate . '%' : '—' }}</div><div class="lbl">উপস্থিত</div></div>
                </div>
                <div class="donut-legend">
                    <span><i style="background:#10B981"></i>উপস্থিত {{ $attendanceDonut['data'][0] }}</span>
                    <span><i style="background:#EF4444"></i>অনুপস্থিত {{ $attendanceDonut['data'][1] }}</span>
                    <span><i style="background:#F59E0B"></i>দেরি {{ $attendanceDonut['data'][2] }}</span>
                    <span><i style="background:#3B82F6"></i>ছুটি {{ $attendanceDonut['data'][3] }}</span>
                </div>
            </div>
            <div class="card">
                <div class="card-head">
                    <div><h3>{{ __('আজকের শিক্ষক/স্টাফ হাজিরা') }}</h3><p>{{ __('উপস্থিত/অনুপস্থিত/দেরি') }}</p></div>
                </div>
                <div class="chart-box small" style="position:relative;">
                    <canvas id="staffAttendanceDonut"></canvas>
                </div>
                <div class="donut-legend">
                    <span><i style="background:#10B981"></i>উপস্থিত {{ $staffDonut['data'][0] }}</span>
                    <span><i style="background:#EF4444"></i>অনুপস্থিত {{ $staffDonut['data'][1] }}</span>
                    <span><i style="background:#F59E0B"></i>দেরি {{ $staffDonut['data'][2] }}</span>
                </div>
            </div>
        </div>
    @endif

    {{-- স্টার শিক্ষার্থী + বেস্ট পারফরমার --}}
    <div class="bottom-grid" style="margin-bottom:16px;">
        @if ($topScorers->isNotEmpty())
            <div class="card">
                <div class="dash-section-head">
                    <h2>স্টার শিক্ষার্থী</h2>
                    <a href="{{ route('merit-list.index') }}" class="link">সব দেখুন →</a>
                </div>
                <p style="margin:-8px 0 14px;font-size:12.5px;color:var(--ink-muted);">{{ $latestExam?->name }} — সর্বোচ্চ নম্বরপ্রাপ্ত ৫ জন</p>
                <table class="star-table">
                    <thead><tr><th></th><th>নাম</th><th>আইডি</th><th>নম্বর</th><th>শতাংশ</th></tr></thead>
                    <tbody>
                        @foreach ($topScorers as $row)
                            <tr>
                                <td><input type="checkbox" class="star-check" disabled @checked($loop->first)></td>
                                <td>
                                    <div class="stud">
                                        <div class="ini">{{ mb_substr($row['student']->name, 0, 1) }}</div>
                                        <div class="info">
                                            <div>{{ $row['student']->name }}</div>
                                            <div class="cls">{{ $row['student']->schoolClass?->full_label }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $row['student']->student_id_no }}</td>
                                <td>{{ $row['total'] }}</td>
                                <td><span class="percent-pill">{{ $row['percent'] }}%</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- বেস্ট পারফরমার: ক্লাসভিত্তিক আজকের হাজিরা --}}
        @if ($widgets['class_attendance'] ?? true)
            <div class="card">
                <div class="dash-section-head">
                    <h2>বেস্ট পারফরমার</h2>
                    <select style="border:1px solid var(--line);border-radius:8px;padding:5px 10px;font-size:12px;font-family:inherit;color:var(--ink-muted);background:#fff;">
                        <option>আজকের হাজিরা</option>
                    </select>
                </div>
                <p style="margin:-8px 0 14px;font-size:12.5px;color:var(--ink-muted);">ক্লাসভিত্তিক আজকের উপস্থিতির হার</p>
                <div class="performer-list">
                    @php $__perfColors = ['var(--admin)', 'var(--teacher)', 'var(--good)', 'var(--gold)', 'var(--student)', 'var(--bad)']; @endphp
                    @forelse ($classAttendance as $i => $row)
                        <div class="performer-row">
                            <div class="cls">{{ $row['label'] }}</div>
                            <div class="bar-track">
                                <div class="bar-fill" style="width:{{ max($row['value'], 6) }}%;background:{{ $__perfColors[$i % count($__perfColors)] }}">{{ $row['value'] }}%</div>
                            </div>
                        </div>
                    @empty
                        <p style="text-align:center;color:var(--ink-soft);">আজকের হাজিরার ডেটা এখনো নেই</p>
                    @endforelse
                </div>
            </div>
        @endif
    </div>

    {{-- ট্রেন্ড + ফি --}}
    <div class="charts-grid">
        @if ($widgets['attendance_trend'] ?? true)
            <div class="card">
                <div class="card-head">
                    <div><h3>{{ __('উপস্থিতির প্রবণতা') }}</h3><p>{{ __('শেষ ১৪ দিনের হার') }}</p></div>
                </div>
                <div class="chart-box"><canvas id="attendanceChart"></canvas></div>
            </div>
        @endif

        @if ($widgets['fee_chart'] ?? true)
            <div class="card">
                <div class="card-head">
                    <div><h3>{{ __('ফি আদায়ের অবস্থা') }}</h3><p>{{ __('চলতি মাস') }}</p></div>
                </div>
                <div class="chart-box small" style="position:relative;">
                    <canvas id="feeDonut"></canvas>
                </div>
            </div>
        @endif
    </div>

    {{-- পরীক্ষার ফলাফল বিভাজন --}}
    @if (($widgets['exam_chart'] ?? true) && $examChart)
        <div class="row-2">
            <div class="card">
                <div class="card-head">
                    <div><h3>{{ __('পরীক্ষার ফলাফল বিভাজন') }}</h3><p>{{ $examChart['exam_name'] }}</p></div>
                </div>
                <div class="chart-box"><canvas id="examGradeBar"></canvas></div>
            </div>
        </div>
    @endif

    {{-- রুটিন --}}
    <div class="card" style="margin-bottom:16px;">
        <div class="dash-section-head">
            <h2>ক্লাস রুটিন</h2>
            <a href="{{ route('routine.index') }}" class="link">সব দেখুন →</a>
        </div>
        <div class="routine-mini">
            <div class="routine-mini-card">
                <div class="rm-head">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M3 10h18M8 2v4M16 2v4"/></svg>
                    {{ now()->translatedFormat('F, Y') }}
                </div>
                <div class="rm-sub">এই মাসের ক্লাস রুটিন দেখুন</div>
                <a href="{{ route('routine.index') }}" class="rm-btn">
                    রুটিন দেখুন
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
                </a>
            </div>
            <div class="routine-mini-card">
                <div class="rm-head">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M3 10h18M8 2v4M16 2v4"/></svg>
                    {{ now()->addMonth()->translatedFormat('F, Y') }}
                </div>
                <div class="rm-sub">আগামী মাসের প্রস্তুতি নিন</div>
                <a href="{{ route('routine.index') }}" class="rm-btn">
                    রুটিন দেখুন
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
                </a>
            </div>
        </div>
    </div>

    {{-- BOTTOM: NOTICES + DEFAULTERS --}}
    <div class="bottom-grid">
        <div class="card">
            <div class="card-head"><div><h3>{{ __('সাম্প্রতিক নোটিশ ও কার্যক্রম') }}</h3></div></div>
            <div class="ledger-list">
                <div class="ledger-row">
                    <div class="date-badge"><div class="d">{{ now()->translatedFormat('d') }}</div><div class="m">{{ now()->translatedFormat('M') }}</div></div>
                    <div class="ledger-body">
                        <div class="t">EDUTION-তে স্বাগতম</div>
                        <div class="m">আপনার প্রতিষ্ঠানের ডেটা এন্ট্রি শুরু করুন</div>
                        <span class="tag general">সাধারণ</span>
                    </div>
                </div>
            </div>
        </div>

        @if ($widgets['defaulters'] ?? true)
            <div class="card">
                <div class="card-head"><div><h3>{{ __('ফি বকেয়া — শীর্ষ তালিকা') }}</h3></div></div>

                {{-- ডেস্কটপ টেবিল --}}
                <table class="swap-table">
                    <thead><tr><th>শিক্ষার্থী</th><th>বকেয়া</th></tr></thead>
                    <tbody>
                        @forelse ($topDefaulters as $row)
                            <tr>
                                <td>
                                    <div class="stud">
                                        <div class="ini">{{ mb_substr($row['student']->name, 0, 1) }}</div>
                                        <div class="info">
                                            <div>{{ $row['student']->name }}</div>
                                            <div class="cls">{{ $row['student']->schoolClass?->full_label }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="due">৳{{ number_format($row['total_due']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" style="text-align:center;color:var(--ink-soft);padding:20px 0;">কোনো বকেয়া নেই</td></tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- মোবাইল কার্ড --}}
                <div class="card-list swap-list">
                    @forelse ($topDefaulters as $row)
                        <div class="row" style="display:flex;align-items:center;justify-content:space-between;">
                            <div class="stud">
                                <div class="ini">{{ mb_substr($row['student']->name, 0, 1) }}</div>
                                <div class="info">
                                    <div>{{ $row['student']->name }}</div>
                                    <div class="cls">{{ $row['student']->schoolClass?->full_label }}</div>
                                </div>
                            </div>
                            <span class="due">৳{{ number_format($row['total_due']) }}</span>
                        </div>
                    @empty
                        <p style="text-align:center;color:var(--ink-soft);">কোনো বকেয়া নেই</p>
                    @endforelse
                </div>
            </div>
        @endif
    </div>

    <script>
        (function () {
            const chartData = {
                attendanceDonut: @json($attendanceDonut),
                classAttendance: @json($classAttendance),
                trendLabels: @json($trendLabels),
                trendData: @json($trendData),
                feeChart: @json($feeChart),
                examChart: @json($examChart),
                staffDonut: @json($staffDonut),
            };

            function boot() {
                // Chart.js এখন resources/js/app.js এর মাধ্যমে Vite বান্ডেলে লোড হয়
                // (আগে external CDN থেকে লোড হতো, নেটওয়ার্ক/অ্যাডব্লকার এ ব্লক হলে
                // চুপচাপ ফেইল করত আর চার্ট কখনো দেখাই যেত না)।
                if (window.Chart) initCharts();
                else window.addEventListener('chartjs:ready', initCharts, { once: true });
            }

            function mk(id, config) {
                const el = document.getElementById(id);
                if (!el) return;
                if (el._chartInstance) { el._chartInstance.destroy(); }
                el._chartInstance = new Chart(el, config);
            }

            function initCharts() {
                Chart.defaults.font.family = "'Hind Siliguri', sans-serif";
                Chart.defaults.color = '#6B7280';

                mk('studentAttendanceDonut', {
                    type: 'doughnut',
                    data: { labels: chartData.attendanceDonut.labels, datasets: [{ data: chartData.attendanceDonut.data, backgroundColor: chartData.attendanceDonut.colors, borderWidth: 3, borderColor: '#fff' }] },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '72%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } }, tooltip: { callbacks: { label: (ctx) => ctx.label + ': ' + ctx.parsed + ' জন' } } } }
                });

                mk('staffAttendanceDonut', {
                    type: 'doughnut',
                    data: { labels: chartData.staffDonut.labels, datasets: [{ data: chartData.staffDonut.data, backgroundColor: chartData.staffDonut.colors, borderWidth: 3, borderColor: '#fff' }] },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } }, tooltip: { callbacks: { label: (ctx) => ctx.label + ': ' + ctx.parsed + ' জন' } } } }
                });

                mk('attendanceChart', {
                    type: 'line',
                    data: { labels: chartData.trendLabels, datasets: [{ label: 'উপস্থিতি %', data: chartData.trendData, borderColor: '#6C5CE7', backgroundColor: 'rgba(108,92,231,.12)', tension: .4, fill: true, spanGaps: true, pointRadius: 2, borderWidth: 2.5 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: (ctx) => 'উপস্থিতি: ' + ctx.parsed.y + '%' } } }, scales: { y: { min: 0, max: 100, grid: { color: 'rgba(31,36,50,.06)' }, ticks: { callback: v => v + '%' } }, x: { grid: { display: false } } } }
                });

                mk('feeDonut', {
                    type: 'doughnut',
                    data: { labels: chartData.feeChart.labels, datasets: [{ data: chartData.feeChart.data, backgroundColor: chartData.feeChart.colors, borderWidth: 3, borderColor: '#fff' }] },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } }, tooltip: { callbacks: { label: (ctx) => ctx.label + ': ৳' + ctx.parsed.toLocaleString('bn-BD') } } } }
                });

                if (chartData.examChart) {
                    mk('examGradeBar', {
                        type: 'bar',
                        data: { labels: chartData.examChart.labels, datasets: [{ data: chartData.examChart.data, backgroundColor: chartData.examChart.colors, borderRadius: 6, maxBarThickness: 40 }] },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: (ctx) => ctx.parsed.y + ' জন শিক্ষার্থী' } } }, scales: { y: { grid: { color: 'rgba(31,36,50,.06)' } }, x: { grid: { display: false } } } }
                    });
                }
            }

            boot();
            document.addEventListener('livewire:navigated', boot);
        })();
    </script>
</div>
