<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * guardian_student — একজন guardian একাধিক student এর সাথে যুক্ত হতে পারে
 * (multi-child পরিবার), আবার একজন student একাধিক guardian থাকতে পারে
 * (বাবা-মা দুইজনই)।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardian_student', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('guardian_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('relationship')->nullable(); // বাবা/মা/অভিভাবক
            $table->timestamps();

            $table->unique(['guardian_id', 'student_id']);
            $table->index('institution_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardian_student');
    }
};
