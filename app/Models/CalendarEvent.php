<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\UuidPrimaryKey;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    use HasFactory, BelongsToTenant, UuidPrimaryKey;

    public const TYPES = [
        'holiday' => 'ছুটি',
        'exam' => 'পরীক্ষা',
        'event' => 'অনুষ্ঠান',
        'meeting' => 'মিটিং',
        'other' => 'অন্যান্য',
    ];

    protected $fillable = [
        'institution_id',
        'title',
        'type',
        'start_date',
        'end_date',
        'is_off_day',
        'audience',
        'description',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_off_day' => 'boolean',
        'audience' => 'array',
    ];

    /** দেওয়া সময়সীমার সাথে যেসব ইভেন্ট একটুও মেলে (স্টার্ট ও এন্ড দুটোই ধরে)। */
    public function scopeOverlapping(Builder $query, CarbonInterface|string $from, CarbonInterface|string $to): Builder
    {
        $from = $from instanceof CarbonInterface ? $from->toDateString() : $from;
        $to = $to instanceof CarbonInterface ? $to->toDateString() : $to;

        return $query->whereDate('start_date', '<=', $to)->whereDate('end_date', '>=', $from);
    }

    public function scopeOffDays(Builder $query): Builder
    {
        return $query->where('is_off_day', true);
    }

    /** এই তারিখে প্রতিষ্ঠান বন্ধ কিনা (বর্তমান টেন্যান্টের জন্য)। */
    public static function isOffDay(CarbonInterface|string $date): bool
    {
        return static::query()->offDays()->overlapping($date, $date)->exists();
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? self::TYPES['other'];
    }

    public function getAudienceLabelAttribute(): string
    {
        if (empty($this->audience)) {
            return 'সকলের জন্য';
        }

        $map = ['guardian' => 'অভিভাবক', 'teacher' => 'শিক্ষক/স্টাফ', 'student' => 'শিক্ষার্থী'];

        return collect($this->audience)->map(fn ($a) => $map[$a] ?? $a)->implode(', ');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
