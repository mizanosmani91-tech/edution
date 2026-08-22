/**
 * EDUTION Service Worker
 *
 * লক্ষ্য: PWA হিসেবে ইনস্টলযোগ্য হওয়া + সংক্ষিপ্ত নেট বিচ্ছিন্নতায় অ্যাপ
 * সম্পূর্ণ সাদা স্ক্রিন না দেখিয়ে একটা অফলাইন পেজ দেখানো। উপস্থিতি/মার্কস/ফি
 * এর মতো লাইভ ডেটা ক্যাশ করা হয় না — সবসময় নেটওয়ার্ক-ফার্স্ট, শুধু নেট
 * ব্যর্থ হলে ফলব্যাক দেখানো হয়। এভাবে পুরনো/ভুল ডেটা কখনো ক্যাশ থেকে
 * দেখানো হবে না।
 */
const CACHE_NAME = 'edution-shell-v1';
const OFFLINE_URL = '/offline.html';

const SHELL_ASSETS = [
    OFFLINE_URL,
    '/icons/icon-192.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(SHELL_ASSETS)).catch(() => {})
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
        ))
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    // শুধু GET, একই-অরিজিন পেজ নেভিগেশনের জন্য অফলাইন ফলব্যাক দরকার
    if (request.method !== 'GET') {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    // বাকি সব রিসোর্স স্বাভাবিকভাবে নেটওয়ার্ক থেকে যাবে (কোনো ক্যাশ-ফার্স্ট না,
    // যাতে পুরনো CSS/JS/ডেটা আটকে না থাকে)
});
