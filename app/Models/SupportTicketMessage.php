<?php

namespace App\Models;

use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicketMessage extends Model
{
    use HasFactory, UuidPrimaryKey;

    protected $fillable = [
        'support_ticket_id', 'sender_type', 'sender_name', 'body',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }
}
