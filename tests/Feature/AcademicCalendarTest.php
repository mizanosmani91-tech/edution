<?php

namespace Tests\Feature;

use App\Livewire\AcademicCalendar;
use App\Models\CalendarEvent;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AcademicCalendarTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institutionA;
    private Institution $institutionB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institutionA = Institution::factory()->create();
        $this->institutionB = Institution::factory()->create();
    }

    private function actAs(string $role, Institution $institution): User
    {
        $user = User::factory()->create(['institution_id' => $institution->id, 'role' => $role]);
        $this->actingAs($user);

        \DB::statement('SELECT set_config(?, ?, false)', ['app.current_institution_id', $institution->id]);
        app()->instance('tenant.institution_id', $institution->id);

        return $user;
    }

    #[Test]
    public function user_cannot_read_another_institutions_calendar_events(): void
    {
        CalendarEvent::factory()->count(2)->create(['institution_id' => $this->institutionA->id]);
        CalendarEvent::factory()->count(3)->create(['institution_id' => $this->institutionB->id]);

        $this->actAs('admin', $this->institutionA);

        $this->assertCount(2, CalendarEvent::all());
    }

    #[Test]
    public function event_is_stamped_with_the_current_institution(): void
    {
        $this->actAs('admin', $this->institutionA);

        $event = CalendarEvent::create([
            'title' => 'বার্ষিক ক্রীড়া',
            'type' => 'event',
            'start_date' => '2026-12-10',
            'end_date' => '2026-12-10',
        ]);

        $this->assertSame($this->institutionA->id, $event->institution_id);
    }

    #[Test]
    public function is_off_day_covers_the_whole_date_range_only_for_off_day_events(): void
    {
        $this->actAs('admin', $this->institutionA);

        CalendarEvent::factory()->create([
            'institution_id' => $this->institutionA->id,
            'type' => 'holiday',
            'is_off_day' => true,
            'start_date' => '2026-12-14',
            'end_date' => '2026-12-16',
        ]);
        CalendarEvent::factory()->create([
            'institution_id' => $this->institutionA->id,
            'is_off_day' => false,
            'start_date' => '2026-12-20',
            'end_date' => '2026-12-20',
        ]);
        // অন্য প্রতিষ্ঠানের ছুটি এই প্রতিষ্ঠানকে প্রভাবিত করবে না
        CalendarEvent::factory()->create([
            'institution_id' => $this->institutionB->id,
            'is_off_day' => true,
            'start_date' => '2026-12-22',
            'end_date' => '2026-12-22',
        ]);

        $this->assertTrue(CalendarEvent::isOffDay('2026-12-14'));
        $this->assertTrue(CalendarEvent::isOffDay('2026-12-16'));
        $this->assertFalse(CalendarEvent::isOffDay('2026-12-17'));
        $this->assertFalse(CalendarEvent::isOffDay('2026-12-20'));
        $this->assertFalse(CalendarEvent::isOffDay('2026-12-22'));
    }

    #[Test]
    public function admin_can_create_an_event(): void
    {
        $this->actAs('admin', $this->institutionA);

        Livewire::test(AcademicCalendar::class)
            ->call('openModal', '2026-12-16')
            ->set('title', 'বিজয় দিবস')
            ->set('type', 'holiday')
            ->set('endDate', '2026-12-16')
            ->call('save')
            ->assertHasNoErrors();

        $event = CalendarEvent::first();
        $this->assertSame('বিজয় দিবস', $event->title);
        $this->assertTrue($event->is_off_day, 'ছুটির ধরন বাছলে ডিফল্টে বন্ধের দিন হওয়ার কথা।');
    }

    #[Test]
    public function end_date_before_start_date_is_rejected(): void
    {
        $this->actAs('admin', $this->institutionA);

        Livewire::test(AcademicCalendar::class)
            ->call('openModal', '2026-12-16')
            ->set('title', 'ভুল তারিখ')
            ->set('endDate', '2026-12-10')
            ->call('save')
            ->assertHasErrors(['endDate']);

        $this->assertSame(0, CalendarEvent::count());
    }

    #[Test]
    public function non_admin_cannot_create_edit_or_delete_events(): void
    {
        $event = CalendarEvent::factory()->create(['institution_id' => $this->institutionA->id]);

        $this->actAs('teacher', $this->institutionA);

        Livewire::test(AcademicCalendar::class)->call('openModal')->assertForbidden();
        Livewire::test(AcademicCalendar::class)->call('edit', $event->id)->assertForbidden();
        Livewire::test(AcademicCalendar::class)->call('delete', $event->id)->assertForbidden();

        $this->assertSame(1, CalendarEvent::count());
    }

    #[Test]
    public function admin_cannot_edit_or_delete_another_institutions_event(): void
    {
        $foreign = CalendarEvent::factory()->create(['institution_id' => $this->institutionB->id]);

        $this->actAs('admin', $this->institutionA);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        Livewire::test(AcademicCalendar::class)->call('delete', $foreign->id);
    }

    #[Test]
    public function audience_limited_events_are_hidden_from_other_roles(): void
    {
        $now = now();
        CalendarEvent::factory()->create([
            'institution_id' => $this->institutionA->id,
            'title' => 'শুধু শিক্ষকদের সভা',
            'type' => 'meeting',
            'audience' => ['teacher'],
            'start_date' => $now->toDateString(),
            'end_date' => $now->toDateString(),
        ]);

        $this->actAs('guardian', $this->institutionA);

        Livewire::test(AcademicCalendar::class)->assertDontSee('শুধু শিক্ষকদের সভা');
    }
}
