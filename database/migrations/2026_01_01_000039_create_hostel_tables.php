<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostel_rooms', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->string('room_no');
            $table->string('room_type')->nullable(); // সাধারণ / এসি / ডাবল / সিঙ্গেল
            $table->unsignedInteger('capacity')->default(1);
            $table->decimal('monthly_fee', 8, 2)->default(0);
            $table->timestamps();

            $table->index('institution_id');
        });

        Schema::create('student_hostels', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('room_id')->constrained('hostel_rooms')->cascadeOnDelete();
            $table->date('check_in_date');
            $table->timestamps();

            $table->unique('student_id');
            $table->index('institution_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_hostels');
        Schema::dropIfExists('hostel_rooms');
    }
};
