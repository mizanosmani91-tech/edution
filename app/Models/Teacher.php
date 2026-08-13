<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id',
        'name',
        'name_en',
        'gender',
        'nid',
        'address',
        'emergency_contact',
        'education',
        'passing_institution',
        'teacher_id_no',
        'phone',
        'email',
        'designation',
        'employee_type',
        'experience_years',
        'previous_workplace',
        'joining_date',
        'status',
        'photo_path',
        'base_salary',
        'house_rent',
        'medical_allowance',
        'bank_name',
        'bank_branch',
        'bank_account',
        'mobile_banking',
        'subjects_taught',
        'assigned_classes',
    ];

    protected $casts = [
        'subjects_taught' => 'array',
        'assigned_classes' => 'array',
        'joining_date' => 'date',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    // exam module এ লাগবে বলে আগেই রিলেশন রেখে দিলাম
    public function examSubjects()
    {
        return $this->hasMany(ExamSubject::class);
    }
}
