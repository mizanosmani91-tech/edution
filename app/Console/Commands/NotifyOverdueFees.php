<?php

namespace App\Console\Commands;

use App\Models\FeeCollection;
use App\Services\NotificationService;
use Illuminate\Console\Command;

/**
 * NotifyOverdueFees — cron/scheduler দিয়ে দৈনিক চালানোর জন্য।
 * console command এ কোনো request/middleware context থাকে না (tenant.institution_id
 * bind করা থাকে না), তাই allTenants() দিয়ে explicit bypass করে সব institution
 * এর overdue fee লুপ করা হচ্ছে।
 */
class NotifyOverdueFees extends Command
{
    protected $signature = 'edution:notify-overdue-fees';
    protected $description = 'বকেয়া/ওভারডিউ ফি এর জন্য guardian দের নোটিফাই করে (দৈনিক cron)';

    public function handle(NotificationService $notifications): int
    {
        $dueFees = FeeCollection::allTenants()
            ->whereIn('status', ['due', 'overdue'])
            ->where('created_at', '<=', now()->subDays(3))
            ->get();

        foreach ($dueFees as $fee) {
            $notifications->feeDue($fee);
        }

        $this->info("{$dueFees->count()} টা বকেয়া ফি এর জন্য নোটিফিকেশন পাঠানো হলো।");

        return self::SUCCESS;
    }
}
