<?php

namespace App\Models;

use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * AppNotification — BelongsToTenant না, কারণ শুধু institution filter যথেষ্ট
 * না — নিজের notification ছাড়া অন্য কারোটা দেখা উচিত না, তাই institution_id
 * + user_id দুইটাই global scope এ চেক করা হচ্ছে (RLS এও একই দুই-শর্ত পলিসি)।
 */
class AppNotification extends Model
{
    use HasFactory, UuidPrimaryKey;

    protected $fillable = [
        'institution_id',
        'user_id',
        'type',
        'title',
        'body',
        'link',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('institution', function (Builder $builder) {
            if (app()->bound('tenant.institution_id')) {
                $builder->where('institution_id', app('tenant.institution_id'));
            }
        });

        // ⚠️ "own only" স্কোপ — শুধু auth()->id() এর notification দেখাবে।
        // System/queue job থেকে (auth() না থাকা অবস্থায়) create() করার সময়
        // এই স্কোপ প্রযোজ্য না (create এ scope লাগে না, শুধু query তে)।
        static::addGlobalScope('owner', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where('user_id', auth()->id());
            }
        });

        static::creating(function (Model $model) {
            if (empty($model->institution_id) && app()->bound('tenant.institution_id')) {
                $model->institution_id = app('tenant.institution_id');
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
