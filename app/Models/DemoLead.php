<?php

namespace App\Models;

use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;

class DemoLead extends Model
{
    use UuidPrimaryKey;

    protected $fillable = [
        'token',
        'name',
        'phone',
        'institution_name',
    ];

    public function accessRequests()
    {
        return $this->hasMany(DemoAccessRequest::class);
    }
}
