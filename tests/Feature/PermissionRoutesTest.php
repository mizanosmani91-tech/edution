<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * গার্ডরেল: লগইন-করা ইউজারের জন্য সব রুটে একটা `permission:` চাবি থাকতেই হবে।
 * নতুন রুট যোগ করে চাবি দিতে ভুলে গেলে এই টেস্ট ফেল করবে — ফলে কোনো পেজ
 * অজান্তে "সব লগইন-করা ইউজারের জন্য খোলা" থেকে যেতে পারবে না।
 *
 * (ডাটাবেস লাগে না — শুধু রুট টেবিল ও কনফিগ পড়ে।)
 */
class PermissionRoutesTest extends TestCase
{
    /**
     * ইচ্ছাকৃতভাবে সব লগইন-করা ইউজারের জন্য খোলা রুট — প্রতিটার কারণ লেখা।
     *
     * @var array<string,string>  "METHOD uri" => কারণ
     */
    private const OPEN_ROUTES = [
        'GET dashboard' => 'হোম পেজ; সব রোলই দেখে',
        'GET settings' => 'প্রতিষ্ঠানের সাধারণ সেটিং পড়া (JSON); লেখা settings.manage-এ আটকানো',
        'GET routine' => 'ক্লাস রুটিন পড়া (JSON); লেখা/মোছা academic.manage-এ আটকানো',
        'GET coming-soon/{title}' => 'স্টাব পেজ, কোনো ডেটা নেই',
        'GET my-profile' => 'প্রত্যেকে নিজের নাম/ফোন/ইমেইল/পাসওয়ার্ড বদলায়; সব রোলের জন্য, কম্পোনেন্ট শুধু নিজের ইউজারেই কাজ করে',
    ];

    /** @return array<int,LaravelRoute> */
    private function protectedGroupRoutes(): array
    {
        return collect(Route::getRoutes()->getRoutes())
            ->filter(function (LaravelRoute $route) {
                $mw = $route->gatherMiddleware();

                // ওয়েবের মূল গ্রুপ (auth + tenant + password.change) এবং মোবাইল API গ্রুপ
                $isWebGroup = in_array('tenant.context', $mw, true) && in_array('password.change', $mw, true);
                $isApiGroup = in_array('tenant.context', $mw, true) && in_array('auth:sanctum', $mw, true);

                return $isWebGroup || $isApiGroup;
            })
            ->reject(fn (LaravelRoute $r) => in_array($r->uri(), ['livewire/update', 'api/logout'], true))
            ->values()
            ->all();
    }

    #[Test]
    public function the_guard_actually_finds_routes_to_check(): void
    {
        $this->assertGreaterThan(80, count($this->protectedGroupRoutes()), 'রুট ফিল্টার ভেঙে গেছে — টেস্টটা কিছুই যাচাই করছে না।');
    }

    #[Test]
    public function every_authenticated_route_requires_a_permission_or_is_explicitly_open(): void
    {
        $missing = [];

        foreach ($this->protectedGroupRoutes() as $route) {
            $required = Permissions::requiredPermissions($route->gatherMiddleware());
            $label = $route->methods()[0] . ' ' . $route->uri();

            if ($required === [] && ! array_key_exists($label, self::OPEN_ROUTES)) {
                $missing[] = $label;
            }
        }

        $this->assertSame([], $missing, "এই রুটগুলোতে permission: মিডলওয়্যার নেই:\n" . implode("\n", $missing));
    }

    #[Test]
    public function every_permission_used_by_a_route_exists_in_the_catalog(): void
    {
        $unknown = [];

        foreach (Route::getRoutes()->getRoutes() as $route) {
            foreach (Permissions::requiredPermissions($route->gatherMiddleware()) as $key) {
                if (! Permissions::isKnown($key)) {
                    $unknown[] = $route->uri() . ' → ' . $key;
                }
            }
        }

        $this->assertSame([], $unknown, "ক্যাটালগে নেই এমন চাবি:\n" . implode("\n", $unknown));
    }

    #[Test]
    public function every_catalog_permission_is_used_by_at_least_one_route(): void
    {
        $used = [];
        foreach (Route::getRoutes()->getRoutes() as $route) {
            array_push($used, ...Permissions::requiredPermissions($route->gatherMiddleware()));
        }

        $unused = array_diff(array_keys(Permissions::catalog()), array_unique($used));

        $this->assertSame([], array_values($unused), 'কোনো রুট এই চাবি চায় না (মৃত চাবি): ' . implode(', ', $unused));
    }

    #[Test]
    public function open_routes_allowlist_has_no_stale_entries(): void
    {
        $existing = collect($this->protectedGroupRoutes())
            ->map(fn (LaravelRoute $r) => $r->methods()[0] . ' ' . $r->uri())
            ->all();

        foreach (array_keys(self::OPEN_ROUTES) as $label) {
            $this->assertContains($label, $existing, "allowlist-এর রুট আর নেই: {$label}");
        }
    }

    #[Test]
    public function role_defaults_only_reference_known_permissions(): void
    {
        foreach (config('permissions.defaults') as $role => $granted) {
            foreach ($granted as $key) {
                $this->assertTrue($key === '*' || Permissions::isKnown($key), "{$role} ডিফল্টে অচেনা চাবি: {$key}");
            }
        }
    }

    #[Test]
    public function sensitive_permissions_are_admin_only_by_default(): void
    {
        $adminOnly = [
            'payroll.manage', 'performance.manage', 'teachers.manage', 'students.manage',
            'fees.manage', 'finance.manage', 'gateway.manage', 'settings.manage',
            'billing.manage', 'audit.view', 'data.import', 'data.export',
            'staff-attendance.manage', 'admission.manage', 'support.manage', 'users.manage',
        ];

        foreach (['teacher', 'guardian', 'student'] as $role) {
            foreach ($adminOnly as $key) {
                $this->assertFalse(Permissions::roleHas($role, $key), "{$role} ভুলবশত {$key} পাচ্ছে");
            }
        }

        foreach ($adminOnly as $key) {
            $this->assertTrue(Permissions::roleHas('admin', $key));
        }
    }

    #[Test]
    public function unknown_roles_and_unknown_permissions_are_denied(): void
    {
        $this->assertFalse(Permissions::roleHas('hacker', 'calendar.view'));
        $this->assertFalse(Permissions::roleHas(null, 'calendar.view'));
        $this->assertFalse(Permissions::roleHas('admin', 'not.a.permission'), 'admin-ও অচেনা চাবি পায় না (টাইপো ধরা পড়ার জন্য)');
    }

    #[Test]
    public function guardians_and_students_get_only_their_own_portal(): void
    {
        $this->assertTrue(Permissions::roleHas('guardian', 'portal.guardian'));
        $this->assertFalse(Permissions::roleHas('guardian', 'portal.student'));
        $this->assertFalse(Permissions::roleHas('guardian', 'portal.teacher'));

        $this->assertTrue(Permissions::roleHas('student', 'portal.student'));
        $this->assertFalse(Permissions::roleHas('student', 'portal.guardian'));

        $this->assertTrue(Permissions::roleHas('teacher', 'portal.teacher'));
        $this->assertFalse(Permissions::roleHas('teacher', 'portal.guardian'));
    }

    #[Test]
    public function user_has_permission_follows_the_role_defaults(): void
    {
        $admin = new User(['role' => 'admin']);
        $teacher = new User(['role' => 'teacher']);

        $this->assertTrue($admin->hasPermission('payroll.manage'));
        $this->assertFalse($teacher->hasPermission('payroll.manage'));
        $this->assertTrue($teacher->hasPermission('homework.manage'));
    }
}
