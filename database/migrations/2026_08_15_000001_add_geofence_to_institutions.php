<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ⚠️ শিক্ষক/স্টাফের নিজে চেক-ইন/চেক-আউট ফিচারের জন্য প্রতিষ্ঠানের নিজস্ব
 * অবস্থান (lat/lng) ও অনুমোদিত ব্যাসার্ধ (মিটার) — এর বাইরে থেকে চেক-ইন
 * করা যাবে না। null থাকলে geofence চেক স্কিপ হবে (এখনো সেট করা হয়নি)।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->unsignedInteger('geofence_radius_meters')->default(150)->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'geofence_radius_meters']);
        });
    }
};
