<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\UuidPrimaryKey;

/**
 * QuestionPaperItem — প্রশ্নপত্রের একটা প্রশ্ন/অংশ (যেমনঃ "শব্দার্থ লিখ",
 * "বাক্য রচনা কর")। এটা নিজে tenant-বাউন্ড না (parent question_paper
 * এর মাধ্যমেই institution scope হয়ে যায়), তাই BelongsToTenant লাগে না।
 */
class QuestionPaperItem extends Model
{
    use UuidPrimaryKey;

    protected $fillable = [
        'question_paper_id', 'order_no', 'heading', 'marks', 'content',
    ];

    protected $casts = [
        'marks' => 'decimal:2',
    ];

    public function questionPaper()
    {
        return $this->belongsTo(QuestionPaper::class);
    }
}
