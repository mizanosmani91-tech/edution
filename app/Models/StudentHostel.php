<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentHostel extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = ['institution_id', 'student_id', 'room_id', 'check_in_date'];

    protected $casts = ['check_in_date' => 'date'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function room()
    {
        return $this->belongsTo(HostelRoom::class, 'room_id');
    }
}
