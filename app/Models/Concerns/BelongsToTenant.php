<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use RuntimeException;

/**
 * BelongsToTenant
 *
 * প্রতিটা tenant-বাউন্ড মডেলে (Student, Teacher, Exam, ExamMark, FeeCollection...)
 * এই trait লাগান। এটা লেয়ার ২ প্রোটেকশন — RLS (লেয়ার ১) fail করলেও এটা
 * institution_id ফিল্টার এনফোর্স করে।
 *
 * ব্যবহার:
 *   class Student extends Model
 *   {
 *       use BelongsToTenant;
 *   }
 *
 * ⚠️ যদি কোনো query-তে ইচ্ছাকৃতভাবে সব institution দেখা লাগে (superadmin
 * প্যানেলে), explicitly লিখতে হবে:
 *   Student::withoutGlobalScope('tenant')->get();
 * — এটা যেন accidentally না হয়, তাই এই bypass শুধুমাত্র superadmin-only
 * controller-এ ব্যবহার করা উচিত, কখনো সাধারণ tenant route-এ না।
 */
trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        // সব query-তে অটোমেটিক institution_id ফিল্টার
        static::addGlobalScope('tenant', function (Builder $builder) {
            $institutionId = app()->bound('tenant.institution_id')
                ? app('tenant.institution_id')
                : null;

            if ($institutionId === null) {
                // Fail-closed: tenant context না থাকলে query আটকে দাও, খোলা রেখো না।
                // এটা ইচ্ছাকৃতভাবে কড়া — "context নাই তো সব দেখাও" এটা কখনোই
                // ডিফল্ট বিহেভিয়ার হওয়া উচিত না।
                throw new RuntimeException(
                    'Tenant context সেট নেই — SetTenantContext middleware রান হয়েছে কিনা '
                    . 'চেক করুন, অথবা এটা ইচ্ছাকৃতভাবে withoutGlobalScope("tenant") '
                    . 'ব্যবহার করা superadmin route কিনা নিশ্চিত করুন।'
                );
            }

            $builder->where($builder->getModel()->getTable() . '.institution_id', $institutionId);
        });

        // Create/save করার সময়ও অটোমেটিক institution_id বসিয়ে দেয়,
        // যাতে ডেভেলপার প্রতিবার ম্যানুয়ালি না লিখলেও ভুল institution_id বসে না যায়।
        static::creating(function (Model $model) {
            if (empty($model->institution_id) && app()->bound('tenant.institution_id')) {
                $model->institution_id = app('tenant.institution_id');
            }
        });
    }

    /**
     * ইচ্ছাকৃতভাবে সব-institution query দরকার হলে (শুধু superadmin কনটেক্সটে)
     * এই স্কোপটা explicitly বাদ দেওয়ার হেল্পার — যাতে কোথায় bypass হচ্ছে
     * কোডবেসে গ্রেপ করে সহজে খুঁজে বের করা যায়।
     */
    public static function allTenants(): Builder
    {
        return static::withoutGlobalScope('tenant');
    }
}
