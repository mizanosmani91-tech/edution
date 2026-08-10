<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * institution_settings — প্রতিষ্ঠান-ভিত্তিক ফিচার টগলের জন্য এক-জায়গায় টেবিল।
 * has_departments ছাড়াও ভবিষ্যতে consecutive_period_blocking, qawmi_grading
 * ইত্যাদি টগলও এখানেই যোগ করা যাবে — নতুন টেবিল লাগবে না।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institution_settings', function (Blueprint $table) {
            $table->foreignUuid('institution_id')->primary()->constrained()->cascadeOnDelete();
            $table->boolean('has_departments')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institution_settings');
    }
};
