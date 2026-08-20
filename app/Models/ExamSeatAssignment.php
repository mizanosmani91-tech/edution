<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ExamSeatAssignment — একজন ছাত্র একটা পরীক্ষায় কোন রুমে, কোন সিট
 * নাম্বারে বসবে। একজন ছাত্র একটা exam-এ একটাই সিট পাবে (unique constraint)।
 */
class ExamSeatAssignment extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id',
        'exam_id',
        'exam_seat_plan_id',
        'student_id',
        'seat_no',
    ];

    protected $casts = [
        'seat_no' => 'integer',
    ];

    public function seatPlan()
    {
        return $this->belongsTo(ExamSeatPlan::class, 'exam_seat_plan_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
