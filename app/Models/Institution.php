<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    use HasFactory, UuidPrimaryKey;

    protected $fillable = [
        'name',
        'slug',        // subdomain slug, e.g. "green-valley-school"
        'status',      // trial / active / suspended
        'trial_ends_at',
        'phone',
        'address',
        'logo_path',
        'registration_email',
        'institution_type',
        'plan',
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
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'enabled_modules' => 'array',
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
    public function hasDepartments(): bool
    {
        return (bool) $this->settings?->has_departments;
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

    public static function resolveFromSubdomain(string $host): ?self
    {
        $slug = explode('.', $host)[0] ?? null;

        if (!$slug || in_array($slug, ['www', 'superadmin', 'app', 'panel', 'edution', 'localhost', '127'])) {
            return null;
        }

        return static::where('slug', $slug)->first();
    }
}
