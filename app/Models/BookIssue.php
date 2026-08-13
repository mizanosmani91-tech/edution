<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookIssue extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id', 'book_id', 'student_id', 'teacher_id',
        'issued_at', 'due_date', 'returned_at', 'fine_amount', 'status',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'due_date' => 'date',
        'returned_at' => 'date',
        'fine_amount' => 'decimal:2',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function getBorrowerNameAttribute(): string
    {
        return $this->student->name ?? $this->teacher->name ?? '—';
    }
}
