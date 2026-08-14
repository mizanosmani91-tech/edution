<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\UuidPrimaryKey;

/**
 * Institution
 *
 * এটাই tenant root — এই মডেলে BelongsToTenant trait লাগাবেন না
 * (একটা institution নিজেই তো tenant, সে নিজেকে filter করবে না)।
 * সাব-ডোমেইন (<slug>.edution.xyz) থেকে রিজলভ করার জন্য `slug` কলাম আছে।
 */
class Institution extends Model
{
    use HasFactory, UuidPrimaryKey, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',        // subdomain slug, e.g. "green-valley-school"
        'status',      // trial / active / suspended
        'trial_ends_at',
        'phone',
        'address',
        'logo_path',
        'favicon_path',
        'registration_email',
        'institution_type',
        'plan',
        'billing_type',
        'prepaid_balance',
        'billing_last_charged_month',
        'billing_due_at',
        'billing_grace_ends_at',
        'billing_suspended',
        'student_count_estimate',
        'eiin',
        'division',
        'district',
        'founding_year',
        'admin_name',
        'admin_designation',
        'preferred_subdomain',
        'enabled_modules',
        'student_limit_override',
        'latitude',
        'longitude',
        'geofence_radius_meters',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'enabled_modules' => 'array',
        'prepaid_balance' => 'decimal:2',
        'billing_due_at' => 'date',
        'billing_grace_ends_at' => 'date',
        'billing_suspended' => 'boolean',
    ];

    // superadmin "প্রতিষ্ঠান পরিচালনা" মোডালে যেসব মডিউল টগল করা যায় —
    // key => বাংলা লেবেল। enabled_modules কলামে null মানে সব ডিফল্টভাবে চালু।
    public const TOGGLEABLE_MODULES = [
        'academic' => 'একাডেমিক',
        'attendance' => 'হাজিরা',
        'admission' => 'ভর্তি',
        'students' => 'শিক্ষার্থী',
        'staff' => 'শিক্ষক ও স্টাফ',
        'finance' => 'অর্থ ব্যবস্থাপনা',
        'communication' => 'যোগাযোগ',
        'library' => 'লাইব্রেরি',
        'transport' => 'পরিবহন',
        'reports' => 'রিপোর্ট',
    ];

    public function isModuleEnabled(string $key): bool
    {
        if ($this->enabled_modules === null) {
            return true; // ডিফল্ট: সব চালু, কেউ ইচ্ছা করে বন্ধ না করলে
        }

        return (bool) ($this->enabled_modules[$key] ?? false);
    }

    public function hasGeofence(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * Haversine সূত্র দিয়ে প্রতিষ্ঠানের অবস্থান থেকে দূরত্ব (মিটারে) —
     * চেক-ইন/চেক-আউট প্রতিষ্ঠানের বাইরে থেকে করা যাবে না এটা নিশ্চিত করতে।
     */
    public function distanceInMetersFrom(float $lat, float $lng): float
    {
        $earthRadius = 6371000; // মিটার

        $latDelta = deg2rad($lat - (float) $this->latitude);
        $lngDelta = deg2rad($lng - (float) $this->longitude);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad((float) $this->latitude)) * cos(deg2rad($lat)) * sin($lngDelta / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function isWithinGeofence(float $lat, float $lng): bool
    {
        if (! $this->hasGeofence()) {
            return true; // geofence সেট করা না থাকলে চেক স্কিপ
        }

        return $this->distanceInMetersFrom($lat, $lng) <= $this->geofence_radius_meters;
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function settings()
    {
        return $this->hasOne(InstitutionSetting::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function classes()
    {
        return $this->hasMany(SchoolClass::class);
    }

    /**
     * এই institution এ বিভাগ (department) ফিচার চালু আছে কিনা।
     * UI-তে সব জায়গায় (student form, teacher assign, exam setup) এই একটা
     * মেথড চেক করেই department dropdown দেখাবেন কিনা ঠিক করবেন।
     */
    /**
     * ⚠️ আগে এটা settings-এর একটা আলাদা ম্যানুয়াল টগল ছিল (has_departments)
     * যেটা admin ভুলে চালু না করলে বিভাগ ফিচার আসলেই কাজ করত না, অথচ
     * বিভাগ আগে থেকেই তৈরি করে রাখা যেত (কোনো guard ছিল না) — বিভ্রান্তিকর।
     * এখন এটা স্বয়ংক্রিয়: প্রতিষ্ঠানে অন্তত ১টা বিভাগ তৈরি হলেই সব জায়গায়
     * (ক্লাস ফর্মে বিভাগ ড্রপডাউন, বিভাগ-অনুযায়ী ফিল্টার ইত্যাদি) নিজে থেকেই
     * চালু হয়ে যায় — কোনো টগল মনে রাখা লাগে না।
     */
    public function hasDepartments(): bool
    {
        return \App\Models\Department::exists();
    }

    /**
     * consecutive period blocking চালু আছে কিনা — না থাকলে (settings row
     * এখনো তৈরিই না হয়ে থাকলে) ডিফল্ট true ধরা হচ্ছে, কারণ আগে এটা hard
     * rule ছিল — নতুন institution এও পুরনো আচরণ বজায় থাকবে যতক্ষণ না
     * কেউ ইচ্ছা করে off করে।
     */
    public function blocksConsecutivePeriods(): bool
    {
        return $this->settings === null ? true : (bool) $this->settings->consecutive_period_blocking;
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function payments()
    {
        return $this->hasMany(InstitutionPayment::class);
    }

    public function isPrepaid(): bool
    {
        return $this->billing_type === 'prepaid';
    }

    public function isPostpaid(): bool
    {
        return $this->billing_type !== 'prepaid';
    }

    /**
     * গ্রেস পিরিয়ড শেষ হতে (postpaid) আর কত দিন বাকি — নেগেটিভ মানে
     * ইতিমধ্যে গ্রেস পিরিয়ড পার হয়ে গেছে।
     */
    public function graceDaysLeft(): ?int
    {
        if (! $this->billing_grace_ends_at) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->billing_grace_ends_at, false);
    }

    public static function resolveFromSubdomain(string $host): ?self
    {
        $slug = explode('.', $host)[0] ?? null;

        if (!$slug || in_array($slug, ['www', 'superadmin', 'app', 'panel', 'edution', 'localhost', '127'])) {
            return null;
        }

        return static::where('slug', $slug)->first();
    }
}
