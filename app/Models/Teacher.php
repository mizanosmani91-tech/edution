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
        'teacher_id_no',
        'phone',
        'email',
        'designation',
        'joining_date',
        'photo_path',
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
