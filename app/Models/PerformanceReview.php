<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;

class PerformanceReview extends Model
{
    use BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id', 'teacher_id', 'review_period', 'review_date',
        'teaching_quality', 'punctuality', 'discipline', 'cooperation',
        'strengths', 'improvement_areas', 'reviewed_by',
    ];

    protected $casts = [
        'review_date' => 'date',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function getOverallScoreAttribute(): float
    {
        return round(($this->teaching_quality + $this->punctuality + $this->discipline + $this->cooperation) / 4, 1);
    }
}
