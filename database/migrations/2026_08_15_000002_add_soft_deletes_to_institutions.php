<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ⚠️ প্রতিষ্ঠান ডিলিট এখন soft-delete (রিসাইকেল বিন) — কেউ ভুল করে বা
 * ইচ্ছাকৃতভাবে (একাধিক superadmin থাকলে) ডিলিট করলেও ডেটা সাথে সাথে হারিয়ে
 * যায় না, রিসাইকেল বিন থেকে ফেরানো যায়। deleted_by দিয়ে জবাবদিহিতা
 * (accountability) থাকে — কে ডিলিট করেছে সেটা ট্র্যাক করা যায়।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->softDeletes();
            $table->uuid('deleted_by')->nullable()->after('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn(['deleted_at', 'deleted_by']);
        });
    }
};
