<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id', 'quiz_id', 'question_bank_item_id', 'marks', 'order_no',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function questionBankItem()
    {
        return $this->belongsTo(QuestionBankItem::class);
    }
}
