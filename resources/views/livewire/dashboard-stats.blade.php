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
                <div class="honor-card" @click="honorModal = 'student'">
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
                <div class="honor-card" @click="honorModal = 'teacher'">
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
        <div class="kpi-grid">
            <div class="kpi-card" style="--accent:var(--admin)">
                <div class="kpi-top">
                    <div class="kpi-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="4"/><path d="M4 20c1.5-4.5 5-6.5 8-6.5s6.5 2 8 6.5"/></svg></div>
                </div>
                <div class="kpi-label">মোট শিক্ষার্থী</div>
                <div class="kpi-value">{{ number_format($totalStudents) }}</div>
            </div>

            <div class="kpi-card" style="--accent:var(--teacher)">
                <div class="kpi-top">
                    <div class="kpi-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M8 20h8"/></svg></div>
                </div>
                <div class="kpi-label">মোট শিক্ষক</div>
                <div class="kpi-value">{{ number_format($totalTeachers) }}</div>
            </div>

            <div class="kpi-card" style="--accent:var(--good)">
                <div class="kpi-top">
                    <div class="kpi-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="m8.5 12 2.3 2.3L16 9.7"/></svg></div>
                </div>
                <div class="kpi-label">আজকের উপস্থিতি</div>
                <div class="kpi-value">{{ $attendanceRate !== null ? $attendanceRate . '%' : '—' }}</div>
            </div>

            <div class="kpi-card" style="--accent:var(--gold)">
                <div class="kpi-top">
                    <div class="kpi-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3.5" y="7" width="17" height="12" rx="2.5"/><path d="M3.5 11h17"/><circle cx="16.5" cy="15" r="1.4"/></svg></div>
                </div>
                <div class="kpi-label">এই মাসের কালেকশন</div>
                <div class="kpi-value">৳{{ number_format($monthCollection) }}</div>
                @if ($totalDue > 0)
                    <div class="kpi-trend down"><span class="note">বকেয়া ৳{{ number_format($totalDue) }}</span></div>
                @endif
            </div>
        </div>
    @endif

    {{-- আজকের হাজিরা ডোনাট (শিক্ষার্থী + শিক্ষক) --}}
    @if ($widgets['attendance_donut'] ?? true)
        <div class="charts-grid">
            <div class="card">
                <div class="card-head">
                    <div><h3>আজকের শিক্ষার্থী হাজিরা</h3><p>উপস্থিত/অনুপস্থিত/দেরি/ছুটি — মাউস নিলে বিস্তারিত</p></div>
                </div>
                <div class="chart-box small" style="position:relative;">
                    <canvas id="studentAttendanceDonut"></canvas>
                    <div class="donut-center"><div class="big">{{ $attendanceRate !== null ? $attendanceRate . '%' : '—' }}</div><div class="lbl">উপস্থিত</div></div>
                </div>
            </div>
            <div class="card">
                <div class="card-head">
                    <div><h3>আজকের শিক্ষক/স্টাফ হাজিরা</h3><p>উপস্থিত/অনুপস্থিত/দেরি</p></div>
                </div>
                <div class="chart-box small" style="position:relative;">
                    <canvas id="staffAttendanceDonut"></canvas>
                </div>
            </div>
        </div>
    @endif

    {{-- ক্লাসভিত্তিক হাজিরা --}}
    @if ($widgets['class_attendance'] ?? true)
        <div class="row-2">
            <div class="card">
                <div class="card-head">
                    <div><h3>ক্লাসভিত্তিক আজকের হাজিরা</h3><p>প্রতিটি ক্লাসের উপস্থিতির হার</p></div>
                </div>
                <div class="chart-box"><canvas id="classAttendanceBar"></canvas></div>
            </div>
        </div>
    @endif

    {{-- ট্রেন্ড + ফি --}}
    <div class="charts-grid">
        @if ($widgets['attendance_trend'] ?? true)
            <div class="card">
                <div class="card-head">
                    <div><h3>উপস্থিতির প্রবণতা</h3><p>শেষ ১৪ দিনের হার</p></div>
                </div>
                <div class="chart-box"><canvas id="attendanceChart"></canvas></div>
            </div>
        @endif

        @if ($widgets['fee_chart'] ?? true)
            <div class="card">
                <div class="card-head">
                    <div><h3>ফি আদায়ের অবস্থা</h3><p>চলতি মাস</p></div>
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
                    <div><h3>পরীক্ষার ফলাফল বিভাজন</h3><p>{{ $examChart['exam_name'] }}</p></div>
                </div>
                <div class="chart-box"><canvas id="examGradeBar"></canvas></div>
            </div>
        </div>
    @endif

    {{-- BOTTOM: NOTICES + DEFAULTERS --}}
    <div class="bottom-grid">
        <div class="card">
            <div class="card-head"><div><h3>সাম্প্রতিক নোটিশ ও কার্যক্রম</h3></div></div>
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
                <div class="card-head"><div><h3>ফি বকেয়া — শীর্ষ তালিকা</h3></div></div>

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
                Chart.defaults.color = '#7A7061';

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

                mk('classAttendanceBar', {
                    type: 'bar',
                    data: { labels: chartData.classAttendance.map(r => r.label), datasets: [{ label: 'উপস্থিতি %', data: chartData.classAttendance.map(r => r.value), backgroundColor: '#C9A227', borderRadius: 6, maxBarThickness: 34 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: (ctx) => 'উপস্থিতি: ' + ctx.parsed.y + '%' } } }, scales: { y: { min: 0, max: 100, grid: { color: 'rgba(42,35,32,.06)' }, ticks: { callback: v => v + '%' } }, x: { grid: { display: false } } } }
                });

                mk('attendanceChart', {
                    type: 'line',
                    data: { labels: chartData.trendLabels, datasets: [{ label: 'উপস্থিতি %', data: chartData.trendData, borderColor: '#5C1A2B', backgroundColor: 'rgba(92,26,43,.1)', tension: .4, fill: true, spanGaps: true, pointRadius: 2, borderWidth: 2.5 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: (ctx) => 'উপস্থিতি: ' + ctx.parsed.y + '%' } } }, scales: { y: { min: 0, max: 100, grid: { color: 'rgba(42,35,32,.06)' }, ticks: { callback: v => v + '%' } }, x: { grid: { display: false } } } }
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
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: (ctx) => ctx.parsed.y + ' জন শিক্ষার্থী' } } }, scales: { y: { grid: { color: 'rgba(42,35,32,.06)' } }, x: { grid: { display: false } } } }
                    });
                }
            }

            boot();
            document.addEventListener('livewire:navigated', boot);
        })();
    </script>
</div>
