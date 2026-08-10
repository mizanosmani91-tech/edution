<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Unauthenticated — tenant context resolve করা যায়নি।');
        }

        $institutionId = $user->institution_id ?? null;

        if ($institutionId === null) {
            abort(403, 'এই ইউজারের সাথে কোনো institution যুক্ত নেই — tenant route এ প্রবেশ নিষেধ।');
        }

        DB::statement(
            'SELECT set_config(?, ?, false)',
            ['app.current_institution_id', (string) $institutionId]
        );

        DB::statement(
            'SELECT set_config(?, ?, false)',
            ['app.current_user_id', (string) $user->id]
        );

        app()->instance('tenant.institution_id', $institutionId);
        app()->instance('tenant.current_user_id', $user->id);

        return $next($request);
    }
}
