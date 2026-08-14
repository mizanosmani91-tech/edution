<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">ভর্তি / আসন ব্যবস্থাপনা</div>
            <h2>আসন ব্যবস্থাপনা</h2>
            <p>প্রতিটা শাখার ধারণক্ষমতা নির্ধারণ করুন এবং বর্তমান পূর্ণতা দেখুন</p>
        </div>
    </div>

    <div class="table-card">
        <table>
            <thead><tr><th>শ্রেণি</th><th>শাখা</th><th>ধারণক্ষমতা</th><th>বর্তমান শিক্ষার্থী</th><th>খালি আসন</th><th>পূর্ণতা</th></tr></thead>
            <tbody>
                @forelse ($sections as $s)
                    @php
                        $filled = $s->students->count();
                        $capacity = (int) ($s->capacity ?? 0);
                        $vacant = max(0, $capacity - $filled);
                        $pct = $capacity > 0 ? min(100, round(($filled / $capacity) * 100)) : 0;
                    @endphp
                    <tr wire:key="seat-{{ $s->id }}">
                        <td>{{ $s->schoolClass->full_label ?? '—' }}</td>
                        <td>{{ $s->name }}</td>
                        <td style="width:130px;">
                            <input type="number" min="0" value="{{ $capacity }}" style="width:90px;" wire:change="updateCapacity('{{ $s->id }}', $event.target.value)">
                        </td>
                        <td>{{ $filled }}</td>
                        <td>{{ $capacity > 0 ? $vacant : '—' }}</td>
                        <td>
                            @if ($capacity > 0)
                                <span class="pill {{ $pct >= 100 ? 'due' : ($pct >= 80 ? 'day' : 'active') }}">{{ $pct }}%</span>
                            @else
                                <span style="color:var(--ink-soft);font-size:12px;">নির্ধারিত হয়নি</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো শাখা তৈরি করা হয়নি</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
