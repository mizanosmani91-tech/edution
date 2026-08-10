<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * exam_subjects — exam + subject + class এর combination, একটা নির্দিষ্ট
 * exam এ একটা নির্দিষ্ট ক্লাসের একটা subject কত মার্কের হবে সেটা এখানে সেট হয়।
 * get_own_exam_subject_marks() ফাংশন এই টেবিলটাই মূলত খোঁজে।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_subjects', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->uuid('class_id'); // FK পরের migration এ যোগ হবে (000005 এ আছে)
            $table->foreignUuid('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->decimal('full_marks', 6, 2)->default(100);
            $table->decimal('pass_marks', 6, 2)->default(33);
            $table->timestamps();

            $table->unique(['exam_id', 'subject_id', 'class_id']);
            $table->index('institution_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_subjects');
    }
};
