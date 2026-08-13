<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransportRoute extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id', 'route_name', 'vehicle_no', 'driver_name', 'driver_phone', 'capacity', 'monthly_fee',
    ];

    protected $casts = ['monthly_fee' => 'decimal:2'];

    public function assignments()
    {
        return $this->hasMany(StudentTransport::class, 'route_id');
    }
}
