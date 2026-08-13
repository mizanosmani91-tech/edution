<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;

class PayrollRecord extends Model
{
    use BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id', 'teacher_id', 'month', 'year', 'base_salary',
        'house_rent', 'medical_allowance', 'other_allowance', 'deductions',
        'deduction_reason', 'net_pay', 'status', 'paid_date', 'paid_by',
    ];

    protected $casts = [
        'paid_date' => 'date',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function getMonthLabelAttribute(): string
    {
        $months = ['জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'];

        return ($months[$this->month - 1] ?? '') . ' ' . $this->year;
    }
}
