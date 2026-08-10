<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * role='teacher' এর user হলে teacher_id, role='student' হলে student_id সেট
 * থাকবে — portal এ "আমার নিজের ডেটা" রিজলভ করার জন্য এটাই একমাত্র সোর্স।
 * দুটোই nullable, কারণ admin/guardian/superadmin এর কোনোটাই লাগবে না।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignUuid('teacher_id')->nullable()->after('role')->constrained('teachers')->nullOnDelete();
            $table->foreignUuid('student_id')->nullable()->after('teacher_id')->constrained('students')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['student_id']);
            $table->dropColumn(['teacher_id', 'student_id']);
        });
    }
};
