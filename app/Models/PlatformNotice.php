<?php

namespace App\Models;

use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformNotice extends Model
{
    use HasFactory, UuidPrimaryKey;

    protected $fillable = [
        'title', 'body', 'notice_type', 'audience', 'reached_count', 'sent_by',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
