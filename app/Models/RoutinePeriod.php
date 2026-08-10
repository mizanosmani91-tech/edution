<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * RoutinePeriod — একটা ক্লাসের একটা দিনের একটা পিরিয়ডে কোন teacher কোন
 * subject পড়াবে সেটার রেকর্ড। consecutive-period-blocking রুল এই টেবিলের
 * ওপরই কাজ করে (একই teacher, একই দিনে পরপর দুই পিরিয়ড — যদি টগল অন থাকে,
 * আটকানো হবে)।
 */
class RoutinePeriod extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id',
        'class_id',
        'section_id',
        'teacher_id',
        'subject_id',
        'day_of_week',   // 1=রবি ... 7=শনি (institution_settings এর সপ্তাহ কনভেনশন অনুযায়ী)
        'period_number',
        'start_time',
        'end_time',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
