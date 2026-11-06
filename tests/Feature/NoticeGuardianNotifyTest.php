<?php

namespace Tests\Feature;

use App\Livewire\NoticeBoard;
use App\Models\AppNotification;
use App\Models\Institution;
use App\Models\User;
use App\Services\TenantSmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NoticeGuardianNotifyTest extends TestCase
{
    use RefreshDatabase;

    private function setup_school(): array
    {
        $inst = Institution::factory()->create(['status' => 'active']);
        $admin = User::factory()->create(['institution_id' => $inst->id, 'role' => 'admin', 'must_change_password' => false]);
        $g1 = User::factory()->create(['institution_id' => $inst->id, 'role' => 'guardian', 'phone' => '01711111111']);
        $g2 = User::factory()->create(['institution_id' => $inst->id, 'role' => 'guardian', 'phone' => null]);
        $other = Institution::factory()->create(['status' => 'active']);
        $outsider = User::factory()->create(['institution_id' => $other->id, 'role' => 'guardian', 'phone' => '01722222222']);
        $this->actingAs($admin);

        return [$inst, $g1, $g2, $outsider];
    }

    #[Test]
    public function notice_with_the_option_notifies_only_this_schools_guardians(): void
    {
        [$inst, $g1, $g2, $outsider] = $this->setup_school();
        $this->mock(TenantSmsService::class)->shouldReceive('send')->once()->andReturn(true);

        Livewire::test(NoticeBoard::class)
            ->set('title', 'ছুটির নোটিশ')->set('body', 'আগামীকাল ছুটি')->set('notifyGuardians', true)
            ->call('submit');

        $this->assertSame(1, AppNotification::where('user_id', $g1->id)->where('type', 'notice')->count());
        $this->assertSame(1, AppNotification::where('user_id', $g2->id)->where('type', 'notice')->count());
        $this->assertSame(0, AppNotification::where('user_id', $outsider->id)->count());
    }

    #[Test]
    public function without_the_option_nobody_is_notified(): void
    {
        [$inst, $g1] = $this->setup_school();
        $this->mock(TenantSmsService::class)->shouldReceive('send')->never();

        Livewire::test(NoticeBoard::class)->set('title', 'নোটিশ')->set('body', 'বিবরণ')->call('submit');

        $this->assertSame(0, AppNotification::where('type', 'notice')->count());
    }

    #[Test]
    public function a_teachers_only_notice_does_not_go_to_guardians(): void
    {
        [$inst, $g1] = $this->setup_school();
        $this->mock(TenantSmsService::class)->shouldReceive('send')->never();

        Livewire::test(NoticeBoard::class)
            ->set('title', 'শিক্ষক সভা')->set('body', 'বিবরণ')->set('audience', ['teacher'])->set('notifyGuardians', true)
            ->call('submit');

        $this->assertSame(0, AppNotification::where('type', 'notice')->count());
    }
}
