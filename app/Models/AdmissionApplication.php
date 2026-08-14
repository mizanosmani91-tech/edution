<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;

class AdmissionApplication extends Model
{
    use BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id', 'applicant_name', 'guardian_name', 'guardian_phone',
        'date_of_birth', 'gender', 'applying_class_id', 'previous_school',
        'address', 'status', 'test_date', 'test_time', 'test_score',
        'interview_notes', 'converted_student_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'test_date' => 'date',
    ];

    public function applyingClass()
    {
        return $this->belongsTo(SchoolClass::class, 'applying_class_id');
    }

    public function convertedStudent()
    {
        return $this->belongsTo(Student::class, 'converted_student_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'test_scheduled' => 'পরীক্ষা নির্ধারিত',
            'shortlisted' => 'শর্টলিস্টেড',
            'waiting' => 'অপেক্ষমাণ তালিকা',
            'accepted' => 'গৃহীত',
            'rejected' => 'বাতিল',
            default => 'পর্যালোচনাধীন',
        };
    }
}
