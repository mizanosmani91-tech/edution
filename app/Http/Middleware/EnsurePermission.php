<?php

namespace App\Http\Middleware;

use App\Support\Permissions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsurePermission — `->middleware('permission:fees.manage')`
 *
 * একাধিক চাবি দিলে (কমা দিয়ে) যেকোনো একটা থাকলেই চলবে।
 * Fail-closed: ইউজার না থাকলে, চাবি অচেনা হলে, বা চাবিই না দিলে ৪০৩।
 *
 * Livewire আপডেট রিকোয়েস্টেও একই চেক চলে (AppServiceProvider-এ persistent
 * middleware হিসেবে নিবন্ধিত), যাতে পারমিশন তুলে নেওয়ার পর খোলা থাকা
 * পেজ থেকেও আর অ্যাকশন চালানো না যায়।
 */
class EnsurePermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        abort_unless($user, 403, 'এই পেজ দেখার অনুমতি আপনার নেই।');
        abort_if($permissions === [], 403, 'এই পেজ দেখার অনুমতি আপনার নেই।');

        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }

        abort(403, 'এই পেজ দেখার অনুমতি আপনার নেই।');
    }
}
