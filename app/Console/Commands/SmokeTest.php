<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Blade;
use Symfony\Component\Finder\Finder;
use Throwable;

/**
 * edution:smoke-test — ডিপ্লয়ের সময় চালানো হয় (GitHub Actions deploy.yml)।
 *
 * উদ্দেশ্য: কোডে কোনো ভাঙা Blade সিনট্যাক্স/অন্যান্য বেসিক সমস্যা থাকলে
 * সেটা DEPLOY-এর সময়ই ধরা পড়ুক — প্রোডাকশনে গিয়ে ইউজার/সুপারএডমিন
 * দেখার আগেই। এটা আজকের সেশনে পাওয়া `@php($expr)` শর্টহ্যান্ড বাগের
 * ঠিক এই ক্লাসের সমস্যা ধরার জন্য বানানো — Blade::compileString() ভুল
 * সিনট্যাক্স থাকলেও চুপচাপ pass করে (শুধু টেক্সট ট্রান্সফর্ম করে), আসল
 * PHP এরর তখনই ধরা পড়ে যখন কম্পাইল করা PHP ফাইলটা `php -l` দিয়ে
 * সিনট্যাক্স-চেক করা হয় — এই কমান্ড ঠিক সেটাই করে, প্রতিটা .blade.php
 * ফাইলের জন্য।
 *
 * ব্যর্থ হলে: exit code 1 (deploy স্ক্রিপ্ট `set -e` থাকায় পুরো ডিপ্লয়
 * থেমে যাবে, migrate/cache কিছুই চলবে না, লাইভ সাইট আগের ভালো ভার্সনেই
 * থেকে যাবে) + সুপারএডমিনদের এসএমএস/ইমেইলে সঠিক ফাইল+লাইন+এরর মেসেজ চলে
 * যাবে।
 */
class SmokeTest extends Command
{
    protected $signature = 'edution:smoke-test';

    protected $description = 'ডিপ্লয়ের আগে সব Blade ভিউ কম্পাইল করে PHP সিনট্যাক্স যাচাই করে; ভাঙা কিছু পেলে ডিপ্লয় আটকে দেয় এবং সুপারএডমিনদের জানায়।';

    public function handle(): int
    {
        $this->info('সব .blade.php ফাইল কম্পাইল করে সিনট্যাক্স চেক করা হচ্ছে...');

        $viewsPath = resource_path('views');
        $failures = [];
        $checked = 0;

        $finder = new Finder();
        $finder->files()->in($viewsPath)->name('*.blade.php');

        $tmpDir = sys_get_temp_dir().'/edution-smoketest-'.uniqid();
        mkdir($tmpDir, 0777, true);

        foreach ($finder as $file) {
            $relative = str_replace($viewsPath.DIRECTORY_SEPARATOR, '', $file->getRealPath());
            $checked++;

            try {
                $compiled = Blade::compileString(file_get_contents($file->getRealPath()));
            } catch (Throwable $e) {
                $failures[] = [
                    'file' => $relative,
                    'error' => 'Blade compile ব্যর্থ: '.$e->getMessage(),
                ];
                continue;
            }

            $tmpFile = $tmpDir.'/'.uniqid().'.php';
            file_put_contents($tmpFile, $compiled);

            $output = [];
            $exitCode = 0;
            exec('php -l '.escapeshellarg($tmpFile).' 2>&1', $output, $exitCode);
            @unlink($tmpFile);

            if ($exitCode !== 0) {
                $failures[] = [
                    'file' => $relative,
                    'error' => implode("\n", $output),
                ];
            }
        }

        @rmdir($tmpDir);

        $this->info("মোট চেক করা হয়েছে: {$checked}টা ভিউ।");

        if (empty($failures)) {
            $this->info('✅ সব ভিউ ঠিক আছে, সিনট্যাক্স এরর নেই।');
            return self::SUCCESS;
        }

        $this->error('❌ '.count($failures).'টা ভিউতে সমস্যা পাওয়া গেছে:');
        $summaryLines = [];
        foreach ($failures as $f) {
            $line = "- {$f['file']}: {$f['error']}";
            $this->line($line);
            $summaryLines[] = $line;
        }

        try {
            app(NotificationService::class)->deploySmokeTestFailed(implode("\n\n", $summaryLines));
        } catch (Throwable $e) {
            $this->warn('সুপারএডমিনদের অ্যালার্ট পাঠাতে ব্যর্থ: '.$e->getMessage());
        }

        return self::FAILURE;
    }
}
