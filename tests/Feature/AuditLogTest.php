<?php

namespace Tests\Feature;

use App\Livewire\AuditLogViewer;
use App\Models\AuditLog;
use App\Models\FeeCollection;
use App\Models\FeeStructure;
use App\Models\Institution;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuditLogTest extends TestCase
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
        $user = User::factory()->create(['institution_id' => $institution->id, 'role' => $role]);
        $this->actingAs($user);

        \DB::statement('SELECT set_config(?, ?, false)', ['app.current_institution_id', $institution->id]);
        app()->instance('tenant.institution_id', $institution->id);

        return $user;
    }

    private function makeFee(Institution $institution, array $attrs = []): FeeCollection
    {
        $student = Student::factory()->create(['institution_id' => $institution->id]);

        return FeeCollection::factory()->create(['institution_id' => $institution->id, 'student_id' => $student->id] + $attrs);
    }

    #[Test]
    public function creating_a_fee_writes_a_log_with_the_actor(): void
    {
        $user = $this->actAs('admin', $this->a);

        $fee = $this->makeFee($this->a);

        $log = AuditLog::where('auditable_id', $fee->id)->first();
        $this->assertNotNull($log);
        $this->assertSame('created', $log->event);
        $this->assertSame(FeeCollection::class, $log->auditable_type);
        $this->assertSame($user->id, $log->user_id);
        $this->assertSame($user->name, $log->user_name);
        $this->assertSame('admin', $log->user_role);
        $this->assertSame($this->a->id, $log->institution_id);
    }

    #[Test]
    public function updating_logs_only_the_changed_fields_with_old_and_new_values(): void
    {
        $this->actAs('admin', $this->a);
        $fee = $this->makeFee($this->a, ['amount_paid' => 0, 'status' => 'due']);

        $fee->update(['amount_paid' => 500, 'status' => 'partial']);

        $log = AuditLog::where('auditable_id', $fee->id)->where('event', 'updated')->firstOrFail();
        $this->assertEquals(['amount_paid', 'status'], collect(array_keys($log->new_values))->sort()->values()->all());
        $this->assertEquals(0, $log->old_values['amount_paid']);
        $this->assertEquals(500, $log->new_values['amount_paid']);
        $this->assertSame('due', $log->old_values['status']);
        $this->assertSame('partial', $log->new_values['status']);
        $this->assertArrayNotHasKey('updated_at', $log->new_values);
    }

    #[Test]
    public function saving_without_real_changes_writes_no_log(): void
    {
        $this->actAs('admin', $this->a);
        $fee = $this->makeFee($this->a);
        $before = AuditLog::count();

        $fee->save();
        $fee->touch();

        $this->assertSame($before, AuditLog::count());
    }

    #[Test]
    public function deleting_logs_the_last_values(): void
    {
        $this->actAs('admin', $this->a);
        $fee = $this->makeFee($this->a, ['amount_due' => 1200]);

        $fee->delete();

        $log = AuditLog::where('auditable_id', $fee->id)->where('event', 'deleted')->firstOrFail();
        $this->assertEquals(1200, $log->old_values['amount_due']);
        $this->assertNull($log->new_values);
    }

    #[Test]
    public function other_audited_models_are_logged_too(): void
    {
        $this->actAs('admin', $this->a);

        $structure = FeeStructure::factory()->create([
            'institution_id' => $this->a->id,
            'class_id' => \App\Models\SchoolClass::factory()->create(['institution_id' => $this->a->id])->id,
        ]);

        $this->assertTrue(AuditLog::where('auditable_id', $structure->id)->where('auditable_type', FeeStructure::class)->exists());
    }

    #[Test]
    public function user_cannot_read_another_institutions_logs(): void
    {
        $this->actAs('admin', $this->b);
        $this->makeFee($this->b);
        $this->makeFee($this->b);

        $this->actAs('admin', $this->a);
        $this->makeFee($this->a);

        $this->assertSame(1, AuditLog::where('auditable_type', FeeCollection::class)->count());
    }

    #[Test]
    public function logs_cannot_be_updated_or_deleted(): void
    {
        $this->actAs('admin', $this->a);
        $this->makeFee($this->a);
        $log = AuditLog::firstOrFail();

        try {
            $log->update(['user_name' => 'কেউ একজন']);
            $this->fail('অডিট লগ আপডেট হয়ে গেছে।');
        } catch (LogicException) {
            $this->assertTrue(true);
        }

        $this->expectException(LogicException::class);
        $log->delete();
    }

    #[Test]
    public function only_admin_can_open_the_viewer(): void
    {
        $this->actAs('teacher', $this->a);
        Livewire::test(AuditLogViewer::class)->assertForbidden();

        $this->actAs('guardian', $this->a);
        Livewire::test(AuditLogViewer::class)->assertForbidden();
    }

    #[Test]
    public function admin_sees_logs_and_can_filter_by_type(): void
    {
        $this->actAs('admin', $this->a);
        $this->makeFee($this->a, ['due_month' => '2026-09']);
        FeeStructure::factory()->create([
            'institution_id' => $this->a->id,
            'class_id' => \App\Models\SchoolClass::factory()->create(['institution_id' => $this->a->id])->id,
            'fee_type' => 'exam',
        ]);

        // ড্রপডাউনে দুই ধরনের নামই সব সময় থাকে, তাই লগের নিজস্ব লেবেল দিয়ে যাচাই
        Livewire::test(AuditLogViewer::class)
            ->assertSee('monthly 2026-09')
            ->assertSee('exam (monthly)')
            ->set('typeFilter', FeeStructure::class)
            ->assertSee('exam (monthly)')
            ->assertDontSee('monthly 2026-09');
    }

    #[Test]
    public function unknown_filter_values_are_ignored_not_trusted(): void
    {
        $this->actAs('admin', $this->a);
        $this->makeFee($this->a);

        Livewire::test(AuditLogViewer::class)
            ->set('typeFilter', 'App\\Models\\User')
            ->set('dateFrom', 'not-a-date')
            ->assertSee('monthly ' . now()->format('Y-m'));
    }
}
