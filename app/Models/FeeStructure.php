<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey, Auditable;

    protected $fillable = [
        'institution_id',
        'class_id',
        'fee_type',
        'amount',
        'frequency',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function auditLabel(): string
    {
        return trim(($this->fee_type ?? 'ফি কাঠামো') . ' (' . ($this->frequency ?? '') . ')');
    }
}
