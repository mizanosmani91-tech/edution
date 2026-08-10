<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ⚠️ UUID FIX: আসল 029 migration দেখে নিশ্চিত হলাম আপনার স্কিমা সব জায়গায়
 * `uuid` primary key ব্যবহার করে (Supabase/gen_random_uuid() কনভেনশন)।
 * তাই এখানে Laravel এর ডিফল্ট bigint auto-increment বাদ দিয়ে uuid ব্যবহার
 * করা হলো — students, teachers, classes, sections, exams, subjects সব জায়গায়
 * এই একই কনভেনশন বজায় রাখা হয়েছে যেন আসল ডেটার সাথে মেলে।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status')->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('admin');
            $table->rememberToken();
            $table->timestamps();

            $table->unique(['institution_id', 'email']);
        });

        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('student_id_no');
            $table->uuid('class_id')->nullable();   // FK পরের migration এ (classes টেবিল তৈরির পর)
            $table->uuid('section_id')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('status')->default('active'); // get_effective_exam_marks এ st.status='active' চেক হয়
            $table->timestamps();

            $table->unique(['institution_id', 'student_id_no']);
            $table->index('institution_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
        Schema::dropIfExists('users');
        Schema::dropIfExists('institutions');
    }
};
