<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\Notice;
use Illuminate\Http\Request;

/**
 * PublicSiteController — প্রতিটা প্রতিষ্ঠানের জন্য একটা পাবলিক (লগইন ছাড়াই
 * দেখা যায়) প্রোফাইল পেজ, তাদের সাবডোমেইনে। নোটিশ/ঘটনা বাইরের মানুষ
 * (ভবিষ্যৎ অভিভাবক, দর্শনার্থী) দেখতে পারবে — লগইন করা পোর্টালের বদলে।
 *
 * ⚠️ ইচ্ছাকৃতভাবে মূল '/' রুট বদলানো হয়নি (এখনো সরাসরি লগইন পেজ দেখায়) —
 * প্রতিষ্ঠানের স্টাফ/অভিভাবকরা এখন যেভাবে অভ্যস্ত সেটা ভাঙতে চাইনি।
 * এই পাবলিক সাইট আলাদা path এ (/school-profile), Facebook/গুগলে শেয়ার
 * করার জন্য উপযুক্ত।
 */
class PublicSiteController extends Controller
{
    public function show(Request $request)
    {
        $institution = Institution::resolveFromSubdomain($request->getHost());

        abort_unless($institution, 404);

        $notices = Notice::where('institution_id', $institution->id)
            ->published()
            ->where(function ($q) {
                $q->whereNull('audience')->orWhereJsonLength('audience', 0);
            })
            ->orderByDesc('is_pinned')
            ->orderByDesc('publish_at')
            ->limit(10)
            ->get();

        return view('public-site', [
            'institution' => $institution,
            'notices' => $notices,
        ]);
    }
}
