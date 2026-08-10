<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    protected $fillable = [
        'institution_id',
        'conversation_id',
        'sender_id',
        'body',
        'attachment_path',
        'attachment_type',
    ];

    /**
     * ⚠️ শুধু institution_id ফিল্টার (BelongsToTenant থেকে) যথেষ্ট না এখানে —
     * একই institution এর অন্য কারো ব্যক্তিগত চ্যাট দেখা আটকাতে দ্বিতীয়
     * global scope যোগ করা হলো: conversation এর participant হতে হবে।
     * এটা layer ২ প্রোটেকশন, RLS policy (messages_participant_only) layer ১।
     */
    protected static function booted(): void
    {
        static::addGlobalScope('participant', function (Builder $builder) {
            if (!app()->bound('tenant.current_user_id')) {
                return; // context না থাকলে BelongsToTenant এমনিতেই fail-closed করবে
            }

            $userId = app('tenant.current_user_id');

            $builder->whereHas('conversation.participants', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        });
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        return $this->attachment_path
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->attachment_path)
            : null;
    }
}
