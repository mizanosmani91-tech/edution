<?php

namespace App\Models;

use App\Models\Concerns\UuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;

class DemoAccessRequest extends Model
{
    use UuidPrimaryKey;

    protected $fillable = [
        'demo_lead_id',
        'role', // admin / teacher / guardian
        'status', // pending / approved / rejected
        'unlocked_until',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'unlocked_until' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(DemoLead::class, 'demo_lead_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isCurrentlyUnlocked(): bool
    {
        return $this->status === 'approved'
            && $this->unlocked_until
            && $this->unlocked_until->isFuture();
    }

    /**
     * এই role টা এই মুহূর্তে global ভাবে আনলক আছে কিনা — ফিক্সড/শেয়ার্ড
     * ডেমো ক্রেডেনশিয়াল বলেই কোন লিড আনলক করেছে সেটা গুরুত্বপূর্ণ না,
     * শুধু "কেউ একজনকে কল দিয়ে আনলক করে দেওয়া হয়েছে কিনা" — সেটাই গেট।
     */
    public static function isRoleUnlocked(string $role): bool
    {
        return static::where('role', $role)
            ->where('status', 'approved')
            ->where('unlocked_until', '>=', now())
            ->exists();
    }
}
