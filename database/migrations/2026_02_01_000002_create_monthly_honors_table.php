<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * monthly_honors — "মাসের সেরা" শিক্ষার্থী/শিক্ষক/স্টাফ, স্বয়ংক্রিয়ভাবে
 * হাজিরা% + রেজাল্ট/পারফরম্যান্স স্কোরের ভিত্তিতে হিসাব করে প্রতিষ্ঠান-ভিত্তিক
 * সংরক্ষণ করা হয় (সব ইউজারের জন্য একই রকম দেখাবে)।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_honors', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->string('category'); // student / teacher / staff
            $table->string('month');    // YYYY-MM
            $table->uuid('student_id')->nullable();
            $table->uuid('teacher_id')->nullable();
            $table->decimal('score', 6, 2)->default(0);
            $table->json('metrics')->nullable(); // { attendance_pct, avg_marks, performance_avg... }
            $table->timestamps();

            $table->unique(['institution_id', 'category', 'month']);
            $table->index(['institution_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_honors');
    }
};
