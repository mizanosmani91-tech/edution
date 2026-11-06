<?php

namespace Tests\Feature;

use App\Livewire\StudentEditForm;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StudentEditTest extends TestCase
{
    use RefreshDatabase;

    private Institution $a;
    private Institution $b;

    protected function setUp(): void
    {
        parent::setUp();

        $this->a = Institution::factory()->create();
        $this->b = Institution::factory()->create();
    }

    private function actAs(string $role, Institution $institution): User
    {
        $user = User::factory()->create(['institution_id' => $institution->id, 'role' => $role, 'must_change_password' => false]);
        $this->actingAs($user);

        \DB::statement('SELECT set_config(?, ?, false)', ['app.current_institution_id', $institution->id]);
        app()->instance('tenant.institution_id', $institution->id);

        return $user;
    }

    private function student(Institution $inst, array $attrs = []): Student
    {
        return Student::factory()->create($attrs + ['institution_id' => $inst->id]);
    }

    #[Test]
    public function admin_can_edit_a_student_and_the_id_number_stays_the_same(): void
    {
        $class = SchoolClass::factory()->create(['institution_id' => $this->a->id]);
        $student = $this->student($this->a, ['student_id_no' => 'STU-12345']);
        $this->actAs('admin', $this->a);

        Livewire::test(StudentEditForm::class, ['student' => $student])
            ->set('name', 'নতুন নাম')
            ->set('class_id', $class->id)
            ->set('guardian_phone', '01711111111')
            ->call('save')
            ->assertHasNoErrors();

        $student->refresh();
        $this->assertSame('নতুন নাম', $student->name);
        $this->assertSame($class->id, $student->class_id);
        $this->assertSame('01711111111', $student->guardian_phone);
        $this->assertSame('STU-12345', $student->student_id_no);
    }

    #[Test]
    public function status_can_be_changed_without_deleting_the_record(): void
    {
        $class = SchoolClass::factory()->create(['institution_id' => $this->a->id]);
        $student = $this->student($this->a, ['class_id' => $class->id]);
        $this->actAs('admin', $this->a);

        Livewire::test(StudentEditForm::class, ['student' => $student])
            ->set('status', 'transferred')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('transferred', $student->refresh()->status);
        $this->assertSame(1, Student::count());
    }

    #[Test]
    public function unknown_status_is_rejected(): void
    {
        $class = SchoolClass::factory()->create(['institution_id' => $this->a->id]);
        $student = $this->student($this->a, ['class_id' => $class->id]);
        $this->actAs('admin', $this->a);

        Livewire::test(StudentEditForm::class, ['student' => $student])
            ->set('status', 'deleted')
            ->call('save')
            ->assertHasErrors(['status']);
    }

    #[Test]
    public function non_admins_cannot_open_or_save_the_edit_form(): void
    {
        $student = $this->student($this->a);

        foreach (['teacher', 'guardian', 'student'] as $role) {
            $this->actAs($role, $this->a);
            Livewire::test(StudentEditForm::class, ['student' => $student])->assertForbidden();
        }
    }

    #[Test]
    public function class_from_another_institution_is_rejected(): void
    {
        $foreignClass = SchoolClass::factory()->create(['institution_id' => $this->b->id]);
        $class = SchoolClass::factory()->create(['institution_id' => $this->a->id]);
        $student = $this->student($this->a, ['class_id' => $class->id]);
        $this->actAs('admin', $this->a);

        Livewire::test(StudentEditForm::class, ['student' => $student])
            ->set('class_id', $foreignClass->id)
            ->call('save')
            ->assertHasErrors(['class_id']);

        $this->assertSame($class->id, $student->refresh()->class_id);
    }

    #[Test]
    public function section_must_belong_to_the_chosen_class(): void
    {
        $classOne = SchoolClass::factory()->create(['institution_id' => $this->a->id]);
        $classTwo = SchoolClass::factory()->create(['institution_id' => $this->a->id]);
        $sectionOfTwo = Section::factory()->create(['institution_id' => $this->a->id, 'class_id' => $classTwo->id]);
        $student = $this->student($this->a, ['class_id' => $classOne->id]);
        $this->actAs('admin', $this->a);

        $component = Livewire::test(StudentEditForm::class, ['student' => $student])
            ->set('class_id', $classOne->id)
            ->set('section_id', $sectionOfTwo->id)
            ->call('save');

        $component->assertHasErrors(['section_id']);
    }

    #[Test]
    public function changing_the_class_clears_the_old_section(): void
    {
        $classOne = SchoolClass::factory()->create(['institution_id' => $this->a->id]);
        $classTwo = SchoolClass::factory()->create(['institution_id' => $this->a->id]);
        $sectionOne = Section::factory()->create(['institution_id' => $this->a->id, 'class_id' => $classOne->id]);
        $student = $this->student($this->a, ['class_id' => $classOne->id, 'section_id' => $sectionOne->id]);
        $this->actAs('admin', $this->a);

        Livewire::test(StudentEditForm::class, ['student' => $student])
            ->set('class_id', $classTwo->id)
            ->assertSet('section_id', null);
    }

    #[Test]
    public function another_institutions_student_is_not_reachable_by_url(): void
    {
        $foreign = $this->student($this->b);
        $this->actAs('admin', $this->a);

        $this->get("/students-page/{$foreign->id}/edit")->assertNotFound();
    }
}
