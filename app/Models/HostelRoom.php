<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostelRoom extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = ['institution_id', 'room_no', 'room_type', 'capacity', 'monthly_fee'];

    protected $casts = ['monthly_fee' => 'decimal:2'];

    public function residents()
    {
        return $this->hasMany(StudentHostel::class, 'room_id');
    }
}
