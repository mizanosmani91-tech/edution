<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAnswer extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id', 'quiz_attempt_id', 'quiz_question_id', 'selected_answer',
        'is_correct', 'marks_awarded',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'marks_awarded' => 'decimal:2',
    ];

    public function attempt()
    {
        return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id');
    }

    public function quizQuestion()
    {
        return $this->belongsTo(QuizQuestion::class);
    }
}
