<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;

class MonthlyHonor extends Model
{
    use BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id', 'category', 'month', 'student_id', 'teacher_id', 'score', 'metrics',
    ];

    protected $casts = [
        'metrics' => 'array',
        'score' => 'float',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
