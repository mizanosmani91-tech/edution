<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Auditable
 *
 * সংবেদনশীল মডেলে (ফি, নম্বর, বেতন) এই trait লাগালে তৈরি/পরিবর্তন/মোছা
 * অটোমেটিক audit_logs-এ লেখা হয়।
 *
 * ⚠️ শুধু Eloquent মডেল ইভেন্টে কাজ করে। `Model::where(...)->update([...])`,
 *    `->delete()` বা `DB::table()` ইভেন্ট ছোঁড়ে না, তাই সেগুলো লগে আসবে না —
 *    অডিট দরকার এমন জায়গায় মডেল লোড করে save()/delete() করুন।
 *
 * ঐচ্ছিক:
 *   protected array $auditExclude = ['field'];   // লগে যাবে না এমন কলাম
 *   public function auditLabel(): string          // লগ তালিকায় দেখানোর নাম
 */
trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(fn (Model $m) => $m->writeAudit('created', [], $m->getAttributes()));

        static::updated(function (Model $m) {
            $changes = $m->getChanges();
            $old = array_intersect_key($m->getOriginal(), $changes);
            $m->writeAudit('updated', $old, $changes);
        });

        static::deleted(fn (Model $m) => $m->writeAudit('deleted', $m->getAttributes(), []));
    }

    /** @param array<string,mixed> $old @param array<string,mixed> $new */
    protected function writeAudit(string $event, array $old, array $new): void
    {
        $skip = array_merge(
            ['id', 'institution_id', 'created_at', 'updated_at'],
            $this->auditExclude ?? []
        );

        $old = array_diff_key($old, array_flip($skip));
        $new = array_diff_key($new, array_flip($skip));

        // শুধু টাইমস্ট্যাম্প বদলালে লগ করার কিছু নেই
        if ($event === 'updated' && $new === []) {
            return;
        }

        // টেন্যান্ট কনটেক্সট ছাড়া (যেমন কনসোল/সিডার) কোন প্রতিষ্ঠানের লগ তা জানা যায় না
        if (! app()->bound('tenant.institution_id')) {
            return;
        }

        try {
            $user = auth()->user();

            AuditLog::create([
                'user_id' => $user?->id,
                'user_name' => $user?->name,
                'user_role' => $user?->role,
                'event' => $event,
                'auditable_type' => static::class,
                'auditable_id' => $this->getKey(),
                'auditable_label' => method_exists($this, 'auditLabel') ? $this->auditLabel() : null,
                'old_values' => $old ?: null,
                'new_values' => $new ?: null,
                'ip_address' => app()->runningInConsole() ? null : request()->ip(),
            ]);
        } catch (\Throwable $e) {
            // অডিট লেখা ব্যর্থ হলে আসল কাজ (ফি নেওয়া, নম্বর দেওয়া) আটকাবে না, তবে লগে থাকবে
            Log::error('অডিট লগ লেখা যায়নি', [
                'model' => static::class,
                'id' => $this->getKey(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
