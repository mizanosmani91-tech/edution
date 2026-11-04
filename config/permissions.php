<?php

/**
 * পারমিশন ক্যাটালগ — কোন পেজ/কাজ কোন "চাবি" (permission key) চায়, এবং বিদ্যমান
 * রোলগুলো ডিফল্টভাবে কোন চাবি পায়।
 *
 * নিয়ম:
 *   - রুট `->middleware('permission:fees.manage')` দিয়ে চাবি চায়। একাধিক হলে
 *     কমা দিয়ে (OR): 'permission:a,b'।
 *   - `admin` সব চাবি পায় ('*') — নিজেকে লক আউট করা যায় না।
 *   - এখানে না-থাকা চাবি রুটে বসালে tests/Feature/PermissionRoutesTest ফেল করবে।
 *   - নতুন রুট যোগ করলে একটা চাবি দিতেই হবে (বা সেই টেস্টের allowlist-এ কারণসহ
 *     রাখতে হবে) — যাতে ভুলে কোনো পেজ সবার জন্য খোলা না থাকে।
 *
 * ধাপ ২-এ এই ডিফল্টগুলো প্রতিষ্ঠানভিত্তিক কাস্টম রোলে বদলানো যাবে।
 */
return [

    'permissions' => [
        // ── সাধারণ ──
        'calendar.view' => 'অ্যাকাডেমিক ক্যালেন্ডার দেখা',
        'chat.use' => 'মেসেজিং/চ্যাট',
        'notices.manage' => 'নোটিশ বোর্ড',
        'complaints.use' => 'অভিযোগ/পরামর্শ',

        // ── একাডেমিক ──
        'academic.manage' => 'শ্রেণি, বিভাগ, বিষয়, সেশন, রুটিন',
        'homework.manage' => 'হোমওয়ার্ক',
        'classdiary.manage' => 'ক্লাস ডায়েরি',
        'quizzes.manage' => 'অনলাইন কুইজ তৈরি',
        'quizzes.take' => 'কুইজে অংশ নেওয়া',
        'lessons.manage' => 'লেসন প্ল্যান',
        'questionbank.manage' => 'প্রশ্ন ব্যাংক',
        'hifz.manage' => 'হিফজ/কুরআন অগ্রগতি',

        // ── ভর্তি ও শিক্ষার্থী ──
        'admission.manage' => 'ভর্তি ব্যবস্থাপনা',
        'students.manage' => 'শিক্ষার্থী তালিকা ও রেকর্ড (আইডি কার্ড, প্রমোশন, সনদ, স্বাস্থ্য, শৃঙ্খলা, অ্যালামনাই)',
        'students.profile' => 'শিক্ষার্থী প্রোফাইল দেখা',

        // ── শিক্ষক ও কর্মী ──
        'teachers.manage' => 'শিক্ষক তালিকা, নিয়োগ, প্রোফাইল',
        'payroll.manage' => 'পে-রোল/বেতন',
        'performance.manage' => 'কর্মমূল্যায়ন',
        'leave.manage' => 'ছুটির আবেদন',

        // ── হাজিরা ──
        'attendance.take' => 'শিক্ষার্থী হাজিরা ও রিপোর্ট',
        'staff-attendance.manage' => 'স্টাফ হাজিরা',

        // ── পরীক্ষা ──
        'exams.schedule' => 'পরীক্ষার সময়সূচি',
        'exams.marks' => 'নম্বর এন্ট্রি',
        'exams.results' => 'ফলাফল, মেধাতালিকা, রিপোর্ট কার্ড',
        'exams.papers' => 'প্রশ্নপত্র',
        'exams.documents' => 'সিট প্ল্যান, প্রবেশপত্র ও পরীক্ষার কাগজপত্র',
        'marksheet.student' => 'শিক্ষার্থীর মার্কশিট',

        // ── অর্থ ──
        'fees.manage' => 'ফি, বৃত্তি, কিস্তি',
        'fees.receipt' => 'ফির রশিদ দেখা',
        'fees.pay' => 'অনলাইনে ফি দেওয়া',
        'finance.manage' => 'খরচ, আয়-ব্যয় রিপোর্ট, বাজেট',
        'gateway.manage' => 'পেমেন্ট/SMS/ইমেইল গেটওয়ে সেটিং',

        // ── সুবিধা ──
        'library.manage' => 'লাইব্রেরি',
        'transport.manage' => 'ট্রান্সপোর্ট',
        'transport.track' => 'গাড়ির লাইভ ট্র্যাকিং',
        'hostel.manage' => 'হোস্টেল',
        'visitors.manage' => 'ভিজিটর লগ',
        'inventory.manage' => 'ইনভেন্টরি',

        // ── ডেটা ও প্রশাসন ──
        'data.import' => 'ডেটা ইম্পোর্ট',
        'data.export' => 'ডেটা এক্সপোর্ট',
        'settings.manage' => 'প্রতিষ্ঠানের সেটিংস',
        'billing.manage' => 'বিলিং',
        'audit.view' => 'অডিট লগ',
        'support.manage' => 'সাপোর্ট টিকেট',

        // ── পোর্টাল ──
        'portal.guardian' => 'অভিভাবক পোর্টাল',
        'portal.student' => 'শিক্ষার্থী পোর্টাল',
        'portal.teacher' => 'শিক্ষক পোর্টাল',
    ],

    'defaults' => [
        'admin' => ['*'],

        'teacher' => [
            'calendar.view', 'chat.use', 'notices.manage', 'complaints.use',
            'homework.manage', 'classdiary.manage', 'quizzes.manage',
            'students.profile', 'leave.manage', 'attendance.take',
            'exams.schedule', 'exams.marks', 'exams.results', 'exams.papers',
            'exams.documents', 'marksheet.student',
            'portal.teacher',
        ],

        'guardian' => [
            'calendar.view', 'chat.use', 'students.profile', 'marksheet.student',
            'fees.receipt', 'fees.pay', 'portal.guardian',
        ],

        'student' => [
            'calendar.view', 'chat.use', 'students.profile', 'marksheet.student',
            'fees.receipt', 'quizzes.take', 'portal.student',
        ],
    ],
];
