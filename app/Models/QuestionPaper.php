<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * QuestionPaper — শিক্ষক প্রশ্ন লিখবেন (draft), এডমিন রিভিউ করে approve
 * করবেন, তারপর প্রিন্ট করা যাবে। প্রতিটা প্রশ্ন আলাদা QuestionPaperItem
 * হিসেবে থাকে (order_no অনুযায়ী সাজানো)।
 */
class QuestionPaper extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id', 'exam_id', 'class_id', 'subject_id', 'created_by',
        'title', 'duration_text', 'full_marks', 'status',
        'submitted_at', 'approved_at', 'approved_by',
    ];

    protected $casts = [
        'full_marks' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(QuestionPaperItem::class)->orderBy('order_no');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'submitted' => 'রিভিউয়ের অপেক্ষায়',
            'approved' => 'অনুমোদিত',
            default => 'খসড়া',
        };
    }
}
