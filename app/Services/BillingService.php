<?php

namespace App\Services;

use App\Models\Institution;
use App\Models\Student;
use App\Models\WalletTransaction;

/**
 * BillingService — postpaid (ছাত্রসংখ্যা-ভিত্তিক টায়ার) ও prepaid
 * (ছাত্র প্রতি ফ্ল্যাট রেট, ব্যালেন্স থেকে কর্তন) দুই মডেলের হিসাব একই
 * জায়গায়। superadmin UI (প্রিভিউ দেখানো) ও দৈনিক বিলিং কমান্ড দুটোই
 * এই সার্ভিস ব্যবহার করে, যাতে হিসাবের সূত্র দুই জায়গায় আলাদা না হয়ে যায়।
 */
class BillingService
{
    // ছাত্রসংখ্যা টায়ার => মাসিক টাকা (postpaid)
    public const POSTPAID_TIERS = [
        200 => 499,
        500 => 999,
        1000 => 1999,
    ];

    public const PREPAID_RATE_PER_STUDENT = 5;

    public const GRACE_DAYS = 15;

    public function activeStudentCount(Institution $institution): int
    {
        return Student::allTenants()->where('institution_id', $institution->id)->count();
    }

    /**
     * নির্দিষ্ট ছাত্রসংখ্যার জন্য postpaid মাসিক ফি — ১০০০ এর বেশি হলে
     * null ফেরত দেয় (এখনো কোনো টায়ার সেট করা হয়নি, এন্টারপ্রাইজ/কাস্টম
     * কোটেশন লাগবে — superadmin ম্যানুয়ালি ধার্য করবে)।
     */
    public function postpaidTierPrice(int $studentCount): ?int
    {
        foreach (self::POSTPAID_TIERS as $upperBound => $price) {
            if ($studentCount <= $upperBound) {
                return $price;
            }
        }

        return null; // "যোগাযোগ করুন" — কাস্টম প্রাইসিং
    }

    public function postpaidDueAmount(Institution $institution): ?int
    {
        return $this->postpaidTierPrice($this->activeStudentCount($institution));
    }

    public function prepaidMonthlyCost(Institution $institution): int
    {
        return $this->activeStudentCount($institution) * self::PREPAID_RATE_PER_STUDENT;
    }

    /**
     * প্রিপেইড ব্যালেন্সে টাকা যোগ করা (টপ-আপ অনুমোদন হলে superadmin থেকে
     * কল হয়) — WalletTransaction লগসহ।
     */
    public function topUp(Institution $institution, float $amount, ?string $note, ?string $byUserId): WalletTransaction
    {
        $institution->prepaid_balance = (float) $institution->prepaid_balance + $amount;
        $institution->billing_suspended = false;
        $institution->save();

        return WalletTransaction::create([
            'institution_id' => $institution->id,
            'type' => 'topup',
            'amount' => $amount,
            'balance_after' => $institution->prepaid_balance,
            'note' => $note,
            'created_by' => $byUserId,
        ]);
    }

    /**
     * চলতি মাসের প্রিপেইড কর্তন — মাসে একবারই কাটে (billing_last_charged_month
     * দিয়ে idempotent), ব্যালেন্স ঋণাত্মক হতেও পারে (তখন suspend হবে,
     * নিচে processDaily দেখুন)।
     */
    public function chargePrepaidIfDue(Institution $institution): void
    {
        $currentMonth = now()->format('Y-m');

        if ($institution->billing_last_charged_month === $currentMonth) {
            return; // এই মাসে ইতিমধ্যে কাটা হয়ে গেছে
        }

        $cost = $this->prepaidMonthlyCost($institution);

        $institution->prepaid_balance = (float) $institution->prepaid_balance - $cost;
        $institution->billing_last_charged_month = $currentMonth;
        $institution->save();

        WalletTransaction::create([
            'institution_id' => $institution->id,
            'type' => 'deduction',
            'amount' => -$cost,
            'balance_after' => $institution->prepaid_balance,
            'note' => "মাসিক কর্তন ({$currentMonth}) — {$this->activeStudentCount($institution)} জন ছাত্র × ৫ টাকা",
            'created_by' => null,
        ]);
    }
}
