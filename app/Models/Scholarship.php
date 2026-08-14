<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;

class Scholarship extends Model
{
    use BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id', 'student_id', 'type', 'discount_mode', 'discount_value',
        'reason', 'status', 'valid_from', 'valid_to', 'approved_by',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'waiver' => 'মওকুফ',
            'discount' => 'ছাড়',
            default => 'বৃত্তি',
        };
    }
}
