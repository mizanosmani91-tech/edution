<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Services\BillingService;
use App\Services\NotificationService;
use Illuminate\Console\Command;

/**
 * ProcessBilling — দৈনিক cron দিয়ে চালানোর জন্য (routes/console.php বা
 * সার্ভার crontab এ php artisan schedule:run প্রতি মিনিটে চালু থাকতে হবে)।
 *
 * ⚠️ idempotent করে লেখা হয়েছে — দিনে একাধিকবার চললেও সমস্যা নেই:
 * - prepaid: মাসে একবারই কাটে (billing_last_charged_month চেক)
 * - postpaid: due_at/grace_ends_at একবার সেট হলে আর ওভাররাইট হয় না, যতক্ষণ
 *   না নতুন মাস শুরু হয় বা admin পেমেন্ট অনুমোদন করে (তখন FeeCollection-এর
 *   মতো superadmin approvePayment() থেকে due_at পরের মাসে রিসেট হবে)
 */
class ProcessBilling extends Command
{
    protected $signature = 'edution:process-billing';
    protected $description = 'প্রতিদিন postpaid বকেয়া/গ্রেস পিরিয়ড চেক ও prepaid মাসিক কর্তন করে (cron)';

    public function handle(BillingService $billing, NotificationService $notifications): int
    {
        $today = now()->startOfDay();
        $currentMonth = $today->format('Y-m');
        $processed = 0;
        $suspended = 0;

        $institutions = Institution::query()
            ->whereNotIn('status', ['trial', 'pending', 'rejected'])
            ->get();

        foreach ($institutions as $institution) {
            if ($institution->isPrepaid()) {
                $this->handlePrepaid($institution, $billing, $notifications, $currentMonth);
            } else {
                $this->handlePostpaid($institution, $billing, $notifications, $today, $currentMonth);
            }

            $processed++;
            if ($institution->billing_suspended) {
                $suspended++;
            }
        }

        $this->info("{$processed} টা প্রতিষ্ঠান প্রসেস হলো, {$suspended} টা বিলিং-এর কারণে সাসপেন্ড আছে।");

        return self::SUCCESS;
    }

    private function handlePrepaid(Institution $institution, BillingService $billing, NotificationService $notifications, string $currentMonth): void
    {
        $wasCharged = $institution->billing_last_charged_month === $currentMonth;

        if (! $wasCharged) {
            $billing->chargePrepaidIfDue($institution);
        }

        $balance = (float) $institution->prepaid_balance;
        $monthlyCost = $billing->prepaidMonthlyCost($institution);

        if ($balance < 0) {
            if (! $institution->billing_suspended) {
                $institution->update(['billing_suspended' => true, 'status' => 'suspended']);
                $notifications->billingAlert(
                    $institution,
                    'billing_suspended',
                    'ব্যালেন্স শেষ — অ্যাক্সেস সাময়িকভাবে বন্ধ',
                    'আপনার প্রিপেইড ব্যালেন্স ঋণাত্মক হয়ে গেছে। টপ-আপ করে আবার চালু করুন।'
                );
            }
        } elseif ($balance < $monthlyCost && ! $wasCharged) {
            // মাত্রই কাটার পর ব্যালেন্স কম — পরের মাসের জন্য যথেষ্ট না
            $notifications->billingAlert(
                $institution,
                'billing_low_balance',
                'প্রিপেইড ব্যালেন্স কম',
                "বর্তমান ব্যালেন্স ৳{$balance}, পরের মাসের আনুমানিক খরচ ৳{$monthlyCost}। এখনই টপ-আপ করুন।"
            );
        }
    }

    private function handlePostpaid(Institution $institution, BillingService $billing, NotificationService $notifications, \Illuminate\Support\Carbon $today, string $currentMonth): void
    {
        // নতুন মাস শুরু হলে due_at রিসেট (আগের মাসে suspend/grace থাকলেও নতুন সাইকেল শুরু)
        if (! $institution->billing_due_at || $institution->billing_due_at->format('Y-m') !== $currentMonth) {
            $institution->update([
                'billing_due_at' => $today->copy()->startOfMonth()->toDateString(),
                'billing_grace_ends_at' => $today->copy()->startOfMonth()->addDays(BillingService::GRACE_DAYS)->toDateString(),
            ]);
            return;
        }

        if (! $institution->billing_grace_ends_at) {
            return;
        }

        $daysLeft = $institution->graceDaysLeft();

        if ($daysLeft === 3 || $daysLeft === 1) {
            $due = $billing->postpaidDueAmount($institution) ?? 'কাস্টম';
            $notifications->billingAlert(
                $institution,
                'billing_due_soon',
                'বিলিং বকেয়া — গ্রেস পিরিয়ড শেষ হতে যাচ্ছে',
                "এই মাসের বিল ৳{$due} এখনো পরিশোধ হয়নি। আর {$daysLeft} দিন পর অ্যাক্সেস বন্ধ হয়ে যাবে।"
            );
        } elseif ($daysLeft !== null && $daysLeft < 0 && ! $institution->billing_suspended) {
            $institution->update(['billing_suspended' => true, 'status' => 'suspended']);
            $notifications->billingAlert(
                $institution,
                'billing_suspended',
                'গ্রেস পিরিয়ড শেষ — অ্যাক্সেস বন্ধ',
                'বকেয়া বিল পরিশোধ না হওয়ায় আপনার প্রতিষ্ঠানের অ্যাক্সেস সাময়িকভাবে বন্ধ করা হয়েছে। পেমেন্ট সাবমিট করুন।'
            );
        }
    }
}
