<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Visitor — গেটে/রিসেপশনে ভিজিটরের লগ। প্রতিষ্ঠানে কে কখন ঢুকল-বের হলো তার
 * রেকর্ড রাখার জন্য (নিরাপত্তা ও জবাবদিহিতার প্রয়োজনে)।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('institution_id');
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('purpose');
            $table->string('meeting_with')->nullable(); // কার সাথে দেখা করতে এসেছে (ফ্রি টেক্সট)
            $table->string('id_type')->nullable(); // NID/অন্যান্য (ঐচ্ছিক)
            $table->string('id_number')->nullable();
            $table->timestamp('check_in');
            $table->timestamp('check_out')->nullable();
            $table->text('remarks')->nullable();
            $table->uuid('recorded_by')->nullable();
            $table->timestamps();

            $table->index('institution_id');
            $table->index('check_in');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
