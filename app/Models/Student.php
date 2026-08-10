<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Student — রেফারেন্স মডেল দেখাচ্ছে কীভাবে একটা নতুন tenant-বাউন্ড মডেল বানাতে হয়।
 * ভবিষ্যতে Teacher, Exam, FeeCollection ইত্যাদি সব মডেলে এই একই প্যাটার্ন কপি করুন।
 */
class Student extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey; // 👈 এই লাইনটাই আসল কাজ করে

    protected $fillable = [
        'institution_id', // creating() hook এ override হয়, তাও fillable এ রাখা fine
        'name',
        'student_id_no',
        'class_id',
        'section_id',
        'guardian_phone',
        'photo_path',
        'date_of_birth',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function guardians()
    {
        return $this->belongsToMany(User::class, 'guardian_student', 'student_id', 'guardian_id')
            ->withPivot('relationship');
    }
}
