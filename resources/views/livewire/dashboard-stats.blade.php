<div>
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

    {{-- KPI CARDS --}}
<div>
    {{-- KPI CARDS --}}
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

    {{-- CHARTS --}}
    <div class="charts-grid">
        <div class="card">
            <div class="card-head">
                <div>
                    <h3>উপস্থিতির প্রবণতা</h3>
                    <p>শেষ ১২ মাসের হার</p>
                </div>
                <div class="legend">
                    <span><i style="background:var(--gold)"></i> শিক্ষার্থী</span>
                    <span><i style="background:var(--teacher)"></i> শিক্ষক</span>
                </div>
            </div>
            <div class="chart-box"><canvas id="attendanceChart"></canvas></div>
        </div>

        <div class="card">
            <div class="card-head">
                <div>
                    <h3>ফি আদায়ের অবস্থা</h3>
                    <p>চলতি মাস</p>
                </div>
            </div>
            <div class="chart-box small" style="position:relative;">
                <canvas id="feeDonut"></canvas>
                <div class="donut-center">
                    <div class="big">৮৫%</div>
                    <div class="lbl">আদায়কৃত</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row-2">
        <div class="card">
            <div class="card-head">
                <div>
                    <h3>শ্রেণিভিত্তিক শিক্ষার্থী সংখ্যা</h3>
                    <p>আপনার প্রতিষ্ঠানের সব ক্লাস</p>
                </div>
            </div>
            <div class="chart-box"><canvas id="classBar"></canvas></div>
        </div>
    </div>

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
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
    <script>
        (function () {
            if (window.Chart) initCharts();
            else {
                const s = document.createElement('script');
                s.src = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js';
                s.onload = initCharts;
                document.head.appendChild(s);
            }

            function initCharts() {
                Chart.defaults.font.family = "'Hind Siliguri', sans-serif";
                Chart.defaults.color = '#7A7061';

                const months = ['জানু','ফেব্রু','মার্চ','এপ্রি','মে','জুন','জুলাই','আগ','সেপ্ট','অক্টো','নভে','ডিসে'];

                const attCanvas = document.getElementById('attendanceChart');
                if (attCanvas && !attCanvas.dataset.rendered) {
                    attCanvas.dataset.rendered = '1';
                    new Chart(attCanvas, {
                        type: 'line',
                        data: {
                            labels: months,
                            datasets: [
                                { label: 'শিক্ষার্থী', data: [88,90,89,91,92,90,87,93,94,95,94,{{ $attendanceRate ?? 95 }}], borderColor: '#C9A227', backgroundColor: 'rgba(201,162,39,.14)', tension: .4, fill: true, pointRadius: 0, borderWidth: 2.5 },
                                { label: 'শিক্ষক', data: [95,96,94,96,97,95,93,97,98,97,98,98], borderColor: '#35528F', backgroundColor: 'rgba(53,82,143,.08)', tension: .4, fill: true, pointRadius: 0, borderWidth: 2.5 }
                            ]
                        },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { min: 80, max: 100, grid: { color: 'rgba(42,35,32,.06)' }, ticks: { callback: v => v + '%' } }, x: { grid: { display: false } } } }
                    });
                }

                const donutCanvas = document.getElementById('feeDonut');
                if (donutCanvas && !donutCanvas.dataset.rendered) {
                    donutCanvas.dataset.rendered = '1';
                    new Chart(donutCanvas, {
                        type: 'doughnut',
                        data: { labels: ['আদায়কৃত','বকেয়া','ওভারডিউ'], datasets: [{ data: [85, 10, 5], backgroundColor: ['#2F6E52','#C9A227','#A6412E'], borderWidth: 3, borderColor: '#fff' }] },
                        options: { responsive: true, maintainAspectRatio: false, cutout: '72%', plugins: { legend: { display: false } } }
                    });
                }

                const barCanvas = document.getElementById('classBar');
                if (barCanvas && !barCanvas.dataset.rendered) {
                    barCanvas.dataset.rendered = '1';
                    new Chart(barCanvas, {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode($classDistribution->pluck('label')) !!},
                            datasets: [{ data: {!! json_encode($classDistribution->pluck('count')) !!}, backgroundColor: '#5C1A2B', borderRadius: 6, maxBarThickness: 28 }]
                        },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { grid: { color: 'rgba(42,35,32,.06)' } }, x: { grid: { display: false } } } }
                    });
                }
            }
        })();
    </script>
</div>