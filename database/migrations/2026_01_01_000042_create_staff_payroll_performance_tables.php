<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * স্টাফ পে-রোল ও পারফরম্যান্স মূল্যায়ন
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('month'); // 1-12
            $table->unsignedSmallInteger('year');
            $table->decimal('base_salary', 10, 2)->default(0);
            $table->decimal('house_rent', 10, 2)->default(0);
            $table->decimal('medical_allowance', 10, 2)->default(0);
            $table->decimal('other_allowance', 10, 2)->default(0);
            $table->decimal('deductions', 10, 2)->default(0);
            $table->text('deduction_reason')->nullable();
            $table->decimal('net_pay', 10, 2)->default(0);
            $table->string('status')->default('pending'); // pending/paid
            $table->date('paid_date')->nullable();
            $table->uuid('paid_by')->nullable();
            $table->timestamps();

            $table->unique(['teacher_id', 'month', 'year']);
            $table->index(['institution_id', 'year', 'month']);
        });

        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->constrained()->cascadeOnDelete();
            $table->string('review_period'); // e.g. "2026 - প্রথম প্রান্তিক"
            $table->date('review_date');
            $table->unsignedTinyInteger('teaching_quality')->default(0); // 1-5
            $table->unsignedTinyInteger('punctuality')->default(0);
            $table->unsignedTinyInteger('discipline')->default(0);
            $table->unsignedTinyInteger('cooperation')->default(0);
            $table->text('strengths')->nullable();
            $table->text('improvement_areas')->nullable();
            $table->uuid('reviewed_by')->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'teacher_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
        Schema::dropIfExists('payroll_records');
    }
};
