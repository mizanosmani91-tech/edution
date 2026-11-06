<?php

namespace Tests\Feature;

use App\Livewire\ClassDiaryManager;
use App\Models\ClassDiaryEntry;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ClassDiaryTest extends TestCase
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

    private function actAs(string $role, Institution $institution, array $attrs = []): User
    {
        $user = User::factory()->create($attrs + ['institution_id' => $institution->id, 'role' => $role]);
        $this->actingAs($user);

        \DB::statement('SELECT set_config(?, ?, false)', ['app.current_institution_id', $institution->id]);
        app()->instance('tenant.institution_id', $institution->id);

        return $user;
    }

    #[Test]
    public function user_cannot_read_another_institutions_diary_entries(): void
    {
        ClassDiaryEntry::factory()->count(2)->create(['institution_id' => $this->a->id, 'class_id' => SchoolClass::factory()->create(['institution_id' => $this->a->id])->id]);
        ClassDiaryEntry::factory()->count(3)->create(['institution_id' => $this->b->id, 'class_id' => SchoolClass::factory()->create(['institution_id' => $this->b->id])->id]);

        $this->actAs('admin', $this->a);

        $this->assertCount(2, ClassDiaryEntry::all());
    }

    #[Test]
    public function admin_can_write_a_diary_entry(): void
    {
        $class = SchoolClass::factory()->create(['institution_id' => $this->a->id]);
        $this->actAs('admin', $this->a);

        Livewire::test(ClassDiaryManager::class)
            ->call('openModal')
            ->set('classId', $class->id)
            ->set('topic', 'ভগ্নাংশ')
            ->set('classwork', 'যোগ-বিয়োগ')
            ->call('save')
            ->assertHasNoErrors();

        $entry = ClassDiaryEntry::first();
        $this->assertSame('ভগ্নাংশ', $entry->topic);
        $this->assertSame($this->a->id, $entry->institution_id);
    }

    #[Test]
    public function class_from_another_institution_is_rejected(): void
    {
        $foreignClass = SchoolClass::factory()->create(['institution_id' => $this->b->id]);
        $this->actAs('admin', $this->a);

        Livewire::test(ClassDiaryManager::class)
            ->call('openModal')
            ->set('classId', $foreignClass->id)
            ->set('topic', 'অন্য প্রতিষ্ঠানের শ্রেণি')
            ->call('save')
            ->assertHasErrors(['classId']);

        $this->assertSame(0, ClassDiaryEntry::count());
    }

    #[Test]
    public function section_must_belong_to_the_chosen_class(): void
    {
        $classOne = SchoolClass::factory()->create(['institution_id' => $this->a->id]);
        $classTwo = SchoolClass::factory()->create(['institution_id' => $this->a->id]);
        $sectionOfTwo = Section::factory()->create(['institution_id' => $this->a->id, 'class_id' => $classTwo->id]);
        $this->actAs('admin', $this->a);

        Livewire::test(ClassDiaryManager::class)
            ->call('openModal')
            ->set('classId', $classOne->id)
            ->set('sectionId', $sectionOfTwo->id)
            ->set('topic', 'ভুল সেকশন')
            ->call('save')
            ->assertHasErrors(['sectionId']);
    }

    #[Test]
    public function teacher_entry_is_stamped_with_their_own_teacher_id(): void
    {
        $class = SchoolClass::factory()->create(['institution_id' => $this->a->id]);
        $me = Teacher::factory()->create(['institution_id' => $this->a->id]);
        $other = Teacher::factory()->create(['institution_id' => $this->a->id]);
        $this->actAs('teacher', $this->a, ['teacher_id' => $me->id]);

        Livewire::test(ClassDiaryManager::class)
            ->call('openModal')
            ->set('classId', $class->id)
            ->set('teacherId', $other->id) // অন্য শিক্ষকের নাম বসানোর চেষ্টা
            ->set('topic', 'আমার ক্লাস')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame($me->id, ClassDiaryEntry::first()->teacher_id);
    }

    #[Test]
    public function teacher_cannot_edit_or_delete_another_teachers_entry(): void
    {
        $class = SchoolClass::factory()->create(['institution_id' => $this->a->id]);
        $me = Teacher::factory()->create(['institution_id' => $this->a->id]);
        $other = Teacher::factory()->create(['institution_id' => $this->a->id]);
        $entry = ClassDiaryEntry::factory()->create([
            'institution_id' => $this->a->id,
            'class_id' => $class->id,
            'teacher_id' => $other->id,
        ]);

        $this->actAs('teacher', $this->a, ['teacher_id' => $me->id]);

        Livewire::test(ClassDiaryManager::class)->call('edit', $entry->id)->assertForbidden();
        Livewire::test(ClassDiaryManager::class)->call('delete', $entry->id)->assertForbidden();

        $this->assertSame(1, ClassDiaryEntry::count());
    }

    #[Test]
    public function guardians_and_students_cannot_use_the_diary_manager(): void
    {
        $this->actAs('guardian', $this->a);

        Livewire::test(ClassDiaryManager::class)->assertForbidden();
    }

    #[Test]
    public function for_student_scope_matches_class_and_section_or_whole_class(): void
    {
        $class = SchoolClass::factory()->create(['institution_id' => $this->a->id]);
        // একই শ্রেণিতে (class_id, name) ইউনিক — তাই নাম আলাদা দিতে হয়
        $sectionA = Section::factory()->create(['institution_id' => $this->a->id, 'class_id' => $class->id, 'name' => 'ক']);
        $sectionB = Section::factory()->create(['institution_id' => $this->a->id, 'class_id' => $class->id, 'name' => 'খ']);
        $otherClass = SchoolClass::factory()->create(['institution_id' => $this->a->id]);

        $student = Student::factory()->create([
            'institution_id' => $this->a->id,
            'class_id' => $class->id,
            'section_id' => $sectionA->id,
        ]);

        $this->actAs('admin', $this->a);

        $wholeClass = ClassDiaryEntry::factory()->create(['institution_id' => $this->a->id, 'class_id' => $class->id, 'section_id' => null]);
        $mySection = ClassDiaryEntry::factory()->create(['institution_id' => $this->a->id, 'class_id' => $class->id, 'section_id' => $sectionA->id]);
        ClassDiaryEntry::factory()->create(['institution_id' => $this->a->id, 'class_id' => $class->id, 'section_id' => $sectionB->id]);
        ClassDiaryEntry::factory()->create(['institution_id' => $this->a->id, 'class_id' => $otherClass->id]);

        $ids = ClassDiaryEntry::forStudent($student)->pluck('id')->all();

        $this->assertEqualsCanonicalizing([$wholeClass->id, $mySection->id], $ids);
    }
}
