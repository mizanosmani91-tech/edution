<?php

namespace Tests\Feature;

use App\Livewire\TeacherEditForm;
use App\Models\Institution;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AccountDeactivationTest extends TestCase
{
    use RefreshDatabase;

    private Institution $inst;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inst = Institution::factory()->create(['slug' => 'greenvalley']);
    }

    private function loginUrl(): string
    {
        return 'http://greenvalley.edution.test/login';
    }

    #[Test]
    public function active_user_can_log_in(): void
    {
        User::factory()->create([
            'institution_id' => $this->inst->id, 'role' => 'admin',
            'email' => 'a@example.com', 'password' => 'secret-pass-1', 'must_change_password' => false,
        ]);

        $this->post($this->loginUrl(), ['email' => 'a@example.com', 'password' => 'secret-pass-1'])
            ->assertRedirect();
        $this->assertAuthenticated();
    }

    #[Test]
    public function inactive_user_cannot_log_in_even_with_the_right_password(): void
    {
        User::factory()->create([
            'institution_id' => $this->inst->id, 'role' => 'teacher',
            'email' => 't@example.com', 'password' => 'secret-pass-1', 'is_active' => false,
        ]);

        $this->post($this->loginUrl(), ['email' => 't@example.com', 'password' => 'secret-pass-1'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    #[Test]
    public function wrong_password_gives_the_generic_error_not_the_inactive_message(): void
    {
        User::factory()->create([
            'institution_id' => $this->inst->id, 'role' => 'teacher',
            'email' => 't@example.com', 'password' => 'secret-pass-1', 'is_active' => false,
        ]);

        $response = $this->post($this->loginUrl(), ['email' => 't@example.com', 'password' => 'wrong']);

        $response->assertSessionHasErrors(['email' => 'ইমেইল বা পাসওয়ার্ড সঠিক না।']);
    }

    #[Test]
    public function a_logged_in_user_is_signed_out_when_deactivated(): void
    {
        $user = User::factory()->create([
            'institution_id' => $this->inst->id, 'role' => 'admin', 'must_change_password' => false,
        ]);
        $this->actingAs($user);
        $this->get('/dashboard')->assertOk();

        $user->update(['is_active' => false]);

        $this->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();
    }

    #[Test]
    public function inactive_user_gets_403_on_json_requests(): void
    {
        $user = User::factory()->create([
            'institution_id' => $this->inst->id, 'role' => 'admin', 'is_active' => false, 'must_change_password' => false,
        ]);
        $this->actingAs($user);

        $this->getJson('/settings')->assertForbidden();
    }

    #[Test]
    public function deactivating_a_teacher_disables_their_login_and_reactivating_restores_it(): void
    {
        $admin = User::factory()->create(['institution_id' => $this->inst->id, 'role' => 'admin', 'must_change_password' => false]);
        $teacher = Teacher::factory()->create(['institution_id' => $this->inst->id, 'status' => 'active']);
        $teacherUser = User::factory()->create([
            'institution_id' => $this->inst->id, 'role' => 'teacher', 'teacher_id' => $teacher->id,
        ]);

        $this->actingAs($admin);
        \DB::statement('SELECT set_config(?, ?, false)', ['app.current_institution_id', $this->inst->id]);
        app()->instance('tenant.institution_id', $this->inst->id);

        Livewire::test(TeacherEditForm::class, ['teacher' => $teacher])
            ->set('status', 'inactive')
            ->call('save');
        $this->assertFalse($teacherUser->refresh()->is_active);

        Livewire::test(TeacherEditForm::class, ['teacher' => $teacher->refresh()])
            ->set('status', 'active')
            ->call('save');
        $this->assertTrue($teacherUser->refresh()->is_active);
    }

    #[Test]
    public function deactivating_one_institutions_teacher_never_touches_another_institutions_users(): void
    {
        $other = Institution::factory()->create();
        $sameTeacherId = Teacher::factory()->create(['institution_id' => $this->inst->id]);
        $foreignUser = User::factory()->create([
            'institution_id' => $other->id, 'role' => 'teacher', 'teacher_id' => $sameTeacherId->id,
        ]);
        $admin = User::factory()->create(['institution_id' => $this->inst->id, 'role' => 'admin']);

        $this->actingAs($admin);
        \DB::statement('SELECT set_config(?, ?, false)', ['app.current_institution_id', $this->inst->id]);
        app()->instance('tenant.institution_id', $this->inst->id);

        Livewire::test(TeacherEditForm::class, ['teacher' => $sameTeacherId])
            ->set('status', 'inactive')
            ->call('save');

        $this->assertTrue($foreignUser->refresh()->is_active);
    }
}
