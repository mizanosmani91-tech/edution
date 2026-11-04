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
        $user = User::factory()->create([
            'institution_id' => $this->institution->id,
            'role' => $role,
            'must_change_password' => false,
        ]);

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
            'student' => ['/payroll', '/fees-page', '/students-page', '/marks-entry', '/homework', '/audit-log', '/portal/guardian', '/portal/teacher', '/online-payment/00000000-0000-0000-0000-000000000000/initiate'],
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
