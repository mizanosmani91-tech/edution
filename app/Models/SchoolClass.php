<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * SchoolClass — টেবিলের নাম `classes`, মডেলের নাম SchoolClass
 * (কারণ `Class` PHP-এর reserved word, মডেল নাম হিসেবে ব্যবহার করা যায় না)।
 *
 * department_id nullable — has_departments বন্ধ থাকা institution-এ এটা
 * সবসময় null থাকবে, ক্লাস তখন সরাসরি institution-এর অধীনে গণ্য হবে।
 */
class SchoolClass extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $table = 'classes';

    protected $fillable = [
        'institution_id',
        'department_id', // nullable — has_departments off হলে সবসময় null
        'name',
        'display_order',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function sections()
    {
        return $this->hasMany(Section::class, 'class_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    /**
     * UI-তে দেখানোর জন্য পূর্ণ label — বিভাগ থাকলে "বিজ্ঞান - ৯ম শ্রেণি",
     * না থাকলে শুধু "৯ম শ্রেণি"। সব জায়গায় (list, dropdown, marksheet)
     * এই একটা accessor ব্যবহার করলে বিভাগ অন/অফ যেকোনো অবস্থায় সঠিক দেখাবে।
     */
    public function getFullLabelAttribute(): string
    {
        return $this->department
            ? "{$this->department->name} - {$this->name}"
            : $this->name;
    }
}
