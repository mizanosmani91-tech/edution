<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * প্রশ্নপত্র বিল্ডার (শিক্ষক লিখবেন, এডমিন অ্যাপ্রুভ/প্রিন্ট করবেন) +
 * পরীক্ষার হলে দায়িত্বরত শিক্ষক অ্যাসাইনমেন্ট — এই দুটো ফিচারের জন্য
 * টেবিল/কলাম যোগ করা হলো।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_papers', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignUuid('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title')->nullable(); // ফাঁকা রাখলে exam+subject নাম থেকে অটো টাইটেল
            $table->string('duration_text')->default('১ ঘন্টা');
            $table->decimal('full_marks', 6, 2)->default(20);
            // draft: শিক্ষক লিখছেন, submitted: এডমিনের রিভিউয়ের অপেক্ষায়,
            // approved: প্রিন্ট করার জন্য প্রস্তুত
            $table->string('status')->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('question_paper_items', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('question_paper_id')->constrained('question_papers')->cascadeOnDelete();
            $table->unsignedSmallInteger('order_no')->default(1);
            $table->string('heading')->nullable(); // যেমনঃ "শব্দার্থ লিখ", "অনুবাদ কর"
            $table->decimal('marks', 5, 2)->default(5);
            $table->text('content'); // প্রশ্নের মূল লেখা — বাংলা+আরবি মিশ্রিত হতে পারে
            $table->timestamps();
        });

        Schema::table('exam_seat_plans', function (Blueprint $table) {
            $table->foreignUuid('assigned_teacher_id')->nullable()->after('capacity')->constrained('teachers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('exam_seat_plans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_teacher_id');
        });

        Schema::dropIfExists('question_paper_items');
        Schema::dropIfExists('question_papers');
    }
};
