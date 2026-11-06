<?php

namespace Tests\Feature;

use App\Livewire\UserManager;
use App\Models\Institution;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserManagerTest extends TestCase
{
    use RefreshDatabase;

    private Institution $a;
    private Institution $b;

    protected function setUp(): void
    {
        parent::setUp();

        $this->a = Institution::factory()->create(['slug' => 'alpha']);
        $this->b = Institution::factory()->create(['slug' => 'beta']);
    }

    private function actAs(string $role, Institution $inst, array $attrs = []): User
    {
        $user = User::factory()->create($attrs + ['institution_id' => $inst->id, 'role' => $role, 'must_change_password' => false]);
        $this->actingAs($user);

        \DB::statement('SELECT set_config(?, ?, false)', ['app.current_institution_id', $inst->id]);
        app()->instance('tenant.institution_id', $inst->id);

        return $user;
    }

    private function make(Institution $inst, string $role, array $attrs = []): User
    {
        return User::factory()->create($attrs + ['institution_id' => $inst->id, 'role' => $role]);
    }

    #[Test]
    public function non_admins_cannot_open_the_page(): void
    {
        foreach (['teacher', 'guardian', 'student'] as $role) {
            $this->actAs($role, $this->a);
            Livewire::test(UserManager::class)->assertForbidden();
        }
    }

    #[Test]
    public function the_list_shows_only_this_institutions_users(): void
    {
        $this->make($this->a, 'teacher', ['name' => 'আমাদের শিক্ষক']);
        $this->make($this->b, 'teacher', ['name' => 'অন্য স্কুলের শিক্ষক']);
        $this->actAs('admin', $this->a);

        Livewire::test(UserManager::class)
            ->assertSee('আমাদের শিক্ষক')
            ->assertDontSee('অন্য স্কুলের শিক্ষক');
    }

    #[Test]
    public function admin_can_create_another_admin_with_a_temporary_password(): void
    {
        $this->actAs('admin', $this->a);

        $component = Livewire::test(UserManager::class)
            ->set('adminName', 'নতুন অ্যাডমিন')
            ->set('adminEmail', 'new-admin@example.com')
            ->call('createAdmin')
            ->assertHasNoErrors();

        $user = User::where('email', 'new-admin@example.com')->firstOrFail();
        $this->assertSame('admin', $user->role);
        $this->assertSame($this->a->id, $user->institution_id);
        $this->assertTrue($user->must_change_password);
        $this->assertTrue($user->is_active);

        $revealed = $component->get('revealed');
        $this->assertTrue(Hash::check($revealed['password'], $user->password));
    }

    #[Test]
    public function duplicate_email_in_the_same_institution_is_rejected(): void
    {
        $this->make($this->a, 'teacher', ['email' => 'dup@example.com']);
        $this->actAs('admin', $this->a);

        Livewire::test(UserManager::class)
            ->set('adminName', 'কেউ')
            ->set('adminEmail', 'dup@example.com')
            ->call('createAdmin')
            ->assertHasErrors(['adminEmail']);
    }

    #[Test]
    public function the_same_email_in_another_institution_is_allowed(): void
    {
        $this->make($this->b, 'teacher', ['email' => 'shared@example.com']);
        $this->actAs('admin', $this->a);

        Livewire::test(UserManager::class)
            ->set('adminName', 'কেউ')
            ->set('adminEmail', 'shared@example.com')
            ->call('createAdmin')
            ->assertHasNoErrors();
    }

    #[Test]
    public function an_admin_can_deactivate_and_reactivate_someone(): void
    {
        $other = $this->make($this->a, 'guardian');
        $this->actAs('admin', $this->a);

        Livewire::test(UserManager::class)->call('toggleActive', $other->id);
        $this->assertFalse($other->refresh()->is_active);

        Livewire::test(UserManager::class)->call('toggleActive', $other->id);
        $this->assertTrue($other->refresh()->is_active);
    }

    #[Test]
    public function an_admin_cannot_deactivate_themselves(): void
    {
        $me = $this->actAs('admin', $this->a);
        $this->make($this->a, 'admin'); // আরেকজন আছে, তবু নিজেকে নয়

        Livewire::test(UserManager::class)->call('toggleActive', $me->id);

        $this->assertTrue($me->refresh()->is_active);
    }

    #[Test]
    public function one_admin_can_deactivate_another_while_the_acting_admin_stays_active(): void
    {
        // অ্যাকশন চালানো অ্যাডমিন নিজেও "সক্রিয় অ্যাডমিন" গোনায়, তাই অন্য একজনকে বন্ধ করলেও
        // প্রতিষ্ঠানে অন্তত একজন (নিজে) থেকে যায়। "শেষ সক্রিয় অ্যাডমিন" গার্ডটা এর পরেও
        // প্রতিরক্ষা হিসেবে আছে (প্রবেশযোগ্য নয়: বন্ধ ইউজার অ্যাকশনই চালাতে পারে না)।
        $me = $this->actAs('admin', $this->a);
        $other = $this->make($this->a, 'admin');

        Livewire::test(UserManager::class)->call('toggleActive', $other->id);

        $this->assertFalse($other->refresh()->is_active);
        $this->assertTrue($me->refresh()->is_active);
        $this->assertSame(1, User::where('institution_id', $this->a->id)->where('role', 'admin')->where('is_active', true)->count());
    }

    #[Test]
    public function deactivating_a_teacher_user_also_marks_the_teacher_record_inactive(): void
    {
        $teacher = Teacher::factory()->create(['institution_id' => $this->a->id, 'status' => 'active']);
        $teacherUser = $this->make($this->a, 'teacher', ['teacher_id' => $teacher->id]);
        $this->actAs('admin', $this->a);

        Livewire::test(UserManager::class)->call('toggleActive', $teacherUser->id);

        $this->assertFalse($teacherUser->refresh()->is_active);
        $this->assertSame('inactive', $teacher->refresh()->status);
    }

    #[Test]
    public function admin_cannot_touch_another_institutions_user(): void
    {
        $foreign = $this->make($this->b, 'teacher');
        $this->actAs('admin', $this->a);

        $this->expectException(ModelNotFoundException::class);
        Livewire::test(UserManager::class)->call('toggleActive', $foreign->id);
    }

    #[Test]
    public function admin_cannot_reset_another_institutions_users_password(): void
    {
        $foreign = $this->make($this->b, 'teacher', ['password' => 'untouched-pass-1']);
        $this->actAs('admin', $this->a);

        try {
            Livewire::test(UserManager::class)->call('resetPassword', $foreign->id);
            $this->fail('অন্য প্রতিষ্ঠানের ইউজারের পাসওয়ার্ড বদলে গেছে।');
        } catch (ModelNotFoundException) {
            $this->assertTrue(Hash::check('untouched-pass-1', $foreign->refresh()->password));
        }
    }

    #[Test]
    public function password_reset_sets_a_new_temporary_password_and_forces_a_change(): void
    {
        $guardian = $this->make($this->a, 'guardian', ['password' => 'old-pass-12345', 'must_change_password' => false]);
        $this->actAs('admin', $this->a);

        $component = Livewire::test(UserManager::class)->call('resetPassword', $guardian->id);

        $guardian->refresh();
        $this->assertFalse(Hash::check('old-pass-12345', $guardian->password));
        $this->assertTrue($guardian->must_change_password);
        $this->assertTrue(Hash::check($component->get('revealed')['password'], $guardian->password));
    }

    #[Test]
    public function the_revealed_password_is_cleared_when_closed(): void
    {
        $guardian = $this->make($this->a, 'guardian');
        $this->actAs('admin', $this->a);

        Livewire::test(UserManager::class)
            ->call('resetPassword', $guardian->id)
            ->assertNotSet('revealed', null)
            ->call('closeReveal')
            ->assertSet('revealed', null);
    }

    #[Test]
    public function an_admin_cannot_reset_their_own_password_here(): void
    {
        $me = $this->actAs('admin', $this->a, ['password' => 'my-pass-12345']);

        Livewire::test(UserManager::class)->call('resetPassword', $me->id)->assertSet('revealed', null);

        $this->assertTrue(Hash::check('my-pass-12345', $me->refresh()->password));
    }

    #[Test]
    public function a_guardians_phone_is_taken_from_their_generated_email_for_sms(): void
    {
        $guardian = $this->make($this->a, 'guardian', ['email' => 'guardian-01712345678@alpha.local', 'phone' => null]);
        $this->actAs('admin', $this->a);

        $component = Livewire::test(UserManager::class)->call('resetPassword', $guardian->id);

        $this->assertSame('01712345678', $component->get('revealed')['phone']);
    }

    #[Test]
    public function admin_can_create_a_student_login_linked_to_the_student(): void
    {
        $student = Student::factory()->create(['institution_id' => $this->a->id, 'name' => 'রহিম', 'student_id_no' => 'STU-12345']);
        $this->actAs('admin', $this->a);

        $component = Livewire::test(UserManager::class)
            ->set('studentId', $student->id)
            ->call('createStudentLogin')
            ->assertHasNoErrors();

        $user = User::where('student_id', $student->id)->firstOrFail();
        $this->assertSame('student', $user->role);
        $this->assertSame('student-stu12345@alpha.local', $user->email);
        $this->assertSame($this->a->id, $user->institution_id);
        $this->assertTrue($user->must_change_password);
        $this->assertTrue(Hash::check($component->get('revealed')['password'], $user->password));
    }

    #[Test]
    public function a_second_login_for_the_same_student_is_rejected(): void
    {
        $student = Student::factory()->create(['institution_id' => $this->a->id]);
        $this->make($this->a, 'student', ['student_id' => $student->id]);
        $this->actAs('admin', $this->a);

        Livewire::test(UserManager::class)
            ->set('studentId', $student->id)
            ->call('createStudentLogin')
            ->assertHasErrors(['studentId']);

        $this->assertSame(1, User::where('student_id', $student->id)->count());
    }

    #[Test]
    public function a_student_from_another_institution_cannot_get_a_login(): void
    {
        $foreign = Student::factory()->create(['institution_id' => $this->b->id]);
        $this->actAs('admin', $this->a);

        $this->expectException(ModelNotFoundException::class);
        Livewire::test(UserManager::class)->set('studentId', $foreign->id)->call('createStudentLogin');
    }
}
