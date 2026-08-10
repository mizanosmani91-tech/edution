<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * SetTenantContext
 *
 * প্রতিটা request-এ লগইন করা ইউজারের institution_id বের করে দুই জায়গায় সেট করে:
 *
 *   1. Postgres session variable (app.current_institution_id)
 *      -> এটা RLS policy গুলো পড়বে (current_setting('app.current_institution_id'))
 *      -> Supabase-এর auth.uid() যেভাবে RLS-এ ব্যবহৃত হতো, এটা ঠিক সেই role পালন করে
 *
 *   2. App container-এ singleton bind (Tenant::current())
 *      -> BelongsToTenant global scope এটা পড়ে query filter করার জন্য
 *
 * SECURITY NOTE:
 * - এই middleware অবশ্যই auth middleware-এর পরে চলতে হবে (user resolve হওয়ার পর)
 * - superadmin route-এ এই middleware স্কিপ করা উচিত (superadmin সব institution দেখে) —
 *   কিন্তু তখন RLS policy-তেও আলাদা bypass role/policy লাগবে, এখানে না
 * - যদি institution resolve করতে ব্যর্থ হয় (guest, বা institution ছাড়া user), request
 *   abort করা হয় — silently pass করা হয় না, কারণ silent pass মানে RLS filter ছাড়াই
 *   query চলে যাওয়ার ঝুঁকি
 */
class SetTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            // Auth middleware আগেই আটকানোর কথা, কিন্তু defense-in-depth হিসেবে এখানেও চেক
            abort(401, 'Unauthenticated — tenant context resolve করা যায়নি।');
        }

        $institutionId = $user->institution_id ?? null;

        // Superadmin-দের institution_id null থাকতে পারে — তাদের জন্য আলাদা route
        // group ব্যবহার করুন যেটাতে এই middleware অ্যাটাচ করা নেই।
        if ($institutionId === null) {
            abort(403, 'এই ইউজারের সাথে কোনো institution যুক্ত নেই — tenant route এ প্রবেশ নিষেধ।');
        }

        // ১. Postgres session variable সেট (RLS policy-র জন্য)
        DB::statement(
            'SELECT set_config(?, ?, false)',
            ['app.current_institution_id', (string) $institutionId]
        );

        // ⚠️ current_user_id-ও সেট করা হচ্ছে — আসল 029 migration এর
        // is_user_institution_admin() ফাংশন Supabase auth.uid() ব্যবহার করত,
        // এখন সেটার বদলে এই session variable পড়বে (নিচে নোট দেখুন)
        DB::statement(
            'SELECT set_config(?, ?, false)',
            ['app.current_user_id', (string) $user->id]
        );

        // ২. App-level bind — global scope এখান থেকে পড়বে
        app()->instance('tenant.institution_id', $institutionId);
        app()->instance('tenant.current_user_id', $user->id); // Message এর participant scope এর জন্য

        return $next($request);
    }
}

/**
 * IMPORTANT — Connection pooling নোট:
 *
 * যদি PgBouncer (transaction-mode pooling) ব্যবহার করেন, session variable
 * (set_config with persist=false) কানেকশন পুনর্ব্যবহার হওয়ার সময় "leak" করতে
 * পারে অন্য request-এ। এক্ষেত্রে প্রতিটা query transaction-এর ভেতরে চালান এবং
 * SET LOCAL ব্যবহার করুন (persist=true parameter দিয়ে set_config, transaction-scoped)।
 * এই ফাইলে সরলতার জন্য session-level দেখানো হয়েছে — production-এ পুলিং থাকলে
 * অবশ্যই transaction-scoped ভার্সনে পরিবর্তন করে নিন, নাহলে tenant leak হতে পারে।
 */
