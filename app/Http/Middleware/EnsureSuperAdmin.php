<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureSuperAdmin
 *
 * সাধারণ tenant route এ SetTenantContext চলে (app.current_institution_id
 * সেট করে)। Superadmin এর কোনো institution_id নেই, তাই এই middleware
 * আলাদা — app.is_superadmin = true সেট করে, যেটা superadmin-only টেবিলের
 * (institution_payments) RLS policy পড়ে ("...OR is_superadmin")।
 *
 * ⚠️ এই দুইটা middleware কখনো একসাথে একই route এ লাগাবেন না — বিভ্রান্তিকর
 * এবং RLS policy design এর সাথে সাংঘর্ষিক।
 */
class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isSuperAdmin()) {
            abort(403, 'শুধু superadmin এই পেজ অ্যাক্সেস করতে পারবেন।');
        }

        DB::statement('SELECT set_config(?, ?, false)', ['app.is_superadmin', 'true']);
        // ইচ্ছাকৃতভাবে app.current_institution_id সেট করা হয়নি/খালি রাখা হলো —
        // superadmin কোনো নির্দিষ্ট institution এর সাথে বাঁধা না
        DB::statement('SELECT set_config(?, ?, false)', ['app.current_institution_id', '']);

        return $next($request);
    }
}
