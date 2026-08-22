<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id', 'title', 'class_id', 'subject_id', 'created_by',
        'duration_minutes', 'starts_at', 'ends_at', 'shuffle_questions', 'is_published',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'shuffle_questions' => 'boolean',
        'is_published' => 'boolean',
    ];

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

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('order_no');
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function getTotalMarksAttribute(): int
    {
        return (int) $this->questions()->sum('marks');
    }

    /**
     * কুইজ এখন শিক্ষার্থীর জন্য খোলা আছে কিনা — প্রকাশিত + সময়সীমার মধ্যে
     */
    public function isOpenNow(): bool
    {
        if (! $this->is_published) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }

        return true;
    }
}
