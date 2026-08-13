<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('isbn')->nullable();
            $table->string('category')->nullable();
            $table->unsignedInteger('total_copies')->default(1);
            $table->unsignedInteger('available_copies')->default(1);
            $table->timestamps();

            $table->index('institution_id');
        });

        Schema::create('book_issues', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('book_id')->constrained('books')->cascadeOnDelete();
            $table->foreignUuid('student_id')->nullable()->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('teacher_id')->nullable()->constrained('teachers')->cascadeOnDelete();
            $table->date('issued_at');
            $table->date('due_date');
            $table->date('returned_at')->nullable();
            $table->decimal('fine_amount', 8, 2)->default(0);
            $table->string('status')->default('issued'); // issued / returned / overdue
            $table->timestamps();

            $table->index(['institution_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_issues');
        Schema::dropIfExists('books');
    }
};
