<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ⚠️ superadmin ইউজারের জন্য phone নম্বর যোগ করা হলো — superadmin-এর
 * forgot-password SMS OTP ফ্লোতে ব্যবহৃত হবে (institution admin-এর ক্ষেত্রে
 * Institution::phone আগে থেকেই আছে, কিন্তু superadmin কোনো institution-এর
 * সাথে যুক্ত না, তাই তার নিজের একটা phone দরকার)।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }
};
