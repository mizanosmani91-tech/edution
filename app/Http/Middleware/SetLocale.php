<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/**
 * সেশনে সংরক্ষিত ভাষা (bn/en) অনুযায়ী প্রতিটা রিকুয়েস্টে অ্যাপের locale
 * সেট করে — ব্যবহারকারী টগল করলে পুরো অ্যাপ জুড়ে সেই ভাষায় দেখায়।
 * ডিফল্ট ভাষা বাংলা (bn)।
 */
class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale', 'bn');
        if (! in_array($locale, ['bn', 'en'], true)) {
            $locale = 'bn';
        }
        App::setLocale($locale);

        return $next($request);
    }
}
