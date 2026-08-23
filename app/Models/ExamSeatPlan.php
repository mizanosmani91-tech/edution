<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ExamSeatPlan — একটা পরীক্ষার একটা রুম/হল (নাম + ধারণক্ষমতা)।
 * প্রতিটা রুমের সাথে ExamSeatAssignment এর মাধ্যমে ছাত্র বসানো হয়।
 */
class ExamSeatPlan extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id',
        'exam_id',
        'room_name',
        'capacity',
        'assigned_teacher_id',
        'display_order',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'display_order' => 'integer',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function assignedTeacher()
    {
        return $this->belongsTo(Teacher::class, 'assigned_teacher_id');
    }

    public function assignments()
    {
        return $this->hasMany(ExamSeatAssignment::class)->orderBy('seat_no');
    }
}
