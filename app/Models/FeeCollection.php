<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * FeeCollection — একটা student এর একটা নির্দিষ্ট মাস/টার্মের ফি সংক্রান্ত রেকর্ড।
 * Mizanur এর pricing model অনুযায়ী payment_method এ bKash/Nagad/bank_transfer/cash
 * থাকতে পারে (manual collection, memory অনুযায়ী)।
 */
class FeeCollection extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id',
        'student_id',
        'fee_type',        // admission / monthly / exam / other
        'amount_due',
        'fine_amount',
        'fine_reason',
        'amount_paid',
        'payment_method',  // bkash / nagad / bank_transfer / cash
        'transaction_ref', // bKash/Nagad TrxID বা bank reference
        'due_month',       // যেমন: 2026-08
        'paid_at',
        'collected_by',    // user id যিনি এন্ট্রি করেছেন
        'status',          // paid / partial / due / overdue
    ];

    protected $casts = [
        'amount_due' => 'decimal:2',
        'fine_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function getDueAmountAttribute(): float
    {
        return (float) $this->amount_due + (float) $this->fine_amount - (float) $this->amount_paid;
    }
}
