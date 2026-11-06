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

        // ⚠️ Livewire::setUpdateRoute (AppServiceProvider) সব Livewire action-এর
        // জন্য এই একই '/livewire/update' route ব্যবহার করে — তাই superadmin
        // প্যানেলের Livewire component (SuperadminInstitutionsList ইত্যাদি)
        // থেকে আসা wire:click কলও এখানে দিয়েই আসে। superadmin ইচ্ছাকৃতভাবেই
        // কোনো institution-এর সাথে যুক্ত না, তাই তাকে 403 দিয়ে আটকানো ভুল —
        // শুধু RLS is_superadmin ফ্ল্যাগ সেট করে দিয়ে পাস করিয়ে দেওয়া হচ্ছে।
        if ($user->isSuperAdmin()) {
            DB::statement("SELECT set_config(?, ?, false)", ['app.is_superadmin', 'true']);

            return $next($request);
        }

        // নিষ্ক্রিয় ইউজারের চলমান সেশনও বন্ধ — নতুন লগইন আগেই আটকানো হয়, কিন্তু
        // ইতিমধ্যে লগইন থাকা ইউজারকে এখানেই বের করে দেওয়া হয়।
        // `=== false` ইচ্ছাকৃত: কলাম না থাকলে/null হলে (মাইগ্রেশনের আগে) কাউকে আটকাবে না।
        if ($user->is_active === false) {
            return $this->rejectInactive($request);
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

    private function rejectInactive(Request $request): Response
    {
        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $message = 'এই অ্যাকাউন্টটি নিষ্ক্রিয় করা হয়েছে। প্রতিষ্ঠানের অ্যাডমিনের সাথে যোগাযোগ করুন।';

        if ($request->expectsJson() || $request->hasHeader('X-Livewire')) {
            abort(403, $message);
        }

        return redirect('/login')->withErrors(['email' => $message]);
    }
}
