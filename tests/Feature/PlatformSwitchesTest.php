<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Services\InstitutionApprovalService;
use App\Services\NotificationService;
use App\Services\SmsOtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PlatformSwitchesTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_approval_service_approves_a_pending_institution_once(): void
    {
        Mail::fake();
        $this->mock(SmsOtpService::class)->shouldReceive('sendMessage')->andReturn(true);
        $inst = Institution::factory()->create(['status' => 'pending', 'registration_email' => 'o@s.test', 'phone' => '01711111111']);

        $first = app(InstitutionApprovalService::class)->approve($inst->id);
        $second = app(InstitutionApprovalService::class)->approve($inst->id);

        $this->assertNotNull($first);
        $this->assertNull($second);
        $this->assertSame('active', $inst->refresh()->status);
        $this->assertSame(1, User::where('institution_id', $inst->id)->where('role', 'admin')->count());
    }

    #[Test]
    public function maintenance_mode_blocks_tenant_users_but_not_when_off(): void
    {
        $inst = Institution::factory()->create(['status' => 'active']);
        $user = User::factory()->create(['institution_id' => $inst->id, 'role' => 'teacher', 'must_change_password' => false]);
        $this->actingAs($user);

        $this->get('/academic-calendar')->assertOk();

        PlatformSetting::setBool('maintenance_mode', true);
        $this->get('/academic-calendar')->assertStatus(503);
    }

    #[Test]
    public function maintenance_mode_does_not_block_the_superadmin(): void
    {
        PlatformSetting::setBool('maintenance_mode', true);
        $this->actingAs(User::factory()->create(['institution_id' => null, 'role' => 'superadmin', 'must_change_password' => false]));

        $this->assertNotSame(503, $this->get('/academic-calendar')->getStatusCode());
    }

    #[Test]
    public function billing_alert_sms_follows_its_switch(): void
    {
        Mail::fake();
        $inst = Institution::factory()->create(['status' => 'active', 'phone' => '01711111111']);

        $this->mock(SmsOtpService::class)->shouldReceive('sendMessage')->once()->andReturn(true);
        app(NotificationService::class)->billingAlert($inst, 'x', 'শিরোনাম', 'বিবরণ');

        PlatformSetting::setBool('billing_alert_sms', false);
        // সুইচ বন্ধ: দ্বিতীয়বার আর SMS যাবে না (once() তাই পুরো টেস্টে এক বারই)
        app(NotificationService::class)->billingAlert($inst, 'y', 'শিরোনাম', 'বিবরণ');
    }

    #[Test]
    public function an_expired_trial_is_suspended_only_when_the_switch_is_on(): void
    {
        Mail::fake();
        $this->mock(SmsOtpService::class)->shouldReceive('sendMessage')->andReturn(true);
        $expired = Institution::factory()->create(['status' => 'trial', 'trial_ends_at' => now()->subDay()]);
        $running = Institution::factory()->create(['status' => 'trial', 'trial_ends_at' => now()->addDays(5)]);

        PlatformSetting::setBool('auto_suspend_trial_end', false);
        $this->artisan('edution:process-billing')->assertSuccessful();
        $this->assertSame('trial', $expired->refresh()->status);

        PlatformSetting::setBool('auto_suspend_trial_end', true);
        $this->artisan('edution:process-billing')->assertSuccessful();
        $this->assertSame('suspended', $expired->refresh()->status);
        $this->assertSame('trial', $running->refresh()->status);
    }
}
