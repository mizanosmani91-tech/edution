<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * ComingSoonController — sidebar-এ সব মডিউল লিংক করা থাকে, কিন্তু যেগুলোর
 * আসল পেজ এখনো বানানো হয়নি, সেগুলো এই স্টাব পেজে যায়। পরে একটা একটা করে
 * routes/web.php-এ সেই নির্দিষ্ট মডিউলের route real controller/Livewire
 * দিয়ে replace করে দেওয়া হবে।
 */
class ComingSoonController extends Controller
{
    public function show(string $title)
    {
        return view('stub', ['title' => urldecode($title)]);
    }
}
