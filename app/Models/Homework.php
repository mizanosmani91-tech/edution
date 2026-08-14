<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;

class Homework extends Model
{
    use BelongsToTenant, UuidPrimaryKey;

    // ⚠️ Eloquent এর ডিফল্ট প্লুরাল অনুমান 'homework' (ইংরেজিতে uncountable noun),
    // কিন্তু মাইগ্রেশনে টেবিলের নাম 'homeworks' — তাই এক্সপ্লিসিট override বাধ্যতামূলক।
    protected $table = 'homeworks';

    protected $fillable = [
        'institution_id', 'title', 'description', 'class_id', 'section_id',
        'subject_id', 'teacher_id', 'assigned_date', 'due_date', 'attachment_path',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'due_date' => 'date',
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
}
