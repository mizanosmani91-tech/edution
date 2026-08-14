<div wire:poll.60s>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">একাডেমিক / লাইভ ক্লাস মনিটর</div>
            <h2 style="margin:0;">লাইভ ক্লাস মনিটর</h2>
            <p class="sub" style="margin-top:4px;">আজ {{ $todayLabel }}, এখন {{ now()->format('h:i A') }} — প্রতি ৬০ সেকেন্ডে স্বয়ংক্রিয়ভাবে হালনাগাদ হয়</p>
        </div>
    </div>

    <div class="kpi-grid" style="margin-bottom:20px;">
        <div class="stat-chip">
            <div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="5.5" width="16" height="14" rx="2"/><path d="M8 3v4M16 3v4"/></svg></div>
            <div><div class="sv">{{ $inClassCount }}</div><div class="sl">এই মুহূর্তে ক্লাসে আছেন</div></div>
        </div>
        <div class="stat-chip">
            <div class="sic" style="background:color-mix(in srgb, var(--bad) 14%, white);"><svg viewBox="0 0 24 24" fill="none" stroke="var(--bad)" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg></div>
            <div><div class="sv">{{ $missingCheckins }}</div><div class="sl">ক্লাসে থাকার কথা কিন্তু চেক-ইন নেই</div></div>
        </div>
    </div>

    <div class="table-card">
        <table>
            <thead><tr>
                <th>শিক্ষক</th><th>এখন কোথায়</th><th>পরের পিরিয়ড</th><th>আজকের হাজিরা</th>
            </tr></thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr wire:key="lcm-{{ $row['teacher']->id }}">
                        <td style="font-weight:600;">{{ $row['teacher']->name }}</td>
                        <td>
                            @if ($row['current'])
                                <span class="pill active">{{ $row['current']->schoolClass?->name }}@if($row['current']->section), {{ $row['current']->section->name }} @endif — {{ $row['current']->subject?->name }}</span>
                            @elseif ($row['hasScheduleToday'])
                                <span class="pill day">ফাঁকা পিরিয়ড</span>
                            @else
                                <span class="pill inactive">আজ রুটিনে নেই</span>
                            @endif
                        </td>
                        <td>
                            @if ($row['next'])
                                {{ $row['next']->start_time }} — {{ $row['next']->schoolClass?->name }}@if($row['next']->section), {{ $row['next']->section->name }} @endif ({{ $row['next']->subject?->name }})
                            @else
                                <span class="sub" style="margin:0;">—</span>
                            @endif
                        </td>
                        <td>
                            @if ($row['checkedOut'])
                                <span class="pill day">চেক-আউট হয়ে গেছে</span>
                            @elseif ($row['checkedIn'])
                                <span class="pill active">চেক-ইন আছে</span>
                            @else
                                <span class="pill due">চেক-ইন নেই</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো সক্রিয় শিক্ষক পাওয়া যায়নি</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="info-box" style="margin-top:14px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
        এটা GPS ট্র্যাকিং না — ক্লাস রুটিন ও চেক-ইন/চেক-আউট ডেটার সাথে বর্তমান সময় মিলিয়ে হিসাব করা হয়েছে। রুটিন সঠিকভাবে সাজানো থাকলেই এই তথ্য নির্ভুল হবে।
    </div>
</div>
