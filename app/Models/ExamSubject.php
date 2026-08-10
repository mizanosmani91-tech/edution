<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ExamSubject
 *
 * গুরুত্বপূর্ণ স্কিমা নোট: exam_marks টেবিলে সরাসরি exam_id/subject_id কলাম নেই,
 * এটা exam_subject_id দিয়ে link হয় — অর্থাৎ ExamSubject-ই হলো exam+subject+class
 * এর combination টেবিল, ExamMark এটাকেই foreign key হিসেবে ব্যবহার করে।
 *
 * কলামগুলো placeholder — আসল migration 029 এর schema অনুযায়ী মিলিয়ে
 * ঠিক করে নিতে হবে (নিচে চ্যাট মেসেজে ব্যাখ্যা আছে)।
 */
class ExamSubject extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id',
        'exam_id',
        'subject_id',
        'class_id',
        'teacher_id',
        'full_marks',
        'pass_marks',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function marks()
    {
        return $this->hasMany(ExamMark::class);
    }
}
