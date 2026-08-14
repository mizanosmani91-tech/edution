<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * platform_notices — superadmin থেকে সব/নির্দিষ্ট শ্রেণির প্রতিষ্ঠানে
 * পাঠানো প্ল্যাটফর্ম-লেভেল ঘোষণা (মেইনটেন্যান্স, নতুন ফিচার, বকেয়া রিমাইন্ডার)।
 * এটা কোনো institution-এর না, তাই BelongsToTenant ব্যবহার হয়নি।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_notices', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('title');
            $table->text('body');
            $table->enum('notice_type', ['general', 'urgent', 'feature', 'maintenance'])->default('general');
            // audience: 'all' | 'trial' | 'premium' | 'overdue' — ভবিষ্যতে দরকার হলে আরও যোগ করা যাবে
            $table->string('audience')->default('all');
            $table->unsignedInteger('reached_count')->default(0);
            $table->foreignUuid('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_notices');
    }
};
