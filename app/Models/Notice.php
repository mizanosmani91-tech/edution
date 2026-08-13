<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id',
        'title',
        'body',
        'category',
        'audience',
        'is_pinned',
        'is_urgent',
        'attachment_path',
        'publish_at',
        'expires_at',
        'views',
        'created_by',
    ];

    protected $casts = [
        'audience' => 'array',
        'is_pinned' => 'boolean',
        'is_urgent' => 'boolean',
        'publish_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('publish_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
    }

    public function getAudienceLabelAttribute(): string
    {
        if (empty($this->audience)) {
            return 'সকলের জন্য';
        }

        $map = ['guardian' => 'অভিভাবক', 'teacher' => 'শিক্ষক/স্টাফ', 'student' => 'শিক্ষার্থী'];

        return collect($this->audience)->map(fn ($a) => $map[$a] ?? $a)->implode(', ');
    }
}
