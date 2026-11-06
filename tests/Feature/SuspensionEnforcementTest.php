<?php

namespace Tests\Feature;

use App\Http\Middleware\SetTenantContext;
use App\Models\Institution;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SuspensionEnforcementTest extends TestCase
{
    use RefreshDatabase;

    private function institution(array $attrs = []): Institution
    {
        return Institution::factory()->create($attrs);
    }

    private function login(Institution $inst, string $role): User
    {
        $user = User::factory()->create(['institution_id' => $inst->id, 'role' => $role, 'must_change_password' => false]);
        $this->actingAs($user);

        return $user;
    }

    private function enforce(bool $on): void
    {
        PlatformSetting::setBool('enforce_suspension', $on);
    }

    #[Test]
    public function with_the_switch_off_a_suspended_institution_works_as_before(): void
    {
        $this->enforce(false);
        $inst = $this->institution(['status' => 'suspended', 'billing_suspended' => true]);

        $this->login($inst, 'teacher');
        $this->get('/academic-calendar')->assertOk();
    }

    #[Test]
    public function the_switch_defaults_to_off(): void
    {
        $inst = $this->institution(['status' => 'suspended', 'billing_suspended' => true]);

        $this->login($inst, 'teacher');
        $this->get('/academic-calendar')->assertOk();
    }

    #[Test]
    public function with_the_switch_on_an_active_institution_is_unaffected(): void
    {
        $this->enforce(true);
        $inst = $this->institution(['status' => 'active', 'billing_suspended' => false]);

        $this->login($inst, 'teacher');
        $this->get('/academic-calendar')->assertOk();
    }

    #[Test]
    public function a_billing_suspended_institution_blocks_teachers_and_guardians(): void
    {
        $this->enforce(true);
        $inst = $this->institution(['status' => 'suspended', 'billing_suspended' => true]);

        foreach (['teacher', 'guardian', 'student'] as $role) {
            $this->login($inst, $role);
            $this->get('/academic-calendar')->assertForbidden()->assertSee('সেবা সাময়িকভাবে বন্ধ');
        }
    }

    #[Test]
    public function a_manually_suspended_institution_is_blocked_too(): void
    {
        $this->enforce(true);
        $inst = $this->institution(['status' => 'suspended', 'billing_suspended' => false]);

        $this->login($inst, 'teacher');
        $this->get('/academic-calendar')->assertForbidden();
    }

    #[Test]
    public function a_suspended_admin_can_only_reach_billing(): void
    {
        $this->enforce(true);
        $inst = $this->institution(['status' => 'suspended', 'billing_suspended' => true]);
        $this->login($inst, 'admin');

        $this->get('/dashboard')->assertRedirect(route('billing.index'));
        $this->get('/students-page')->assertRedirect(route('billing.index'));

        $response = $this->get('/billing');
        $this->assertNotSame(403, $response->getStatusCode());
        $this->assertFalse($response->isRedirect(), 'বিলিং পেজ নিজে আবার রিডাইরেক্ট করছে — লুপ।');
    }

    #[Test]
    public function json_requests_from_a_suspended_institution_get_403(): void
    {
        $this->enforce(true);
        $inst = $this->institution(['status' => 'suspended', 'billing_suspended' => true]);
        $this->login($inst, 'teacher');

        $this->getJson('/settings')->assertForbidden();
    }

    #[Test]
    public function paying_the_bill_restores_access(): void
    {
        $this->enforce(true);
        $inst = $this->institution(['status' => 'suspended', 'billing_suspended' => true]);
        $this->login($inst, 'teacher');
        $this->get('/academic-calendar')->assertForbidden();

        // BillingService::topUp / পেমেন্ট অনুমোদনের পর যা হয়
        $inst->update(['status' => 'active', 'billing_suspended' => false]);

        $this->get('/academic-calendar')->assertOk();
    }

    #[Test]
    public function another_institutions_suspension_does_not_affect_this_one(): void
    {
        $this->enforce(true);
        $this->institution(['status' => 'suspended', 'billing_suspended' => true]);
        $healthy = $this->institution(['status' => 'active', 'billing_suspended' => false]);

        $this->login($healthy, 'teacher');
        $this->get('/academic-calendar')->assertOk();
    }

    #[Test]
    public function livewire_components_allowlist_only_lets_billing_related_ones_through(): void
    {
        $snapshot = fn (string $name) => ['snapshot' => json_encode(['memo' => ['name' => $name]])];

        $this->assertTrue(SetTenantContext::componentsAllowed([$snapshot('billing-center')]));
        $this->assertTrue(SetTenantContext::componentsAllowed([$snapshot('billing-center'), $snapshot('notification-bell')]));
        $this->assertFalse(SetTenantContext::componentsAllowed([$snapshot('student-list')]));
        $this->assertFalse(SetTenantContext::componentsAllowed([$snapshot('billing-center'), $snapshot('payroll-manager')]));
    }

    #[Test]
    public function an_unreadable_livewire_payload_does_not_lock_out_the_billing_page(): void
    {
        // বিন্যাস অচেনা হলে ফেল-ওপেন: স্থগিত অ্যাডমিন যেন বিল দেওয়ার পথ না হারায়
        $this->assertTrue(SetTenantContext::componentsAllowed([['snapshot' => 'not-json']]));
        $this->assertTrue(SetTenantContext::componentsAllowed([['something' => 'else']]));
        $this->assertTrue(SetTenantContext::componentsAllowed([]));
    }
}
