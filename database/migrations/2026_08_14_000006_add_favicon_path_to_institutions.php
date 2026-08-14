<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * favicon_path — প্রতিষ্ঠান নিজের ব্রাউজার ফেভিকন সেট করতে পারবে
 * (সেটিংস পেজ থেকে), logo_path থেকে আলাদা রাখা হলো কারণ favicon সাধারণত
 * ছোট/স্কয়ার আইকন, logo বড় হতে পারে (হেডার/আইডি কার্ডে ব্যবহৃত)।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->string('favicon_path')->nullable()->after('logo_path');
        });
    }

    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn('favicon_path');
        });
    }
};
