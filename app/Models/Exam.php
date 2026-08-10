<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id',
        'name',
        'exam_type',      // e.g. term, final, class_test
        'academic_year',
        'start_date',
        'end_date',
        'is_published',   // ফলাফল published হয়েছে কিনা
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_published' => 'boolean',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function examSubjects()
    {
        return $this->hasMany(ExamSubject::class);
    }

    public function weightingsAsTarget()
    {
        return $this->hasMany(ExamResultWeighting::class, 'target_exam_id');
    }

    public function weightingsAsSource()
    {
        return $this->hasMany(ExamResultWeighting::class, 'source_exam_id');
    }
}
