<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * বাকি থাকা ৬টা মডিউলের টেবিল: একাডেমিক সেশন, হোমওয়ার্ক, লেসন প্ল্যান,
 * প্রশ্ন ব্যাংক, বৃত্তি/মওকুফ। (Result Weighting আগে থেকেই exam_result_weightings
 * টেবিলে আছে, নতুন টেবিল লাগেনি — শুধু UI বানানো হয়েছে।)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // যেমনঃ ২০২৬ শিক্ষাবর্ষ
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->index('institution_id');
        });

        Schema::create('homeworks', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->uuid('class_id');
            $table->uuid('section_id')->nullable();
            $table->foreignUuid('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignUuid('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->date('assigned_date');
            $table->date('due_date');
            $table->string('attachment_path')->nullable();
            $table->timestamps();

            $table->foreign('class_id')->references('id')->on('classes')->cascadeOnDelete();
            $table->foreign('section_id')->references('id')->on('sections')->nullOnDelete();
            $table->index(['institution_id', 'class_id']);
        });

        Schema::create('lesson_plans', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->uuid('class_id');
            $table->foreignUuid('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignUuid('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->date('date');
            $table->text('objectives')->nullable();
            $table->text('content')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();

            $table->foreign('class_id')->references('id')->on('classes')->cascadeOnDelete();
            $table->index(['institution_id', 'class_id']);
        });

        Schema::create('question_bank_items', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->uuid('class_id')->nullable();
            $table->foreignUuid('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->string('question_type')->default('short'); // mcq/short/essay
            $table->string('difficulty')->default('medium'); // easy/medium/hard
            $table->text('question_text');
            $table->json('options')->nullable(); // mcq হলে ['a'=>'..','b'=>'..',...]
            $table->string('correct_answer')->nullable();
            $table->unsignedTinyInteger('marks')->default(1);
            $table->timestamps();

            $table->foreign('class_id')->references('id')->on('classes')->nullOnDelete();
            $table->index(['institution_id', 'subject_id']);
        });

        Schema::create('scholarships', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('scholarship'); // scholarship/waiver/discount
            $table->string('discount_mode')->default('percentage'); // percentage/fixed_amount
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->text('reason')->nullable();
            $table->string('status')->default('active'); // active/expired/revoked
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarships');
        Schema::dropIfExists('question_bank_items');
        Schema::dropIfExists('lesson_plans');
        Schema::dropIfExists('homeworks');
        Schema::dropIfExists('academic_sessions');
    }
};
