<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ফ্লোটিং "সাহায্য দরকার?" উইজেট থেকে টিকেট খুললে কোন পেজ থেকে
 * পাঠানো হয়েছে ও ইউজারের ব্রাউজার/ডিভাইস তথ্য অটো-অ্যাটাচ হয়ে যায় —
 * যাতে সাপোর্ট টিমকে বারবার "কোন পেজে সমস্যা?" জিজ্ঞেস করতে না হয়।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_ticket_messages', function (Blueprint $table) {
            $table->string('page_url')->nullable()->after('body');
            $table->string('browser_info')->nullable()->after('page_url');
        });
    }

    public function down(): void
    {
        Schema::table('support_ticket_messages', function (Blueprint $table) {
            $table->dropColumn(['page_url', 'browser_info']);
        });
    }
};
