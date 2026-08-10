<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

/**
 * TenantIsolationTest
 *
 * এটাই আসল গার্ডরেল — RLS/global scope ঠিকমতো লাগানো আছে কিনা প্রতিটা
 * deploy-এর আগে অটোমেটিক যাচাই করে। নতুন প্রতিটা মডিউল (Exam, Fee, Attendance...)
 * যোগ করার সময় এই প্যাটার্ন কপি করে এক্সটেন্ড করুন।
 *
 * নিয়ম: প্রতিটা tenant-বাউন্ড মডেলের জন্য অন্তত এই দুইটা টেস্ট থাকতেই হবে:
 *   1. Institution A এর ইউজার দিয়ে Institution B এর ডেটা read করার চেষ্টা → খালি/৪০৩
 *   2. Institution A এর ইউজার দিয়ে Institution B এর ID ব্যবহার করে write করার চেষ্টা → ব্যর্থ
 *
 * CI pipeline-এ এই test suite ফেল করলে deploy আটকে দিন — এটা negotiable না।
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institutionA;
    private Institution $institutionB;
    private User $userA;
    private User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institutionA = Institution::factory()->create();
        $this->institutionB = Institution::factory()->create();

        $this->userA = User::factory()->create(['institution_id' => $this->institutionA->id]);
        $this->userB = User::factory()->create(['institution_id' => $this->institutionB->id]);
    }

    /** @test */
    public function user_cannot_read_another_institutions_students_via_query(): void
    {
        Student::factory()->count(3)->create(['institution_id' => $this->institutionA->id]);
        Student::factory()->count(2)->create(['institution_id' => $this->institutionB->id]);

        $this->actingAs($this->userA);
        $this->setTenantContextForTest($this->institutionA->id);

        $visibleStudents = Student::all();

        $this->assertCount(3, $visibleStudents, 'Institution A এর ইউজার শুধু নিজের ৩ জন ছাত্র দেখতে পাবে।');
        $this->assertTrue(
            $visibleStudents->every(fn ($s) => $s->institution_id === $this->institutionA->id),
            'কোনো student অন্য institution এর হতে পারবে না।'
        );
    }

    /** @test */
    public function user_cannot_read_another_institutions_student_via_api_endpoint(): void
    {
        $foreignStudent = Student::factory()->create(['institution_id' => $this->institutionB->id]);

        $response = $this->actingAs($this->userA)
            ->getJson("/api/students/{$foreignStudent->id}");

        // 403 বা 404 — কখনোই 200 না। ডেটা লিক হলে এই assertion ফেল করবে।
        $response->assertStatus(fn ($status) => in_array($status, [403, 404]));
        $this->assertStringNotContainsString(
            $foreignStudent->name ?? '',
            $response->getContent(),
            'অন্য institution এর student এর নাম response এ থাকা উচিত না।'
        );
    }

    /** @test */
    public function user_cannot_create_record_under_another_institution_id(): void
    {
        $this->actingAs($this->userA);
        $this->setTenantContextForTest($this->institutionA->id);

        // ইচ্ছাকৃতভাবে অন্য institution এর ID পাঠানোর চেষ্টা (spoofing attempt)
        $response = $this->postJson('/api/students', [
            'name' => 'Test Student',
            'institution_id' => $this->institutionB->id, // 👈 spoof করার চেষ্টা
        ]);

        $created = Student::allTenants()->latest('id')->first();

        $this->assertNotNull($created);
        $this->assertEquals(
            $this->institutionA->id,
            $created->institution_id,
            'BelongsToTenant trait এর creating() hook এই spoofed institution_id ওভাররাইড করে '
            . 'ইউজারের নিজের institution_id বসানোর কথা।'
        );
    }

    /** @test */
    public function query_without_tenant_context_throws_instead_of_returning_all_rows(): void
    {
        Student::factory()->count(5)->create(['institution_id' => $this->institutionA->id]);

        // ⚠️ tenant context ইচ্ছাকৃতভাবে সেট করা হয়নি এখানে
        $this->expectException(RuntimeException::class);

        Student::all(); // fail-closed behavior — সব ডেটা দেখানো উচিত না
    }

    /** @test */
    public function user_cannot_read_another_institutions_teachers(): void
    {
        \App\Models\Teacher::factory()->count(2)->create(['institution_id' => $this->institutionA->id]);
        \App\Models\Teacher::factory()->count(4)->create(['institution_id' => $this->institutionB->id]);

        $this->actingAs($this->userA);
        $this->setTenantContextForTest($this->institutionA->id);

        $this->assertCount(2, \App\Models\Teacher::all());
    }

    /** @test */
    public function user_cannot_create_fee_collection_for_another_institutions_student(): void
    {
        $foreignStudent = Student::factory()->create(['institution_id' => $this->institutionB->id]);

        $this->actingAs($this->userA);
        $this->setTenantContextForTest($this->institutionA->id);

        // Institution A এর ইউজার, Institution B এর student এর জন্য fee এন্ট্রি
        // করার চেষ্টা করছে — Rule::exists()->where('institution_id', ...) টা
        // এটা আটকাবে (প্লেইন 'exists:students,id' আটকাতো না — সেটা Eloquent
        // scope respect করে না, তাই controller এ explicit rule ব্যবহার করা হয়েছে)
        $response = $this->postJson('/fee-collections', [
            'student_id' => $foreignStudent->id,
            'fee_type' => 'monthly',
            'amount_due' => 500,
            'amount_paid' => 500,
            'payment_method' => 'bkash',
            'due_month' => '2026-08',
        ]);

        $response->assertStatus(422); // validation error, student খুঁজেই পায়নি
    }

    /** @test */
    public function user_cannot_bulk_mark_attendance_for_another_institutions_student(): void
    {
        $classA = \App\Models\SchoolClass::factory()->create(['institution_id' => $this->institutionA->id]);
        $foreignStudent = Student::factory()->create(['institution_id' => $this->institutionB->id]);

        $this->actingAs($this->userA);
        $this->setTenantContextForTest($this->institutionA->id);

        $response = $this->postJson('/attendance/bulk', [
            'class_id' => $classA->id,
            'date' => now()->toDateString(),
            'records' => [
                ['student_id' => $foreignStudent->id, 'status' => 'present'],
            ],
        ]);

        $response->assertStatus(422); // Rule::exists->where(institution_id) এখানে আটকাবে
    }

    /** @test */
    public function admin_role_required_to_create_exam_weighting(): void
    {
        $examA1 = \App\Models\Exam::factory()->create(['institution_id' => $this->institutionA->id]);
        $examA2 = \App\Models\Exam::factory()->create(['institution_id' => $this->institutionA->id]);

        // userA ডিফল্টভাবে 'admin' role এ factory তে তৈরি হয় না ধরে নিয়ে,
        // এখানে explicitly non-admin বানানো হলো
        $this->userA->update(['role' => 'teacher']);

        $this->actingAs($this->userA);
        $this->setTenantContextForTest($this->institutionA->id);

        $response = $this->postJson('/exam-weightings', [
            'target_exam_id' => $examA1->id,
            'source_exam_id' => $examA2->id,
            'contribution_type' => 'percentage',
            'weight_percentage' => 20,
        ]);

        $response->assertStatus(403); // app-level check + RLS দুটোই এটা আটকানোর কথা
    }

    /** @test */
    public function user_cannot_read_messages_of_a_conversation_they_are_not_part_of(): void
    {
        // Institution A এর দুইজন ইউজার (userA আর userC) একটা conversation
        // শেয়ার করছে, userA (এই টেস্টে) সেটার অংশ না
        $userC = User::factory()->create(['institution_id' => $this->institutionA->id]);
        $userD = User::factory()->create(['institution_id' => $this->institutionA->id]);

        $conversation = \App\Models\Conversation::factory()->create(['institution_id' => $this->institutionA->id]);
        \App\Models\ConversationParticipant::factory()->create([
            'institution_id' => $this->institutionA->id,
            'conversation_id' => $conversation->id,
            'user_id' => $userC->id,
        ]);
        \App\Models\ConversationParticipant::factory()->create([
            'institution_id' => $this->institutionA->id,
            'conversation_id' => $conversation->id,
            'user_id' => $userD->id,
        ]);

        // userA একই institution এর, কিন্তু এই conversation এর participant না
        $this->actingAs($this->userA);
        $this->setTenantContextForTest($this->institutionA->id);

        $response = $this->getJson("/chat/conversations/{$conversation->id}/messages");

        $response->assertStatus(403); // একই institution হলেও participant না হওয়ায় আটকাবে
    }

    /** @test */
    public function guardian_cannot_access_another_guardians_child_data(): void
    {
        $myChild = Student::factory()->create(['institution_id' => $this->institutionA->id]);
        $otherGuardiansChild = Student::factory()->create(['institution_id' => $this->institutionA->id]);

        $guardianUser = User::factory()->create([
            'institution_id' => $this->institutionA->id,
            'role' => 'guardian',
        ]);

        \DB::table('guardian_student')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'institution_id' => $this->institutionA->id,
            'guardian_id' => $guardianUser->id,
            'student_id' => $myChild->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($guardianUser);
        $this->setTenantContextForTest($this->institutionA->id);

        // নিজের সন্তান — অনুমতি থাকা উচিত
        $ownChild = $guardianUser->children()->where('students.id', $myChild->id)->exists();
        $this->assertTrue($ownChild);

        // একই institution এর অন্য guardian এর সন্তান — অনুমতি থাকা উচিত না
        $notMyChild = $guardianUser->children()->where('students.id', $otherGuardiansChild->id)->exists();
        $this->assertFalse($notMyChild, 'একই institution এর হলেও, নিজের সন্তান না হলে দেখা যাবে না।');
    }

    /**
     * টেস্টে Postgres session variable ম্যানুয়ালি সেট করার হেল্পার
     * (আসল middleware রিকোয়েস্ট সাইকেলের বাইরে টেস্টে কাজ করে না বলে)
     */
    private function setTenantContextForTest(string $institutionId): void
    {
        \DB::statement('SELECT set_config(?, ?, false)', ['app.current_institution_id', $institutionId]);
        app()->instance('tenant.institution_id', $institutionId);

        $userId = auth()->id();
        if ($userId) {
            \DB::statement('SELECT set_config(?, ?, false)', ['app.current_user_id', $userId]);
            app()->instance('tenant.current_user_id', $userId);
        }
    }
}
