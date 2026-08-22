import Chart from 'chart.js/auto';

// window.Chart এর মাধ্যমে ড্যাশবোর্ডের ইনলাইন স্ক্রিপ্ট থেকে অ্যাক্সেসযোগ্য —
// আগে external CDN থেকে লোড করা হতো, যেটা network/ad-blocker/firewall এর
// কারণে চুপচাপ ফেইল করলে চার্ট কখনো দেখাই যেত না। এখন Vite বান্ডেলের অংশ,
// তাই অ্যাপের বাকি সব কিছুর মতোই নির্ভরযোগ্যভাবে লোড হবে।
window.Chart = Chart;
window.dispatchEvent(new Event('chartjs:ready'));

import Alpine from 'alpinejs';

// আগে auth/login ও auth/superadmin-login পেজে Alpine.js external CDN
// (cdn.jsdelivr.net) থেকে লোড করা হতো — Chart.js এর মতোই এটাও Capacitor
// Android অ্যাপের WebView-তে নেটওয়ার্ক/DNS ইস্যুর কারণে চুপচাপ ফেইল করছিল,
// ফলে রোল ট্যাব ও অন্যান্য x-data চালিত অংশ কখনো রেন্ডার হচ্ছিল না।
// এখন Vite বান্ডেলের অংশ হিসেবে লোড হবে — নির্ভরযোগ্য, অফলাইন-বান্ধব।
window.Alpine = Alpine;
Alpine.start();
