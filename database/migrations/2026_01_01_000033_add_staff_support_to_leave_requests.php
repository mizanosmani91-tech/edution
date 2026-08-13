<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * leave_requests টেবিলে শিক্ষক/স্টাফের ছুটির আবেদন সাপোর্ট যোগ করা হলো।
 * এখন পর্যন্ত এই টেবিল শুধু student_id বাধ্যতামূলক রেখে বানানো হয়েছিল
 * (শুধু student/guardian ছুটির আবেদন ধরে নেওয়া হয়েছিল) — সেটা nullable
 * করে দিয়ে applicant_type দিয়ে student/teacher আলাদা করা হচ্ছে।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->string('applicant_type')->default('student')->after('institution_id'); // student / teacher
            $table->foreignUuid('teacher_id')->nullable()->after('student_id')->constrained('teachers')->cascadeOnDelete();
            $table->string('leave_type')->nullable()->after('teacher_id'); // casual / sick / personal / maternity_paternity / family / other
            $table->string('attachment_path')->nullable()->after('reason');
        });

        DB::statement('ALTER TABLE leave_requests ALTER COLUMN student_id DROP NOT NULL');
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('teacher_id');
            $table->dropColumn(['applicant_type', 'leave_type', 'attachment_path']);
        });
    }
};
