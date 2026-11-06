<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LogicException;

class AuditLog extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    public const UPDATED_AT = null;

    /** যেসব মডেল অডিট হয় ও তাদের বাংলা নাম */
    public const TYPES = [
        FeeCollection::class => 'ফি আদায়',
        FeeStructure::class => 'ফি কাঠামো',
        ExamMark::class => 'পরীক্ষার নম্বর',
        PayrollRecord::class => 'বেতন',
    ];

    public const EVENTS = [
        'created' => 'তৈরি',
        'updated' => 'পরিবর্তন',
        'deleted' => 'মুছে ফেলা',
    ];

    protected $fillable = [
        'institution_id',
        'user_id',
        'user_name',
        'user_role',
        'event',
        'auditable_type',
        'auditable_id',
        'auditable_label',
        'old_values',
        'new_values',
        'ip_address',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // লগ কখনো বদলানো বা মোছা যাবে না
        static::updating(fn () => throw new LogicException('অডিট লগ পরিবর্তন করা যায় না।'));
        static::deleting(fn () => throw new LogicException('অডিট লগ মোছা যায় না।'));
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->auditable_type] ?? class_basename($this->auditable_type);
    }

    public function getEventLabelAttribute(): string
    {
        return self::EVENTS[$this->event] ?? $this->event;
    }
}
