<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TransportRoute extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id', 'route_name', 'vehicle_no', 'driver_name', 'driver_phone', 'capacity', 'monthly_fee',
        'tracking_token', 'last_lat', 'last_lng', 'last_location_at',
    ];

    protected $casts = [
        'monthly_fee' => 'decimal:2',
        'last_lat' => 'decimal:7',
        'last_lng' => 'decimal:7',
        'last_location_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // ⚠️ ড্রাইভারের শেয়ার-লিংক তৈরির জন্য প্রতিটা রুটে একটা গ্লোবালি-ইউনিক,
        // আন্দাজ-করা-যায়-না এমন টোকেন লাগে (কোনো auth ছাড়াই ড্রাইভার এই লিংক
        // খুলবে) — তাই creating() এ অটো-জেনারেট করা হচ্ছে।
        static::creating(function (self $route) {
            if (empty($route->tracking_token)) {
                $route->tracking_token = Str::random(32);
            }
        });
    }

    /**
     * শেষ লোকেশন আপডেট ৩ মিনিটের মধ্যে হয়েছে কিনা — "লাইভ" দেখানোর জন্য।
     */
    public function isLocationLive(): bool
    {
        return $this->last_location_at && $this->last_location_at->gt(now()->subMinutes(3));
    }

    public function assignments()
    {
        return $this->hasMany(StudentTransport::class, 'route_id');
    }
}
