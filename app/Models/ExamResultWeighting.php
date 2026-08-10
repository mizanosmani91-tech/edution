<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ExamResultWeighting — আসল 029 migration এর exam_result_weightings টেবিলের
 * সাথে হুবহু মেলানো fillable। কলাম অর্থ:
 *
 *   contribution_type = 'scale'      → source exam(গুলো)-এর গড় শতাংশ,
 *                                       converted_max_marks দিয়ে স্কেল করে
 *                                       target exam-এর মার্কের সাথে যোগ হয়
 *   contribution_type = 'percentage' → source exam সরাসরি weight_percentage
 *                                       হিসেবে final মার্কে যোগ হয়
 *   group_key                        → একাধিক 'scale' rule একই group এ থাকলে
 *                                       তাদের গড় নেওয়া হয় (এক ধাপে না, group-wise)
 *   require_source_pass              → true হলে source exam এ fail করলে এই
 *                                       weighting থেকে "pass" ধরা হবে না
 */
class ExamResultWeighting extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id',
        'target_exam_id',
        'source_exam_id',
        'class_id',
        'subject_id',
        'contribution_type', // 'scale' | 'percentage'
        'group_key',
        'converted_max_marks',
        'weight_percentage',
        'require_source_pass',
    ];

    protected $casts = [
        'require_source_pass' => 'boolean',
        'converted_max_marks' => 'decimal:2',
        'weight_percentage' => 'decimal:2',
    ];

    public function targetExam()
    {
        return $this->belongsTo(Exam::class, 'target_exam_id');
    }

    public function sourceExam()
    {
        return $this->belongsTo(Exam::class, 'source_exam_id');
    }
}
