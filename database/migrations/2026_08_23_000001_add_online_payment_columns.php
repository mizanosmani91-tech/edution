<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * অটোমেটিক অনলাইন ফি পেমেন্ট (bKash Tokenized Checkout) এর জন্য দরকারি কলাম।
 * - fee_collections: গেটওয়ে থেকে আসা পেমেন্ট ট্র্যাক করার জন্য
 * - integration_settings: bKash Checkout API এর জন্য username/password/sandbox flag
 *   (app_key/app_secret আগে থেকেই bkash_api_key/bkash_api_secret কলামে আছে)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_collections', function (Blueprint $table) {
            $table->string('online_gateway')->nullable()->after('guardian_claim_status');
            $table->string('online_payment_id')->nullable()->after('online_gateway');
            $table->string('online_trx_id')->nullable()->after('online_payment_id');
            $table->string('online_status')->nullable()->after('online_trx_id'); // pending / completed / failed
            $table->timestamp('online_initiated_at')->nullable()->after('online_status');
        });

        Schema::table('integration_settings', function (Blueprint $table) {
            $table->string('bkash_username')->nullable()->after('bkash_api_secret');
            $table->string('bkash_password')->nullable()->after('bkash_username');
            $table->boolean('bkash_sandbox')->default(true)->after('bkash_password');
        });
    }

    public function down(): void
    {
        Schema::table('fee_collections', function (Blueprint $table) {
            $table->dropColumn(['online_gateway', 'online_payment_id', 'online_trx_id', 'online_status', 'online_initiated_at']);
        });

        Schema::table('integration_settings', function (Blueprint $table) {
            $table->dropColumn(['bkash_username', 'bkash_password', 'bkash_sandbox']);
        });
    }
};
