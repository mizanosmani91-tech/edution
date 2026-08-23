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
// ⚠️ গুরুত্বপূর্ণ: Livewire v3 নিজের বান্ডেলে Alpine.js নিয়ে আসে এবং
// @livewireScripts (body-এর শেষে, সিঙ্ক্রোনাস স্ক্রিপ্ট) দিয়ে সেটা আগেই
// চালু (Alpine.start()) করে ফেলে — যেসব পেজে Livewire আছে। আমাদের এই
// @vite মডিউল স্ক্রিপ্ট (deferred, head-এ) সবসময় তার পরে রান হয়। তাই
// window.Alpine ইতিমধ্যে সেট থাকলে সেটাই আসল/Livewire-প্লাগইন-যুক্ত
// ইনস্ট্যান্স ধরে নিয়ে দ্বিতীয়বার Alpine.start() করা হচ্ছে না —
// এটা করলে দুটো আলাদা Alpine ইনস্ট্যান্স একই DOM প্রসেস করার চেষ্টা করে,
// ফলে wire:click/wire:model সহ পুরো অ্যাপের Livewire ইন্টারঅ্যাকশন
// (মডাল খোলা, ফর্ম সাবমিট ইত্যাদি) নিঃশব্দে ভেঙে যাচ্ছিল। যেসব পেজে
// Livewire নেই (যেমন লগইন পেজ), সেখানে window.Alpine সেট থাকবে না,
// তাই এই কোডই নিজে থেকে Alpine চালু করবে — আগের মতোই কাজ করবে।
if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.start();
}

import QRCode from 'qrcode';

// সার্টিফিকেট/মার্কশিটের QR-ভেরিফিকেশন কোড ক্লায়েন্ট-সাইডে জেনারেট
// করার জন্য — সার্ভার-সাইড PHP QR লাইব্রেরি (GD/Imagick নির্ভর) ব্যবহার
// না করে ব্রাউজারেই <canvas>-এ আঁকা হয়, তাই আগের PhpSpreadsheet-এর
// মতো সার্ভার রিসোর্স চাপের ঝুঁকি নেই। window.edutionRenderQr(canvasEl, url)
// কল করলেই canvas-এ QR কোড বসে যাবে।
window.edutionRenderQr = function (canvasEl, url) {
    if (!canvasEl || !url) return;
    QRCode.toCanvas(canvasEl, url, { width: 140, margin: 1 }, function (err) {
        if (err) console.error('QR render error:', err);
    });
};
