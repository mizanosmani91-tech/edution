<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryIssue extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id', 'item_id', 'quantity', 'issued_to', 'issued_by',
        'issued_at', 'expected_return_at', 'returned_at', 'status', 'remarks',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'expected_return_at' => 'date',
        'returned_at' => 'date',
    ];

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
