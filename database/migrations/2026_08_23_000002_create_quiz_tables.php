<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * অনলাইন MCQ কুইজ মডিউল — Question Bank এর mcq-টাইপ প্রশ্ন থেকে কুইজ বানানো
 * যায়, শিক্ষার্থী নির্দিষ্ট সময়সীমার মধ্যে অনলাইনে দিয়ে সাথে সাথে ফলাফল
 * পায় (কোনো ম্যানুয়াল মার্কিং লাগে না — MCQ বলেই অটো-গ্রেড সম্ভব)।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('institution_id');
            $table->string('title');
            $table->uuid('class_id');
            $table->uuid('subject_id')->nullable();
            $table->uuid('created_by'); // users.id (admin/teacher)
            $table->unsignedInteger('duration_minutes')->default(20);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('shuffle_questions')->default(true);
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->index('institution_id');
            $table->index('class_id');
        });

        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('institution_id');
            $table->uuid('quiz_id');
            $table->uuid('question_bank_item_id');
            $table->unsignedInteger('marks')->default(1);
            $table->unsignedInteger('order_no')->default(0);
            $table->timestamps();

            $table->foreign('quiz_id')->references('id')->on('quizzes')->cascadeOnDelete();
            $table->unique(['quiz_id', 'question_bank_item_id']);
        });

        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('institution_id');
            $table->uuid('quiz_id');
            $table->uuid('student_id');
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('score', 8, 2)->default(0);
            $table->decimal('total_marks', 8, 2)->default(0);
            $table->string('status')->default('in_progress'); // in_progress / submitted
            $table->timestamps();

            $table->foreign('quiz_id')->references('id')->on('quizzes')->cascadeOnDelete();
            $table->unique(['quiz_id', 'student_id']);
        });

        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('institution_id');
            $table->uuid('quiz_attempt_id');
            $table->uuid('quiz_question_id');
            $table->text('selected_answer')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->decimal('marks_awarded', 8, 2)->default(0);
            $table->timestamps();

            $table->foreign('quiz_attempt_id')->references('id')->on('quiz_attempts')->cascadeOnDelete();
            $table->foreign('quiz_question_id')->references('id')->on('quiz_questions')->cascadeOnDelete();
            $table->unique(['quiz_attempt_id', 'quiz_question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_answers');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('quizzes');
    }
};
