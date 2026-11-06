<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * বাস্তব HTTP অনুরোধে রোল-অনুযায়ী প্রবেশাধিকার।
 * শুধু "৪০৩ কি না" যাচাই করে — পেজের বিষয়বস্তু অন্য টেস্টের কাজ।
 */
class PermissionAccessTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institution = Institution::factory()->create();
    }

    private function loginAs(string $role): User
    {
        // বাস্তবে শিক্ষকের teacher_id ও শিক্ষার্থীর student_id থাকে; কিছু কম্পোনেন্ট
        // (MarksEntry, StudentPortal, StudentQuizList) নিজেই এগুলো না থাকলে ৪০৩ দেয় — সেটা
        // পারমিশন-ব্যবস্থার বিষয় নয়, তাই টেস্ট ইউজারকেও সেভাবেই বানানো হয়।
        $links = match ($role) {
            'teacher' => ['teacher_id' => \App\Models\Teacher::factory()->create(['institution_id' => $this->institution->id])->id],
            'student' => ['student_id' => \App\Models\Student::factory()->create(['institution_id' => $this->institution->id])->id],
            default => [],
        };

        $user = User::factory()->create([
            'institution_id' => $this->institution->id,
            'role' => $role,
            'must_change_password' => false,
        ] + $links);

        $this->actingAs($user);

        return $user;
    }

    /** @return array<string,array{0:string,1:string}> */
    public static function forbiddenPages(): array
    {
        $out = [];

        // যে রোলের যে পেজ ধরা উচিত নয়
        $matrix = [
            'teacher' => ['/payroll', '/fees-page', '/students-page', '/teachers-page', '/payment-gateway-settings', '/settings-page', '/audit-log', '/export/students', '/import/students', '/staff-attendance-page', '/inventory', '/portal/guardian', '/portal/student'],
            'guardian' => ['/payroll', '/fees-page', '/students-page', '/teachers-page', '/marks-entry', '/homework', '/attendance-page', '/audit-log', '/export/fees', '/portal/teacher', '/portal/student', '/notice-board-page'],
            'student' => ['/payroll', '/fees-page', '/students-page', '/marks-entry', '/homework', '/audit-log', '/portal/guardian', '/portal/teacher'],
        ];

        foreach ($matrix as $role => $urls) {
            foreach ($urls as $url) {
                $out["{$role} → {$url}"] = [$role, $url];
            }
        }

        return $out;
    }

    #[Test]
    #[DataProvider('forbiddenPages')]
    public function role_is_forbidden_from_page(string $role, string $url): void
    {
        $this->loginAs($role);

        $this->get($url)->assertForbidden();
    }

    #[Test]
    public function student_and_teacher_are_blocked_from_starting_an_online_payment(): void
    {
        // আসল ফি দিয়ে — নাহলে রাউট-বাইন্ডিং ৪০৪ দেয়, পারমিশন মিডলওয়্যার পর্যন্ত পৌঁছায় না
        $fee = \App\Models\FeeCollection::factory()->create([
            'institution_id' => $this->institution->id,
            'student_id' => \App\Models\Student::factory()->create(['institution_id' => $this->institution->id])->id,
        ]);

        foreach (['student', 'teacher'] as $role) {
            $this->loginAs($role);
            $this->get("/online-payment/{$fee->id}/initiate")->assertForbidden();
        }
    }

    #[Test]
    public function guardian_cannot_write_through_the_json_endpoints(): void
    {
        $this->loginAs('guardian');

        $this->postJson('/fee-collections', [])->assertForbidden();
        $this->postJson('/students', [])->assertForbidden();
        $this->postJson('/teachers', [])->assertForbidden();
        $this->postJson('/attendance/bulk', [])->assertForbidden();
        $this->putJson('/settings', [])->assertForbidden();
        $this->postJson('/routine', [])->assertForbidden();
    }

    #[Test]
    public function admin_is_not_blocked_by_the_permission_layer(): void
    {
        $this->loginAs('admin');

        foreach (['/payroll', '/fees-page', '/students-page', '/audit-log', '/academic-calendar', '/class-diary'] as $url) {
            $this->assertNotSame(403, $this->get($url)->getStatusCode(), "admin {$url} এ ৪০৩ পাচ্ছে");
        }
    }

    #[Test]
    public function teacher_keeps_access_to_their_working_pages(): void
    {
        $this->loginAs('teacher');

        foreach (['/homework', '/class-diary', '/attendance-page', '/marks-entry', '/academic-calendar', '/portal/teacher', '/chat-page'] as $url) {
            $this->assertNotSame(403, $this->get($url)->getStatusCode(), "teacher {$url} এ ৪০৩ পাচ্ছে");
        }
    }

    #[Test]
    public function guardian_and_student_keep_access_to_their_own_portals(): void
    {
        $this->loginAs('guardian');
        $this->assertNotSame(403, $this->get('/portal/guardian')->getStatusCode());

        $this->loginAs('student');
        $this->assertNotSame(403, $this->get('/portal/student')->getStatusCode());
        $this->assertNotSame(403, $this->get('/my-quizzes')->getStatusCode());
    }

    #[Test]
    public function user_with_an_unknown_role_is_denied_everywhere_protected(): void
    {
        $this->loginAs('intruder');

        foreach (['/academic-calendar', '/chat-page', '/portal/guardian', '/students-page'] as $url) {
            $this->get($url)->assertForbidden();
        }
    }
}
