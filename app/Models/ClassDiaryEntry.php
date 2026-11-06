<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassDiaryEntry extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id',
        'class_id',
        'section_id',
        'subject_id',
        'teacher_id',
        'entry_date',
        'topic',
        'classwork',
        'homework',
        'remarks',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /** এই শিক্ষার্থীর ক্লাস/সেকশনে প্রযোজ্য ডায়েরি (সেকশন ফাঁকা মানে পুরো শ্রেণি)। */
    public function scopeForStudent(Builder $query, Student $student): Builder
    {
        return $query->where('class_id', $student->class_id)
            ->where(function ($q) use ($student) {
                $q->whereNull('section_id')->orWhere('section_id', $student->section_id);
            });
    }
}
