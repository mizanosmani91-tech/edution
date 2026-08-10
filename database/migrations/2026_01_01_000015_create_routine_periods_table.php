<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routine_periods', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignUuid('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignUuid('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 1-7
            $table->unsignedTinyInteger('period_number');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();

            $table->unique(['class_id', 'section_id', 'day_of_week', 'period_number']);
            // একই teacher, একই দিন, একই পিরিয়ডে দুই জায়গায় থাকতে পারবে না
            $table->unique(['teacher_id', 'day_of_week', 'period_number'], 'teacher_no_double_booking');
            $table->index('institution_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routine_periods');
    }
};
