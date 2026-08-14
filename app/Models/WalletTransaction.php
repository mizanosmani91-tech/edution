<?php

namespace App\Models;

use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * WalletTransaction — প্রিপেইড ব্যালেন্সের লেজার (টপ-আপ/মাসিক কর্তন/সমন্বয়)।
 * প্রতিটা এন্ট্রিতে balance_after স্ন্যাপশট রাখা হয়, যাতে ইতিহাস দেখতে
 * প্রতিবার সব যোগ-বিয়োগ করে হিসাব করা না লাগে।
 *
 * ⚠️ InstitutionPayment-এর মতোই BelongsToTenant না — superadmin থেকে
 * টপ-আপ অনুমোদন/দৈনিক বিলিং কমান্ড থেকে (কোনো tenant context ছাড়াই)
 * এন্ট্রি তৈরি হয়, তাই কাস্টম tenant-or-superadmin scope।
 */
class WalletTransaction extends Model
{
    use HasFactory, UuidPrimaryKey;

    protected $fillable = [
        'institution_id',
        'type', // topup | deduction | adjustment
        'amount',
        'balance_after',
        'note',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant-or-superadmin', function (Builder $builder) {
            if (app()->bound('tenant.institution_id')) {
                $builder->where('institution_id', app('tenant.institution_id'));
                return;
            }
        });

        static::creating(function (Model $model) {
            if (empty($model->institution_id) && app()->bound('tenant.institution_id')) {
                $model->institution_id = app('tenant.institution_id');
            }
        });
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
