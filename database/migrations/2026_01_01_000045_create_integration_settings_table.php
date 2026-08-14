<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * integration_settings — অনলাইন পেমেন্ট গেটওয়ে ও SMS/Email নোটিফিকেশন কনফিগারেশন।
 * ⚠️ এটা শুধু সেটিংস স্টোরেজ — আসল bKash/Nagad/SMS API কল এখানে ইমপ্লিমেন্ট করা
 * হয়নি, কারণ সেটার জন্য প্রতিষ্ঠানের real merchant/API credentials লাগবে যা
 * এখনো দেওয়া হয়নি। যতক্ষণ না *_enabled true করা হচ্ছে এবং real credential
 * verify হচ্ছে, ততক্ষণ কোনো লাইভ চার্জ/SMS/ইমেইল পাঠানো হবে না।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_settings', function (Blueprint $table) {
            $table->foreignUuid('institution_id')->primary()->constrained()->cascadeOnDelete();

            $table->boolean('bkash_enabled')->default(false);
            $table->string('bkash_merchant_number')->nullable();
            $table->text('bkash_api_key')->nullable();
            $table->text('bkash_api_secret')->nullable();

            $table->boolean('nagad_enabled')->default(false);
            $table->string('nagad_merchant_number')->nullable();
            $table->text('nagad_api_key')->nullable();

            $table->boolean('sms_enabled')->default(false);
            $table->string('sms_provider')->nullable(); // bulksmsbd/alpha_sms/twilio/other
            $table->text('sms_api_key')->nullable();
            $table->string('sms_sender_id')->nullable();

            $table->boolean('email_enabled')->default(false);
            $table->string('email_smtp_host')->nullable();
            $table->string('email_smtp_port')->nullable();
            $table->string('email_smtp_username')->nullable();
            $table->text('email_smtp_password')->nullable();
            $table->string('email_smtp_encryption')->nullable(); // tls/ssl/none
            $table->string('email_from_address')->nullable();
            $table->string('email_from_name')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_settings');
    }
};
