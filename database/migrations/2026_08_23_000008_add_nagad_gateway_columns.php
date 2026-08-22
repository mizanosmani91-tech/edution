<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nagad Payment Gateway (Checkout API) এর জন্য দরকারি কলাম।
 * ⚠️ Nagad এর API bKash এর চেয়ে আলাদা — token/username-password ভিত্তিক না,
 * বরং RSA কী-পেয়ার ভিত্তিক (merchant private key দিয়ে সাইন করা হয়, Nagad এর
 * pg public key দিয়ে এনক্রিপ্ট করা হয়)। এই কী দুটো Nagad Merchant Portal থেকে
 * "Key Generate" করে ডাউনলোড করতে হয় (মার্চেন্ট প্রাইভেট কী + Nagad PG পাবলিক কী)।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('integration_settings', function (Blueprint $table) {
            $table->string('nagad_merchant_id')->nullable()->after('nagad_api_key');
            $table->text('nagad_merchant_private_key')->nullable()->after('nagad_merchant_id');
            $table->text('nagad_pg_public_key')->nullable()->after('nagad_merchant_private_key');
            $table->boolean('nagad_sandbox')->default(true)->after('nagad_pg_public_key');
        });
    }

    public function down(): void
    {
        Schema::table('integration_settings', function (Blueprint $table) {
            $table->dropColumn(['nagad_merchant_id', 'nagad_merchant_private_key', 'nagad_pg_public_key', 'nagad_sandbox']);
        });
    }
};
