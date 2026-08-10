<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('teacher_id_no');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('designation')->nullable();
            $table->date('joining_date')->nullable();
            $table->timestamps();

            $table->unique(['institution_id', 'teacher_id_no']);
            $table->index('institution_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
