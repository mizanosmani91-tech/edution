<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Attendance — একটা student এর একটা নির্দিষ্ট তারিখের উপস্থিতি রেকর্ড।
 * marked_by = কোন teacher/user এন্ট্রি করেছে (audit trail এর জন্য জরুরি,
 * fine/leave-request feature এ পরে কাজে লাগবে)।
 */
class Attendance extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id',
        'student_id',
        'class_id',
        'section_id',
        'date',
        'status',      // present / absent / late / leave
        'remarks',
        'marked_by',   // user id (teacher)
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
