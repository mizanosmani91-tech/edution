<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * শিক্ষার্থী লাইফসাইকেল: Certificates (TC/CC), Discipline Records, Health Records
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // transfer / character
            $table->string('certificate_no');
            $table->date('issue_date');
            $table->text('reason')->nullable();
            $table->text('remarks')->nullable();
            $table->uuid('issued_by')->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'type']);
        });

        Schema::create('discipline_records', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('category')->default('general'); // general/attendance/behavior/academic/other
            $table->string('severity')->default('minor'); // minor/moderate/severe
            $table->text('description');
            $table->text('action_taken')->nullable();
            $table->uuid('recorded_by')->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'student_id']);
        });

        Schema::create('student_health_records', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('student_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('height_cm', 5, 1)->nullable();
            $table->decimal('weight_kg', 5, 1)->nullable();
            $table->string('blood_group')->nullable();
            $table->text('allergies')->nullable();
            $table->text('chronic_conditions')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->date('last_checkup_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_health_records');
        Schema::dropIfExists('discipline_records');
        Schema::dropIfExists('certificates');
    }
};
