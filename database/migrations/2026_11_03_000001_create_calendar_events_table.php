<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * অ্যাকাডেমিক ক্যালেন্ডার — প্রতিষ্ঠানের ছুটি, পরীক্ষা, ইভেন্ট ও মিটিং এক জায়গায়।
 * `is_off_day = true` মানে ওই দিনগুলোতে ক্লাস/অফিস বন্ধ (হাজিরা ইত্যাদি
 * মডিউল পরে এটা দেখে সিদ্ধান্ত নিতে পারবে)।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('institution_id');
            $table->string('title', 150);
            $table->string('type', 20)->default('event'); // holiday|exam|event|meeting|other
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_off_day')->default(false);
            $table->json('audience')->nullable(); // null = সকলের জন্য
            $table->text('description')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'start_date']);
            $table->index(['institution_id', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
