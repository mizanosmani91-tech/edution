<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ExamMark
 *
 * ⚠️ এই মডেলে exam_id/subject_id সরাসরি নেই — শুধু exam_subject_id।
 * exam বা subject দিয়ে filter করতে হলে exam_subject relation দিয়ে যেতে হবে:
 *   ExamMark::whereHas('examSubject', fn($q) => $q->where('exam_id', $examId))
 *
 * raw marks এখানে থাকে — effective/weighted marks আসল ৪টা PostgreSQL
 * function থেকে আসে (get_effective_exam_marks ইত্যাদি), সেটা এই মডেলের
 * attribute না, DB function call এর মাধ্যমে আনতে হবে।
 */
class ExamMark extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id',
        'exam_subject_id', // 👈 exam_id/subject_id না, এটাই একমাত্র লিংক
        'student_id',
        'marks_obtained',
        'is_absent',
        'entered_by', // teacher/user id যিনি মার্কস এন্ট্রি করেছেন
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
        'is_absent' => 'boolean',
    ];

    public function examSubject()
    {
        return $this->belongsTo(ExamSubject::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
