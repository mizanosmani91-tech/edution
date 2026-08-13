<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id',
        'applicant_type', // student / teacher
        'student_id',
        'teacher_id',
        'leave_type',     // casual / sick / personal / maternity_paternity / family / other
        'requested_by',
        'date_from',
        'date_to',
        'reason',
        'attachment_path',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function getApplicantNameAttribute(): string
    {
        return $this->applicant_type === 'teacher'
            ? ($this->teacher->name ?? '—')
            : ($this->student->name ?? '—');
    }

    public function getTotalDaysAttribute(): int
    {
        return $this->date_from->diffInDays($this->date_to) + 1;
    }
}
