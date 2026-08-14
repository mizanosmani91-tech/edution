<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ৪-ধাপের রেজিস্ট্রেশন উইজার্ডের বাকি ফিল্ডগুলো সংরক্ষণের জন্য (EIIN,
 * বিভাগ/জেলা, প্রতিষ্ঠার সাল, এডমিনের নাম/পদবি, পছন্দের সাবডোমেইন)।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->string('eiin')->nullable()->after('institution_type');
            $table->string('division')->nullable()->after('eiin');
            $table->string('district')->nullable()->after('division');
            $table->string('founding_year')->nullable()->after('district');
            $table->string('admin_name')->nullable()->after('registration_email');
            $table->string('admin_designation')->nullable()->after('admin_name');
            $table->string('preferred_subdomain')->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn(['eiin', 'division', 'district', 'founding_year', 'admin_name', 'admin_designation', 'preferred_subdomain']);
        });
    }
};
