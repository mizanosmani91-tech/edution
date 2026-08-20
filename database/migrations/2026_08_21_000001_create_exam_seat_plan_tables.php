<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * পরীক্ষার সিট প্ল্যান — একটা পরীক্ষার জন্য কয়েকটা রুম/হল বানিয়ে,
 * প্রতিটা রুমে ছাত্রদের সিট নাম্বার সহ বসিয়ে দেওয়া (auto-generate বা
 * ম্যানুয়াল রিঅ্যাসাইন করার সুবিধাসহ)।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_seat_plans', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->string('room_name');
            $table->unsignedInteger('capacity')->default(30);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->index(['institution_id', 'exam_id']);
        });

        Schema::create('exam_seat_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignUuid('exam_seat_plan_id')->constrained('exam_seat_plans')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('seat_no');
            $table->timestamps();

            $table->unique(['exam_id', 'student_id']); // একজন ছাত্র একটা পরীক্ষায় একটাই সিট পাবে
            $table->unique(['exam_seat_plan_id', 'seat_no']); // একটা রুমে একটা সিট একবারই বসবে
            $table->index(['institution_id', 'exam_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_seat_assignments');
        Schema::dropIfExists('exam_seat_plans');
    }
};
