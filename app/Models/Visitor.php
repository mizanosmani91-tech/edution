<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id', 'name', 'phone', 'purpose', 'meeting_with',
        'id_type', 'id_number', 'check_in', 'check_out', 'remarks', 'recorded_by',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getDurationMinutesAttribute(): ?int
    {
        if (! $this->check_out) {
            return null;
        }

        return $this->check_in->diffInMinutes($this->check_out);
    }
}
