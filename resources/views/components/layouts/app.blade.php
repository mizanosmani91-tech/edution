<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'বিদ্যাপঞ্জি') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
<div class="app" id="app" x-data="{ collapsed: false, mobileOpen: false }" :class="{ collapsed: collapsed, 'mobile-open': mobileOpen }">

    {{-- ============ SIDEBAR ============ --}}
    <aside class="sidebar">
        <div class="sidebar-head">
            <div class="sidebar-emblem">
                <svg viewBox="0 0 24 24" fill="none" stroke="#E7C767" stroke-width="1.6"><path d="M4 6.5c2.8-1.4 5.6-1.4 8 0v11c-2.4-1.4-5.2-1.4-8 0v-11Z"/><path d="M20 6.5c-2.8-1.4-5.6-1.4-8 0v11c2.4-1.4 5.2-1.4 8 0v-11Z"/></svg>
            </div>
            <div class="sidebar-brand">
                <div class="name">বিদ্যাপঞ্জি</div>
                <div class="inst">{{ auth()->user()->institution?->name ?? '' }}</div>
            </div>
        </div>

        <nav class="nav-scroll">
            <div class="nav-single {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="nav-btn">
                    <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 12 12 4l8 8"/><path d="M6 10v9h12v-9"/></svg></span>
                    <span class="lbl">ড্যাশবোর্ড</span>
                </a>
            </div>

            @php
                $stub = fn($label) => route('stub', urlencode($label));
                $activeIf = fn(...$patterns) => request()->routeIs($patterns) ? 'open active' : '';
            @endphp

            {{-- একাডেমিক --}}
            <div class="nav-module {{ $activeIf('students.*','teachers.*','routine.*') }}" x-data="{ open: {{ request()->routeIs(['students.*','teachers.*','routine.*']) ? 'true' : 'false' }} }" :class="{ open: open }">
                <button class="nav-btn" @click="open = !open" type="button">
                    <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5.5A2 2 0 0 1 6 4h13v14H6a2 2 0 0 0-2 2V5.5Z"/><path d="M19 18v3"/></svg></span>
                    <span class="lbl">একাডেমিক</span>
                    <span class="chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg></span>
                </button>
                <div class="sub-wrap"><div class="sub-inner"><div class="sub-list">
                    <a href="{{ route('academic.classes') }}" class="sub-item {{ request()->routeIs('academic.classes') ? 'active' : '' }}">ক্লাস ও সেকশন</a>
                    <a href="{{ route('academic.departments') }}" class="sub-item {{ request()->routeIs('academic.departments') ? 'active' : '' }}">বিভাগ</a>
                    <a href="{{ route('academic.subjects') }}" class="sub-item {{ request()->routeIs('academic.subjects') ? 'active' : '' }}">বিষয় ও সিলেবাস</a>
                    <a href="{{ route('routine.index') }}" class="sub-item {{ request()->routeIs('routine.*') ? 'active' : '' }}">ক্লাস রুটিন</a>
                    <a href="{{ $stub('একাডেমিক সেশন') }}" class="sub-item">একাডেমিক সেশন</a>
                    <a href="{{ $stub('হোমওয়ার্ক') }}" class="sub-item">হোমওয়ার্ক/অ্যাসাইনমেন্ট</a>
                    <a href="{{ $stub('লেসন প্ল্যান') }}" class="sub-item">লেসন প্ল্যান</a>
                    <a href="{{ $stub('প্রশ্ন ব্যাংক') }}" class="sub-item">প্রশ্ন ব্যাংক</a>
                </div></div></div>
            </div>

            {{-- ভর্তি --}}
            <div class="nav-module" x-data="{ open: false }" :class="{ open: open }">
                <button class="nav-btn" @click="open = !open" type="button">
                    <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="9" cy="8" r="3.3"/><path d="M3 20c1-3.6 3.4-5.4 6-5.4s5 1.8 6 5.4"/><path d="M17 8h4M19 6v4"/></svg></span>
                    <span class="lbl">ভর্তি</span>
                    <span class="chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg></span>
                </button>
                <div class="sub-wrap"><div class="sub-inner"><div class="sub-list">
                    <a href="{{ route('students.admission') }}" class="sub-item {{ request()->routeIs('students.admission') ? 'active' : '' }}">অনলাইন ভর্তি ফর্ম</a>
                    <a href="{{ $stub('ভর্তি আবেদন তালিকা') }}" class="sub-item">ভর্তি আবেদন তালিকা</a>
                    <a href="{{ $stub('আসন ব্যবস্থাপনা') }}" class="sub-item">আসন ব্যবস্থাপনা</a>
                    <a href="{{ $stub('ভর্তি পরীক্ষা') }}" class="sub-item">ভর্তি পরীক্ষা/ইন্টারভিউ</a>
                    <a href="{{ $stub('Waiting List') }}" class="sub-item">Waiting List</a>
                </div></div></div>
            </div>

            {{-- শিক্ষার্থী --}}
            <div class="nav-module {{ $activeIf('students.*','id-cards.*') }}" x-data="{ open: {{ request()->routeIs(['students.*','id-cards.*']) ? 'true' : 'false' }} }" :class="{ open: open }">
                <button class="nav-btn" @click="open = !open" type="button">
                    <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="4"/><path d="M4 20c1.5-4.5 5-6.5 8-6.5s6.5 2 8 6.5"/></svg></span>
                    <span class="lbl">শিক্ষার্থী</span>
                    <span class="chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg></span>
                </button>
                <div class="sub-wrap"><div class="sub-inner"><div class="sub-list">
                    <a href="{{ route('students.index') }}" class="sub-item {{ request()->routeIs('students.*') ? 'active' : '' }}">শিক্ষার্থী তালিকা</a>
                    <a href="{{ route('id-cards.index') }}" class="sub-item {{ request()->routeIs('id-cards.*') ? 'active' : '' }}">আইডি কার্ড জেনারেশন</a>
                    <a href="{{ $stub('প্রমোশন') }}" class="sub-item">প্রমোশন</a>
                    <a href="{{ $stub('Transfer Certificate') }}" class="sub-item">Transfer Certificate</a>
                    <a href="{{ $stub('Character Certificate') }}" class="sub-item">Character Certificate</a>
                    <a href="{{ $stub('আচরণ রেকর্ড') }}" class="sub-item">Discipline/আচরণ রেকর্ড</a>
                    <a href="{{ $stub('স্বাস্থ্য তথ্য') }}" class="sub-item">স্বাস্থ্য তথ্য</a>
                    <a href="{{ $stub('Alumni') }}" class="sub-item">Alumni</a>
                </div></div></div>
            </div>

            {{-- শিক্ষক ও স্টাফ --}}
            <div class="nav-module {{ $activeIf('teachers.*') }}" x-data="{ open: {{ request()->routeIs('teachers.*') ? 'true' : 'false' }} }" :class="{ open: open }">
                <button class="nav-btn" @click="open = !open" type="button">
                    <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M8 20h8"/></svg></span>
                    <span class="lbl">শিক্ষক ও স্টাফ</span>
                    <span class="chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg></span>
                </button>
                <div class="sub-wrap"><div class="sub-inner"><div class="sub-list">
                    <a href="{{ route('teachers.index') }}" class="sub-item {{ request()->routeIs('teachers.*') ? 'active' : '' }}">শিক্ষক তালিকা</a>
                    <a href="{{ route('teachers.hire') }}" class="sub-item {{ request()->routeIs('teachers.hire') ? 'active' : '' }}">নিয়োগ</a>
                    <a href="{{ $stub('পে-রোল') }}" class="sub-item">পে-রোল/বেতন</a>
                    <a href="{{ route('leave-requests.index') }}" class="sub-item">ছুটি ব্যবস্থাপনা</a>
                    <a href="{{ $stub('Performance') }}" class="sub-item">Performance/মূল্যায়ন</a>
                </div></div></div>
            </div>

            {{-- হাজিরা --}}
            <div class="nav-module {{ $activeIf('attendance.*','staff-attendance.*','attendance-report.*') }}" x-data="{ open: {{ request()->routeIs(['attendance.*','staff-attendance.*','attendance-report.*']) ? 'true' : 'false' }} }" :class="{ open: open }">
                <button class="nav-btn" @click="open = !open" type="button">
                    <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="m8.5 12 2.3 2.3L16 9.7"/></svg></span>
                    <span class="lbl">হাজিরা</span>
                    <span class="chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg></span>
                </button>
                <div class="sub-wrap"><div class="sub-inner"><div class="sub-list">
                    <a href="{{ route('attendance.index') }}" class="sub-item {{ request()->routeIs('attendance.*') ? 'active' : '' }}">শিক্ষার্থী হাজিরা</a>
                    <a href="{{ route('staff-attendance.index') }}" class="sub-item {{ request()->routeIs('staff-attendance.*') ? 'active' : '' }}">স্টাফ হাজিরা</a>
                    <a href="{{ route('leave-requests.index') }}" class="sub-item">ছুটির আবেদন</a>
                    <a href="{{ route('attendance-report.index') }}" class="sub-item {{ request()->routeIs('attendance-report.*') ? 'active' : '' }}">হাজিরা রিপোর্ট</a>
                </div></div></div>
            </div>

            {{-- পরীক্ষা ও ফলাফল --}}
            <div class="nav-module" x-data="{ open: false }" :class="{ open: open }">
                <button class="nav-btn" @click="open = !open" type="button">
                    <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 3h6l1 3H8l1-3Z"/><rect x="5" y="6" width="14" height="15" rx="2"/><path d="M9 12h6M9 16h6"/></svg></span>
                    <span class="lbl">পরীক্ষা ও ফলাফল</span>
                    <span class="chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg></span>
                </button>
                <div class="sub-wrap"><div class="sub-inner"><div class="sub-list">
                    <a href="{{ $stub('পরীক্ষার সময়সূচি') }}" class="sub-item">পরীক্ষার সময়সূচি</a>
                    <a href="{{ $stub('মার্কস এন্ট্রি') }}" class="sub-item">মার্কস এন্ট্রি</a>
                    <a href="{{ $stub('Result Weighting') }}" class="sub-item">Result Weighting</a>
                    <a href="{{ $stub('GPA গ্রেড') }}" class="sub-item">GPA/গ্রেড ক্যালকুলেশন</a>
                    <a href="{{ $stub('Merit List') }}" class="sub-item">Merit List/Tabulation</a>
                    <a href="{{ route('marksheet.class') }}" class="sub-item">রিপোর্ট কার্ড/মার্কশিট</a>
                    <a href="{{ route('admit-cards.class') }}" class="sub-item">প্রবেশপত্র (Admit Card)</a>
                    <a href="{{ $stub('কওমি গ্রেডিং') }}" class="sub-item">কওমি গ্রেডিং</a>
                </div></div></div>
            </div>

            {{-- ফি/অর্থ --}}
            <div class="nav-module {{ $activeIf('fees.*','fee-structures.*','expenses.*','income-expense-report.*') }}" x-data="{ open: {{ request()->routeIs(['fees.*','fee-structures.*','expenses.*','income-expense-report.*']) ? 'true' : 'false' }} }" :class="{ open: open }">
                <button class="nav-btn" @click="open = !open" type="button">
                    <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3.5" y="7" width="17" height="12" rx="2.5"/><path d="M3.5 11h17"/><circle cx="16.5" cy="15" r="1.4"/></svg></span>
                    <span class="lbl">ফি/অর্থ</span>
                    <span class="chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg></span>
                </button>
                <div class="sub-wrap"><div class="sub-inner"><div class="sub-list">
                    <a href="{{ route('fee-structures.index') }}" class="sub-item {{ request()->routeIs('fee-structures.*') ? 'active' : '' }}">ফি স্ট্রাকচার সেটআপ</a>
                    <a href="{{ route('fees.index') }}" class="sub-item {{ request()->routeIs('fees.*') ? 'active' : '' }}">ফি সংগ্রহ</a>
                    <a href="{{ $stub('অনলাইন পেমেন্ট') }}" class="sub-item">অনলাইন পেমেন্ট গেটওয়ে</a>
                    <a href="{{ route('fees.index') }}" class="sub-item">বকেয়া তালিকা</a>
                    <a href="{{ $stub('বৃত্তি ও মওকুফ') }}" class="sub-item">বৃত্তি/মওকুফ</a>
                    <a href="{{ route('expenses.index') }}" class="sub-item {{ request()->routeIs('expenses.*') ? 'active' : '' }}">খরচ/ব্যয় ট্র্যাকিং</a>
                    <a href="{{ route('income-expense-report.index') }}" class="sub-item {{ request()->routeIs('income-expense-report.*') ? 'active' : '' }}">আয়-ব্যয় রিপোর্ট</a>
                </div></div></div>
            </div>

            {{-- লাইব্রেরি --}}
            <div class="nav-module" x-data="{ open: false }" :class="{ open: open }">
                <button class="nav-btn" @click="open = !open" type="button">
                    <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 4h6a2 2 0 0 1 2 2v14a2 2 0 0 0-2-2H4V4Z"/><path d="M20 4h-6a2 2 0 0 0-2 2v14a2 2 0 0 1 2-2h6V4Z"/></svg></span>
                    <span class="lbl">লাইব্রেরি</span>
                    <span class="chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg></span>
                </button>
                <div class="sub-wrap"><div class="sub-inner"><div class="sub-list">
                    <a href="{{ route('books.index') }}" class="sub-item {{ request()->routeIs('books.*') ? 'active' : '' }}">বই তালিকা</a>
                    <a href="{{ route('book-issues.index') }}" class="sub-item {{ request()->routeIs('book-issues.*') ? 'active' : '' }}">ইস্যু/রিটার্ন</a>
                    <a href="{{ route('book-issues.index') }}" class="sub-item">জরিমানা</a>
                </div></div></div>
            </div>

            {{-- পরিবহন --}}
            <div class="nav-module" x-data="{ open: false }" :class="{ open: open }">
                <button class="nav-btn" @click="open = !open" type="button">
                    <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="9" width="18" height="8" rx="2"/><circle cx="7.5" cy="18" r="1.5"/><circle cx="16.5" cy="18" r="1.5"/></svg></span>
                    <span class="lbl">পরিবহন</span>
                    <span class="chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg></span>
                </button>
                <div class="sub-wrap"><div class="sub-inner"><div class="sub-list">
                    <a href="{{ route('transport.index') }}" class="sub-item {{ request()->routeIs('transport.*') ? 'active' : '' }}">রুট ও গাড়ি</a>
                    <a href="{{ route('transport.index') }}" class="sub-item">ছাত্র-পরিবহন সংযুক্তি</a>
                </div></div></div>
            </div>

            {{-- হোস্টেল --}}
            <div class="nav-module" x-data="{ open: false }" :class="{ open: open }">
                <button class="nav-btn" @click="open = !open" type="button">
                    <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 11 12 4l9 7"/><path d="M5 10v10h14V10"/></svg></span>
                    <span class="lbl">হোস্টেল</span>
                    <span class="chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg></span>
                </button>
                <div class="sub-wrap"><div class="sub-inner"><div class="sub-list">
                    <a href="{{ route('hostel.index') }}" class="sub-item {{ request()->routeIs('hostel.*') ? 'active' : '' }}">রুম/সিট বরাদ্দ</a>
                    <a href="{{ route('hostel.index') }}" class="sub-item">হোস্টেল ফি</a>
                </div></div></div>
            </div>

            {{-- যোগাযোগ --}}
            <div class="nav-module {{ $activeIf('chat.*','notice-board.*') }}" x-data="{ open: {{ request()->routeIs(['chat.*','notice-board.*']) ? 'true' : 'false' }} }" :class="{ open: open }">
                <button class="nav-btn" @click="open = !open" type="button">
                    <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5h16v11H8l-4 4V5Z"/></svg></span>
                    <span class="lbl">যোগাযোগ</span>
                    <span class="chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg></span>
                </button>
                <div class="sub-wrap"><div class="sub-inner"><div class="sub-list">
                    <a href="{{ route('notice-board.index') }}" class="sub-item {{ request()->routeIs('notice-board.*') ? 'active' : '' }}">নোটিশ বোর্ড</a>
                    <a href="{{ route('chat.index') }}" class="sub-item {{ request()->routeIs('chat.*') ? 'active' : '' }}">মেসেজিং/চ্যাট</a>
                    <a href="{{ $stub('SMS Gateway') }}" class="sub-item">SMS Gateway</a>
                    <a href="{{ $stub('Email Notification') }}" class="sub-item">Email Notification</a>
                    <a href="{{ $stub('অভিযোগ বাক্স') }}" class="sub-item">Complaint/Suggestion</a>
                </div></div></div>
            </div>

            {{-- পোর্টাল --}}
            <div class="nav-module" x-data="{ open: false }" :class="{ open: open }">
                <button class="nav-btn" @click="open = !open" type="button">
                    <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18"/></svg></span>
                    <span class="lbl">পোর্টাল</span>
                    <span class="chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg></span>
                </button>
                <div class="sub-wrap"><div class="sub-inner"><div class="sub-list">
                    <a href="{{ route('portal.guardian') }}" class="sub-item">অভিভাবক পোর্টাল</a>
                    <a href="{{ route('portal.student') }}" class="sub-item">শিক্ষার্থী পোর্টাল</a>
                    <a href="{{ route('portal.teacher') }}" class="sub-item">শিক্ষক পোর্টাল</a>
                </div></div></div>
            </div>

            {{-- রিপোর্ট --}}
            <div class="nav-module" x-data="{ open: false }" :class="{ open: open }">
                <button class="nav-btn" @click="open = !open" type="button">
                    <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 19V5M4 19h16M8 15v-4m4 4V9m4 6v-8"/></svg></span>
                    <span class="lbl">রিপোর্ট</span>
                    <span class="chev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg></span>
                </button>
                <div class="sub-wrap"><div class="sub-inner"><div class="sub-list">
                    <a href="{{ route('export.students') }}" class="sub-item">শিক্ষার্থী রিপোর্ট (Export)</a>
                    <a href="{{ route('export.attendance') }}" class="sub-item">হাজিরা রিপোর্ট (Export)</a>
                    <a href="{{ route('export.fees') }}" class="sub-item">ফি রিপোর্ট (Export)</a>
                </div></div></div>
            </div>

            <div class="nav-single {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <a href="{{ route('settings.index') }}" class="nav-btn">
                    <span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"/></svg></span>
                    <span class="lbl">সেটিংস</span>
                </a>
            </div>
        </nav>

        <div class="sidebar-foot">
            <button class="collapse-btn" @click="collapsed = !collapsed" type="button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 4v16M15 8l-4 4 4 4"/></svg>
                <span>সাইডবার সংকুচিত করুন</span>
            </button>
            <form method="POST" action="{{ route('logout') }}" class="mt-1">
                @csrf
                <button type="submit" class="collapse-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                    <span>লগআউট</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- ============ MAIN ============ --}}
    <main class="main">
        <div class="topbar">
            <button class="menu-toggle" @click="mobileOpen = !mobileOpen" type="button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="breadcrumb">
                <div class="path">{{ auth()->user()->institution?->name ?? '' }}</div>
                <h1>{{ $title ?? 'ড্যাশবোর্ড' }}</h1>
            </div>
            <div class="topbar-actions">
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" placeholder="খুঁজুন...">
                </div>
                @livewire('notification-bell')
                <div class="profile-chip">
                    <div class="avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</div>
                    <div class="who">
                        <div>{{ auth()->user()->name }}</div>
                        <div class="role">{{ ucfirst(auth()->user()->role) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            {{ $slot }}
        </div>
    </main>
</div>

@livewireScripts
</body>
</html>