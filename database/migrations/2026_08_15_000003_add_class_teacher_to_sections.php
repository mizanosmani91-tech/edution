<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * সেকশনের "ক্লাস শিক্ষক" (homeroom/class teacher) — অভিভাবক পোর্টাল থেকে
 * সরাসরি এই শিক্ষকের সাথে মেসেজ করা যাবে। nullable রাখা হলো, কারণ
 * সব প্রতিষ্ঠান/সেকশনে এখনই এটা নির্ধারণ করা নাও থাকতে পারে।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->foreignUuid('class_teacher_id')->nullable()->after('capacity')->constrained('teachers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('class_teacher_id');
        });
    }
};
