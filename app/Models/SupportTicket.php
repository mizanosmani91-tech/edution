<?php

namespace App\Models;

use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * SupportTicket
 *
 * ⚠️ InstitutionPayment-এর মতোই — BelongsToTenant (fail-closed) ব্যবহার
 * করা হয়নি, কারণ superadmin route থেকে সব প্রতিষ্ঠানের টিকেট একসাথে
 * দেখতে হয়। tenant route এ normal filtering, superadmin route এ filter-less।
 */
class SupportTicket extends Model
{
    use HasFactory, UuidPrimaryKey;

    protected $fillable = [
        'institution_id', 'created_by', 'subject', 'priority', 'status',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('tenant-or-superadmin', function (Builder $builder) {
            if (app()->bound('tenant.institution_id')) {
                $builder->where('institution_id', app('tenant.institution_id'));
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

    public function messages()
    {
        return $this->hasMany(SupportTicketMessage::class)->orderBy('created_at');
    }
}
