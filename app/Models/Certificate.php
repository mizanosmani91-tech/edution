<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id', 'student_id', 'type', 'certificate_no',
        'issue_date', 'reason', 'remarks', 'issued_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'character' ? 'চারিত্রিক সনদপত্র' : 'ছাড়পত্র (Transfer Certificate)';
    }
}
