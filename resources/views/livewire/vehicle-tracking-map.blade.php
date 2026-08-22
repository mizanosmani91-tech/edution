<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">পরিবহন / লাইভ ট্র্যাকিং</div>
            <h2>লাইভ গাড়ি ট্র্যাকিং</h2>
            <p>ড্রাইভারের ফোন থেকে পাঠানো GPS লোকেশন অনুযায়ী গাড়িগুলোর অবস্থান ম্যাপে দেখুন</p>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

    <div id="vt-map" style="width:100%;height:460px;border-radius:14px;overflow:hidden;border:1px solid var(--line);margin-bottom:18px;"></div>

    <div class="table-card">
        <table>
            <thead><tr><th>রুট</th><th>গাড়ি নং</th><th>ড্রাইভার</th><th>অবস্থা</th><th>শেয়ার লিংক (ড্রাইভারকে দিন)</th></tr></thead>
            <tbody>
                @forelse ($routes as $r)
                    <tr>
                        <td>{{ $r->route_name }}</td>
                        <td>{{ $r->vehicle_no }}</td>
                        <td>{{ $r->driver_name }} @if($r->driver_phone)<br><span style="color:var(--ink-soft);font-size:12px;">{{ $r->driver_phone }}</span>@endif</td>
                        <td>
                            @if ($r->isLocationLive())
                                <span class="badge" style="background:var(--good-bg);color:var(--good);">🟢 লাইভ ({{ $r->last_location_at->diffForHumans() }})</span>
                            @elseif ($r->last_location_at)
                                <span class="badge" style="background:var(--warn-bg,#fff3cd);color:var(--warn,#a86b00);">অফলাইন (শেষ: {{ $r->last_location_at->diffForHumans() }})</span>
                            @else
                                <span class="badge" style="background:var(--line);color:var(--ink-soft);">লোকেশন শুরু হয়নি</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;align-items:center;">
                                <input type="text" readonly value="{{ route('transport-tracking.share', $r->tracking_token) }}" style="font-size:12px;padding:6px 8px;" onclick="this.select()">
                                <button type="button" class="btn-outline" style="padding:6px 10px;font-size:12px;" onclick="navigator.clipboard.writeText('{{ route('transport-tracking.share', $r->tracking_token) }}'); this.textContent='কপি হয়েছে ✓'; setTimeout(()=>this.textContent='কপি',1500)">কপি</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;color:var(--ink-soft);">কোনো রুট তৈরি করা নেই</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        (function () {
            const map = L.map('vt-map').setView([23.8103, 90.4125], 12); // ডিফল্ট: ঢাকা
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(map);

            let markers = {};

            async function refresh() {
                try {
                    const res = await fetch('{{ route('transport-tracking.positions') }}');
                    const data = await res.json();
                    if (!data.length) return;

                    let bounds = [];
                    data.forEach(function (r) {
                        const latlng = [r.lat, r.lng];
                        bounds.push(latlng);
                        const label = r.route_name + ' (' + r.vehicle_no + ')' + (r.is_live ? ' 🟢' : ' — ' + r.updated_at);

                        if (markers[r.id]) {
                            markers[r.id].setLatLng(latlng).setPopupContent(label);
                        } else {
                            markers[r.id] = L.marker(latlng).addTo(map).bindPopup(label);
                        }
                    });

                    if (bounds.length && !window.__vtFitted) {
                        map.fitBounds(bounds, { padding: [30, 30] });
                        window.__vtFitted = true;
                    }
                } catch (e) {
                    // নেটওয়ার্ক সমস্যা হলে চুপচাপ পরের সাইকেলে আবার চেষ্টা করবে
                }
            }

            refresh();
            setInterval(refresh, 15000);
        })();
    </script>
</div>
