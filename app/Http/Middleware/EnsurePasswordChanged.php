<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsurePasswordChanged
 *
 * সাময়িক (superadmin approve করা বা reset করা) পাসওয়ার্ড দিয়ে লগইন করা
 * ইউজারকে (users.must_change_password = true) অন্য কোনো পেজে যাওয়ার আগেই
 * বাধ্যতামূলক পাসওয়ার্ড পরিবর্তন পেজে পাঠিয়ে দেয় — সরাসরি URL টাইপ করেও
 * এড়ানো যাবে না।
 *
 * ⚠️ শুধু পেজ-লোড রুটে বসানো হয়েছে, Livewire-এর অভ্যন্তরীণ /livewire/update
 * AJAX রুটে না — নাহলে পাসওয়ার্ড-পরিবর্তন ফর্মের নিজের submit-ই কাজ করবে না।
 */
class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->must_change_password) {
            return $next($request);
        }

        $exemptRoutes = ['password.force-change', 'superadmin.password.force-change', 'logout'];

        if ($request->route() && in_array($request->route()->getName(), $exemptRoutes, true)) {
            return $next($request);
        }

        $target = $user->isSuperAdmin() ? 'superadmin.password.force-change' : 'password.force-change';

        return redirect()->route($target);
    }
}
