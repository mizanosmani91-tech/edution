<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * একটা হোমওয়ার্কে একজন শিক্ষার্থী পড়া করেছে কিনা — শিক্ষক এটা চেক করে
 * মার্ক করবেন (Attendance এর মতোই প্যাটার্ন)। রেকর্ড না থাকা মানে এখনো
 * চেক করা হয়নি (status default রাখা হয়নি ইচ্ছাকৃতভাবে, যাতে
 * "চেক করা হয়নি" আর "not_done" আলাদা বোঝা যায়)।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homework_completions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('homework_id')->constrained('homeworks')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->string('status'); // done / not_done / partial
            $table->text('remarks')->nullable();
            $table->foreignUuid('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['homework_id', 'student_id']);
            $table->index(['institution_id', 'homework_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homework_completions');
    }
};
