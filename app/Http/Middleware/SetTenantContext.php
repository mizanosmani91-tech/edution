<?php

namespace App\Http\Middleware;

use App\Models\Institution;
use App\Models\PlatformSetting;
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

        if ($blocked = $this->blockIfSuspended($request, $user, (string) $institutionId)) {
            return $blocked;
        }

        return $next($request);
    }

    /** স্থগিত অবস্থায় অ্যাডমিন যেসব Livewire কম্পোনেন্ট ব্যবহার করতে পারবে (বিল দেওয়ার জন্য)। */
    private const SUSPENDED_ALLOWED_COMPONENTS = ['billing-center', 'notification-bell', 'support-widget'];

    /**
     * বকেয়া/সাসপেনশনে প্রতিষ্ঠান স্থগিত হলে অ্যাক্সেস আটকানো।
     *
     * ⚠️ সুপারঅ্যাডমিন প্যানেলের `enforce_suspension` সুইচ চালু না থাকলে (ডিফল্ট) কিছুই বদলায় না —
     * ভুল হলে টাকা-দেওয়া স্কুলও লক হতে পারে, তাই চালু করার সিদ্ধান্ত মালিকের।
     *
     * - অ্যাডমিন: শুধু বিলিং পেজ (ও তার Livewire কাজ) পায় — যাতে বিল দিয়ে আবার চালু করতে পারে;
     *   বাকি যেকোনো পেজ বিলিংয়ে পাঠিয়ে দেওয়া হয়।
     * - শিক্ষক/অভিভাবক/শিক্ষার্থী: "সেবা সাময়িকভাবে বন্ধ" পাতা।
     * - লগআউট এই মিডলওয়্যারের বাইরে (শুধু `auth`), তাই সবসময় চলে।
     */
    private function blockIfSuspended(Request $request, $user, string $institutionId): ?Response
    {
        if (! PlatformSetting::getBool('enforce_suspension', false)) {
            return null;
        }

        $institution = Institution::query()->find($institutionId);

        if (! $institution || ! ($institution->status === 'suspended' || $institution->billing_suspended === true)) {
            return null;
        }

        $isLivewire = $request->is('livewire/*') || $request->hasHeader('X-Livewire');

        if ($user->role === 'admin') {
            if ($request->routeIs('billing.*', 'password.force-change')) {
                return null;
            }

            if ($isLivewire && self::componentsAllowed((array) $request->input('components', []))) {
                return null;
            }

            if ($isLivewire || $request->expectsJson()) {
                abort(403, 'বিলিং বকেয়ার কারণে অ্যাকাউন্ট স্থগিত। বিলিং পেজ থেকে পরিশোধ করুন।');
            }

            return redirect()->route('billing.index')
                ->with('guard_notice', 'বিলিং বকেয়ার কারণে আপনার অ্যাকাউন্ট স্থগিত। বিল পরিশোধ করলে আবার চালু হবে।');
        }

        if ($isLivewire || $request->expectsJson()) {
            abort(403, 'প্রতিষ্ঠানের সেবা সাময়িকভাবে বন্ধ আছে।');
        }

        return response()->view('errors.suspended', ['institution' => $institution], 403);
    }

    /**
     * Livewire আপডেটের সব কম্পোনেন্ট অনুমোদিত তালিকায় কিনা। কম্পোনেন্টের নাম snapshot-এর
     * `memo.name` থেকে। পড়া না গেলে (পেলোডের বিন্যাস অচেনা) আটকানো হয় না — যাতে স্থগিত
     * অ্যাডমিন বিল দেওয়ার পথ হারিয়ে না ফেলে; ভুল-ইতিবাচকের চেয়ে এটা কম ক্ষতিকর।
     *
     * @param  array<int,mixed>  $components
     */
    public static function componentsAllowed(array $components): bool
    {
        if ($components === []) {
            return true;
        }

        foreach ($components as $component) {
            $snapshot = is_array($component) ? ($component['snapshot'] ?? null) : null;
            $decoded = is_string($snapshot) ? json_decode($snapshot, true) : null;
            $name = is_array($decoded) ? ($decoded['memo']['name'] ?? null) : null;

            if (! is_string($name)) {
                return true; // পড়া গেল না → আটকাই না
            }

            if (! in_array($name, self::SUSPENDED_ALLOWED_COMPONENTS, true)) {
                return false;
            }
        }

        return true;
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
