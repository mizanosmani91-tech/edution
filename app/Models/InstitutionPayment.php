<?php

namespace App\Models;

use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * InstitutionPayment
 *
 * ⚠️ BelongsToTenant trait ব্যবহার করা হয়নি ইচ্ছাকৃতভাবে — সেই trait
 * fail-closed (context না থাকলে throw করে), কিন্তু superadmin route এ
 * tenant.institution_id বাইন্ড করা থাকে না (EnsureSuperAdmin middleware
 * ইচ্ছাকৃতভাবে সেটা করে না)। তাই এখানে কাস্টম scope: normal tenant route এ
 * institution filter, superadmin route এ filter ছাড়াই (RLS এ যেমন
 * "is_superadmin" bypass আছে, এখানে app-level এ সেই একই যুক্তি)।
 */
class InstitutionPayment extends Model
{
    use HasFactory, UuidPrimaryKey;

    protected $fillable = [
        'institution_id',
        'amount',
        'method',
        'transaction_ref',
        'for_month',
        'purpose', // subscription | wallet_topup
        'status',
        'submitted_by',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant-or-superadmin', function (Builder $builder) {
            if (app()->bound('tenant.institution_id')) {
                $builder->where('institution_id', app('tenant.institution_id'));
                return;
            }

            // tenant.institution_id বাইন্ড করা নেই — superadmin route থেকে
            // এসেছে ধরে নেওয়া হচ্ছে (EnsureSuperAdmin middleware দিয়ে যাচাই
            // হয়ে গেছে আগেই, তাই এখানে আর role চেক করার দরকার নেই)
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
}
