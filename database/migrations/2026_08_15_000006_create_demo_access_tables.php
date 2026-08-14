<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * পাবলিক ডেমো (edution.xyz) থেকে কে ডেমো দেখতে চাইছে তার একটা সফট লিড —
 * প্ল্যাটফর্ম-লেভেল টেবিল, কোনো নির্দিষ্ট institution-এর না (platform_notices
 * এর মতোই BelongsToTenant ব্যবহার হয়নি)।
 *
 * এডমিন ডেমো: রেজিস্ট্রেশন করলেই সাথে সাথে দেখা যায় (শুধু লিড-ক্যাপচার)।
 * শিক্ষক/অভিভাবক ডেমো: রিকোয়েস্ট করতে হয়, সুপার এডমিন কল দিয়ে যাচাই করে
 * নির্দিষ্ট সময়ের জন্য (৫/১০/কাস্টম মিনিট) আনলক করে দেয় — সেই সময়ের
 * মধ্যেই শুধু ওই role এর ফিক্সড ডেমো একাউন্ট দিয়ে লগইন করা যাবে
 * (LoginController এ চেক করা হয়, demo_lead অনুযায়ী না — role-ভিত্তিক
 * শেয়ার্ড আনলক-উইন্ডো, কারণ ক্রেডেনশিয়াল ফিক্সড/শেয়ার্ড)।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_leads', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('token')->unique();
            $table->string('name');
            $table->string('phone');
            $table->string('institution_name')->nullable();
            $table->timestamps();
        });

        Schema::create('demo_access_requests', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('demo_lead_id')->constrained('demo_leads')->cascadeOnDelete();
            $table->string('role'); // admin / teacher / guardian
            $table->string('status')->default('pending'); // pending / approved / rejected
            $table->timestamp('unlocked_until')->nullable();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['role', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_access_requests');
        Schema::dropIfExists('demo_leads');
    }
};
