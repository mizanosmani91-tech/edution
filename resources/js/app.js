import Chart from 'chart.js/auto';

// window.Chart এর মাধ্যমে ড্যাশবোর্ডের ইনলাইন স্ক্রিপ্ট থেকে অ্যাক্সেসযোগ্য —
// আগে external CDN থেকে লোড করা হতো, যেটা network/ad-blocker/firewall এর
// কারণে চুপচাপ ফেইল করলে চার্ট কখনো দেখাই যেত না। এখন Vite বান্ডেলের অংশ,
// তাই অ্যাপের বাকি সব কিছুর মতোই নির্ভরযোগ্যভাবে লোড হবে।
window.Chart = Chart;
window.dispatchEvent(new Event('chartjs:ready'));
