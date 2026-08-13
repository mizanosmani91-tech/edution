<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;

class StudentHealthRecord extends Model
{
    use BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id', 'student_id', 'height_cm', 'weight_kg', 'blood_group',
        'allergies', 'chronic_conditions', 'emergency_contact_name',
        'emergency_contact_phone', 'last_checkup_date', 'notes',
    ];

    protected $casts = [
        'last_checkup_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
