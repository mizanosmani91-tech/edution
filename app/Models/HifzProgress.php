<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HifzProgress extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id', 'student_id', 'teacher_id', 'date',
        'sabak_para', 'sabak_range', 'sabak_quality',
        'sabqi_range', 'sabqi_quality',
        'manzil_range', 'manzil_quality',
        'remarks', 'recorded_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
