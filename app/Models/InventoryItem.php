<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id', 'name', 'category', 'asset_tag', 'quantity_total',
        'quantity_available', 'unit', 'purchase_date', 'purchase_price',
        'condition', 'location', 'remarks',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:2',
    ];

    public function issues()
    {
        return $this->hasMany(InventoryIssue::class, 'item_id');
    }
}
