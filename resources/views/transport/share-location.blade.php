<!doctype html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>লোকেশন শেয়ার — {{ $route->route_name }}</title>
    <style>
        body { font-family: system-ui, -apple-system, "Noto Sans Bengali", sans-serif; background:#f4f6f9; margin:0; padding:0; display:flex; min-height:100vh; align-items:center; justify-content:center; }
        .card { background:#fff; border-radius:16px; box-shadow:0 4px 24px rgba(0,0,0,.08); padding:28px 22px; max-width:380px; width:92%; text-align:center; }
        h1 { font-size:18px; margin:0 0 6px; }
        p.sub { color:#667; font-size:13px; margin:0 0 18px; }
        .status { border-radius:12px; padding:14px; font-size:14px; margin-bottom:16px; }
        .status.idle { background:#f0f2f5; color:#556; }
        .status.on { background:#e7f7ee; color:#1a7f4e; }
        .status.err { background:#fdecea; color:#c0392b; }
        button { width:100%; padding:14px; border:none; border-radius:12px; font-size:16px; font-weight:600; cursor:pointer; }
        .start { background:#1a7f4e; color:#fff; }
        .stop { background:#c0392b; color:#fff; }
        .meta { margin-top:14px; font-size:12px; color:#889; line-height:1.6; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🚌 {{ $route->route_name }}</h1>
        <p class="sub">গাড়ি নং: {{ $route->vehicle_no }} — লোকেশন শেয়ার চালু করলে স্কুল লাইভ ট্র্যাক করতে পারবে</p>

        <div id="status" class="status idle">এখনো শুরু হয়নি</div>

        <button id="toggleBtn" class="start" onclick="toggleTracking()">লোকেশন শেয়ার শুরু করুন</button>

        <div class="meta">এই ফোনের ব্রাউজার খোলা রাখুন যতক্ষণ ট্রিপ চলবে।<br>ব্যাটারি বাঁচাতে ট্রিপ শেষে বন্ধ করে দিন।</div>
    </div>

    <script>
        const token = @json($route->tracking_token);
        const updateUrl = @json(route('transport-tracking.update', $route->tracking_token));
        let watchId = null;
        let tracking = false;

        function setStatus(cls, text) {
            const el = document.getElementById('status');
            el.className = 'status ' + cls;
            el.textContent = text;
        }

        function toggleTracking() {
            if (tracking) {
                stopTracking();
            } else {
                startTracking();
            }
        }

        function startTracking() {
            if (!navigator.geolocation) {
                setStatus('err', 'এই ব্রাউজার লোকেশন সাপোর্ট করে না');
                return;
            }
            tracking = true;
            document.getElementById('toggleBtn').textContent = 'লোকেশন শেয়ার বন্ধ করুন';
            document.getElementById('toggleBtn').className = 'stop';
            setStatus('idle', 'লোকেশন খোঁজা হচ্ছে...');

            watchId = navigator.geolocation.watchPosition(function (pos) {
                setStatus('on', 'লাইভ — শেষ পাঠানো হয়েছে ' + new Date().toLocaleTimeString('bn-BD'));
                fetch(updateUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '' },
                    body: JSON.stringify({ lat: pos.coords.latitude, lng: pos.coords.longitude }),
                }).catch(function () {});
            }, function (err) {
                setStatus('err', 'লোকেশন অ্যাক্সেস দিতে হবে (ব্রাউজার সেটিংসে অনুমতি দিন)');
            }, { enableHighAccuracy: true, maximumAge: 10000, timeout: 20000 });
        }

        function stopTracking() {
            tracking = false;
            if (watchId !== null) navigator.geolocation.clearWatch(watchId);
            document.getElementById('toggleBtn').textContent = 'লোকেশন শেয়ার শুরু করুন';
            document.getElementById('toggleBtn').className = 'start';
            setStatus('idle', 'বন্ধ করা হয়েছে');
        }
    </script>
</body>
</html>
