<?php

namespace Tests\Feature;

use App\Livewire\SuperadminDashboard;
use App\Mail\AdminCredentialsMail;
use App\Mail\RegistrationRejectedMail;
use App\Models\Institution;
use App\Models\User;
use App\Services\SmsOtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): void
    {
        $this->actingAs(User::factory()->create(['institution_id' => null, 'role' => 'superadmin', 'must_change_password' => false]));
    }

    private function pending(array $attrs = []): Institution
    {
        // ⚠️ `$attrs + $defaults` (বাঁয়ের মান জেতে) — উল্টো লিখলে পাঠানো status কখনো বসত না,
        // আর "active প্রতিষ্ঠান" টেস্টগুলো আসলে pending নিয়েই চলত (CI-তে ধরা পড়েছিল)।
        return Institution::factory()->create($attrs + [
            'status' => 'pending',
            'registration_email' => 'owner@school.test',
            'phone' => '01711111111',
        ]);
    }

    private function smsExpecting(int $times): void
    {
        $this->mock(SmsOtpService::class, function (MockInterface $m) use ($times) {
            $m->shouldReceive('sendMessage')->times($times)->andReturn(true);
        });
    }

    #[Test]
    public function approving_a_pending_institution_creates_the_admin_activates_it_and_notifies(): void
    {
        Mail::fake();
        $this->smsExpecting(1);
        $this->superadmin();
        $inst = $this->pending();

        Livewire::test(SuperadminDashboard::class)->call('approvePendingInstitution', $inst->id);

        $this->assertSame('active', $inst->refresh()->status);

        $admin = User::where('institution_id', $inst->id)->where('role', 'admin')->firstOrFail();
        $this->assertSame('owner@school.test', $admin->email);
        $this->assertTrue($admin->must_change_password);

        Mail::assertSent(AdminCredentialsMail::class, 1);
    }

    #[Test]
    public function approving_twice_does_not_create_a_second_admin_or_send_a_second_credential(): void
    {
        Mail::fake();
        $this->smsExpecting(1);
        $this->superadmin();
        $inst = $this->pending();

        $component = Livewire::test(SuperadminDashboard::class);
        $component->call('approvePendingInstitution', $inst->id);
        $firstPassword = User::where('institution_id', $inst->id)->value('password');

        $component->call('approvePendingInstitution', $inst->id);

        $this->assertSame(1, User::where('institution_id', $inst->id)->count());
        $this->assertSame($firstPassword, User::where('institution_id', $inst->id)->value('password'), 'দ্বিতীয়বারে পাসওয়ার্ড বদলে গেছে।');
        Mail::assertSent(AdminCredentialsMail::class, 1);
    }

    #[Test]
    public function an_already_active_institution_is_not_re_approved(): void
    {
        Mail::fake();
        $this->smsExpecting(0);
        $this->superadmin();
        $inst = $this->pending(['status' => 'active']);

        Livewire::test(SuperadminDashboard::class)->call('approvePendingInstitution', $inst->id);

        $this->assertSame(0, User::where('institution_id', $inst->id)->count());
        Mail::assertNothingSent();
    }

    #[Test]
    public function a_rejected_application_can_still_be_approved_later(): void
    {
        Mail::fake();
        $this->smsExpecting(1);
        $this->superadmin();
        $inst = $this->pending(['status' => 'rejected']);

        Livewire::test(SuperadminDashboard::class)->call('approvePendingInstitution', $inst->id);

        $this->assertSame('active', $inst->refresh()->status);
        $this->assertSame(1, User::where('institution_id', $inst->id)->where('role', 'admin')->count());
    }

    #[Test]
    public function an_existing_admin_with_the_same_email_is_updated_instead_of_crashing(): void
    {
        Mail::fake();
        $this->smsExpecting(1);
        $this->superadmin();
        $inst = $this->pending();
        User::factory()->create([
            'institution_id' => $inst->id, 'email' => 'owner@school.test', 'role' => 'teacher',
            'password' => 'old-password-1', 'must_change_password' => false,
        ]);

        Livewire::test(SuperadminDashboard::class)->call('approvePendingInstitution', $inst->id);

        $users = User::where('institution_id', $inst->id)->get();
        $this->assertCount(1, $users);
        $this->assertSame('admin', $users->first()->role);
        $this->assertFalse(Hash::check('old-password-1', $users->first()->password));
        $this->assertTrue($users->first()->must_change_password);
    }

    #[Test]
    public function rejecting_a_pending_application_marks_it_and_tells_the_applicant(): void
    {
        Mail::fake();
        $this->smsExpecting(1);
        $this->superadmin();
        $inst = $this->pending();

        Livewire::test(SuperadminDashboard::class)->call('rejectPendingInstitution', $inst->id);

        $this->assertSame('rejected', $inst->refresh()->status);
        Mail::assertSent(RegistrationRejectedMail::class, fn ($mail) => $mail->hasTo('owner@school.test'));
    }

    #[Test]
    public function an_active_institution_cannot_be_rejected(): void
    {
        Mail::fake();
        $this->smsExpecting(0);
        $this->superadmin();
        $inst = $this->pending(['status' => 'active']);

        Livewire::test(SuperadminDashboard::class)->call('rejectPendingInstitution', $inst->id);

        $this->assertSame('active', $inst->refresh()->status);
        Mail::assertNothingSent();
    }

    #[Test]
    public function rejecting_twice_notifies_only_once(): void
    {
        Mail::fake();
        $this->smsExpecting(1);
        $this->superadmin();
        $inst = $this->pending();

        $component = Livewire::test(SuperadminDashboard::class);
        $component->call('rejectPendingInstitution', $inst->id);
        $component->call('rejectPendingInstitution', $inst->id);

        Mail::assertSent(RegistrationRejectedMail::class, 1);
    }
}
