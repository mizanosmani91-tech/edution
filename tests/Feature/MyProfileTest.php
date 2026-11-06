<?php

namespace Tests\Feature;

use App\Livewire\MyProfile;
use App\Models\Institution;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MyProfileTest extends TestCase
{
    use RefreshDatabase;

    private Institution $inst;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inst = Institution::factory()->create();
    }

    private function actAs(string $role = 'admin', array $attrs = []): User
    {
        $user = User::factory()->create([
            'institution_id' => $this->inst->id,
            'role' => $role,
            'password' => 'old-password-1',
            'must_change_password' => false,
        ] + $attrs);
        $this->actingAs($user);

        \DB::statement('SELECT set_config(?, ?, false)', ['app.current_institution_id', $this->inst->id]);
        app()->instance('tenant.institution_id', $this->inst->id);
        RateLimiter::clear('my-profile:password:' . $user->id);

        return $user;
    }

    #[Test]
    public function every_role_can_open_the_page(): void
    {
        foreach (['admin', 'teacher', 'guardian', 'student'] as $role) {
            $this->actAs($role);
            $this->get('/my-profile')->assertOk();
        }
    }

    #[Test]
    public function name_and_phone_can_be_changed_without_a_password(): void
    {
        $user = $this->actAs('guardian');

        Livewire::test(MyProfile::class)
            ->set('name', 'নতুন নাম')
            ->set('phone', '01811111111')
            ->call('saveProfile')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertSame('নতুন নাম', $user->name);
        $this->assertSame('01811111111', $user->phone);
    }

    #[Test]
    public function changing_email_requires_the_current_password(): void
    {
        $user = $this->actAs('admin');
        $old = $user->email;

        Livewire::test(MyProfile::class)
            ->set('email', 'new@example.com')
            ->call('saveProfile')
            ->assertHasErrors(['email_password']);

        $this->assertSame($old, $user->refresh()->email);
    }

    #[Test]
    public function wrong_password_blocks_the_email_change_and_correct_one_allows_it(): void
    {
        $user = $this->actAs('admin');

        Livewire::test(MyProfile::class)
            ->set('email', 'new@example.com')
            ->set('email_password', 'wrong')
            ->call('saveProfile')
            ->assertHasErrors(['email_password']);

        $this->assertNotSame('new@example.com', $user->refresh()->email);

        Livewire::test(MyProfile::class)
            ->set('email', 'new@example.com')
            ->set('email_password', 'old-password-1')
            ->call('saveProfile')
            ->assertHasNoErrors();

        $this->assertSame('new@example.com', $user->refresh()->email);
    }

    #[Test]
    public function email_already_used_in_the_same_institution_is_rejected(): void
    {
        User::factory()->create(['institution_id' => $this->inst->id, 'email' => 'taken@example.com']);
        $this->actAs('admin');

        Livewire::test(MyProfile::class)
            ->set('email', 'taken@example.com')
            ->set('email_password', 'old-password-1')
            ->call('saveProfile')
            ->assertHasErrors(['email']);
    }

    #[Test]
    public function the_same_email_in_another_institution_is_allowed(): void
    {
        $other = Institution::factory()->create();
        User::factory()->create(['institution_id' => $other->id, 'email' => 'shared@example.com']);
        $user = $this->actAs('admin');

        Livewire::test(MyProfile::class)
            ->set('email', 'shared@example.com')
            ->set('email_password', 'old-password-1')
            ->call('saveProfile')
            ->assertHasNoErrors();

        $this->assertSame('shared@example.com', $user->refresh()->email);
    }

    #[Test]
    public function password_change_needs_the_right_current_password(): void
    {
        $user = $this->actAs('admin');

        Livewire::test(MyProfile::class)
            ->set('current_password', 'wrong')
            ->set('new_password', 'brand-new-pass-9')
            ->set('new_password_confirmation', 'brand-new-pass-9')
            ->call('changePassword')
            ->assertHasErrors(['current_password']);

        $this->assertTrue(Hash::check('old-password-1', $user->refresh()->password));
    }

    #[Test]
    public function password_is_changed_and_the_force_change_flag_is_cleared(): void
    {
        $user = $this->actAs('admin', ['must_change_password' => true]);

        Livewire::test(MyProfile::class)
            ->set('current_password', 'old-password-1')
            ->set('new_password', 'brand-new-pass-9')
            ->set('new_password_confirmation', 'brand-new-pass-9')
            ->call('changePassword')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertTrue(Hash::check('brand-new-pass-9', $user->password));
        $this->assertFalse($user->must_change_password);
    }

    #[Test]
    public function weak_mismatched_or_unchanged_passwords_are_rejected(): void
    {
        $user = $this->actAs('admin');

        Livewire::test(MyProfile::class)
            ->set('current_password', 'old-password-1')
            ->set('new_password', 'short')
            ->set('new_password_confirmation', 'short')
            ->call('changePassword')
            ->assertHasErrors(['new_password']);

        Livewire::test(MyProfile::class)
            ->set('current_password', 'old-password-1')
            ->set('new_password', 'brand-new-pass-9')
            ->set('new_password_confirmation', 'different-pass-9')
            ->call('changePassword')
            ->assertHasErrors(['new_password']);

        Livewire::test(MyProfile::class)
            ->set('current_password', 'old-password-1')
            ->set('new_password', 'old-password-1')
            ->set('new_password_confirmation', 'old-password-1')
            ->call('changePassword')
            ->assertHasErrors(['new_password']);

        $this->assertTrue(Hash::check('old-password-1', $user->refresh()->password));
    }

    #[Test]
    public function repeated_wrong_passwords_get_locked_out_for_a_while(): void
    {
        $user = $this->actAs('admin');

        for ($i = 0; $i < 5; $i++) {
            Livewire::test(MyProfile::class)
                ->set('current_password', 'wrong')
                ->set('new_password', 'brand-new-pass-9')
                ->set('new_password_confirmation', 'brand-new-pass-9')
                ->call('changePassword');
        }

        // ষষ্ঠবার সঠিক পাসওয়ার্ড দিলেও আটকাবে
        Livewire::test(MyProfile::class)
            ->set('current_password', 'old-password-1')
            ->set('new_password', 'brand-new-pass-9')
            ->set('new_password_confirmation', 'brand-new-pass-9')
            ->call('changePassword')
            ->assertHasErrors(['current_password']);

        $this->assertTrue(Hash::check('old-password-1', $user->refresh()->password));
    }

    #[Test]
    public function a_teacher_editing_their_profile_updates_their_teacher_record_too(): void
    {
        $teacher = Teacher::factory()->create(['institution_id' => $this->inst->id]);
        $this->actAs('teacher', ['teacher_id' => $teacher->id]);

        Livewire::test(MyProfile::class)
            ->set('name', 'শিক্ষকের নতুন নাম')
            ->set('phone', '01922222222')
            ->call('saveProfile')
            ->assertHasNoErrors();

        $teacher->refresh();
        $this->assertSame('শিক্ষকের নতুন নাম', $teacher->name);
        $this->assertSame('01922222222', $teacher->phone);
    }
}
