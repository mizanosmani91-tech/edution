<?php

namespace App\Services;

/**
 * SystemHealthService — সুপারএডমিন প্যানেলের "সিস্টেম হেলথ" পেজের জন্য।
 * storage/logs/laravel.log পার্স করে সাম্প্রতিক ERROR/CRITICAL এন্ট্রিগুলো
 * বের করে (SSH করে গ্রেপ করার বদলে সরাসরি প্যানেলে দেখানোর জন্য) —
 * ২০২৬-০৮-১৫ তারিখে edution.xyz/panel.edution.xyz এ open_basedir-জনিত
 * ডাউনটাইম ম্যানুয়ালি SSH করে খুঁজে বের করতে হয়েছিল, সেই অভিজ্ঞতা থেকে।
 */
class SystemHealthService
{
    // লগ ফাইল অনেক বড় হতে পারে — শুধু শেষ এই পরিমাণ বাইট পড়া হয়, পুরো
    // ফাইল মেমরিতে লোড করলে বড় প্রোডাকশন লগে পেজ স্লো/ক্র্যাশ হয়ে যেতে পারে।
    protected const TAIL_BYTES = 2 * 1024 * 1024; // ২ মেগাবাইট

    public function logPath(): string
    {
        return storage_path('logs/laravel.log');
    }

    public function logSizeMb(): float
    {
        $path = $this->logPath();

        return file_exists($path) ? round(filesize($path) / 1024 / 1024, 2) : 0.0;
    }

    /**
     * সাম্প্রতিক এরর এন্ট্রি — নতুন থেকে পুরাতন ক্রমে, প্রতিটাতে সময়,
     * লেভেল (ERROR/CRITICAL/WARNING), সংক্ষিপ্ত মেসেজ, আর পুরো এন্ট্রির
     * প্রথম কয়েক লাইন (স্ট্যাক ট্রেসসহ, বেশি বড় হলে ছেঁটে দেওয়া)।
     */
    public function recentErrors(int $limit = 30): array
    {
        $path = $this->logPath();

        if (! file_exists($path)) {
            return [];
        }

        $size = filesize($path);
        $offset = max(0, $size - self::TAIL_BYTES);

        $handle = fopen($path, 'r');
        if (! $handle) {
            return [];
        }

        fseek($handle, $offset);
        $chunk = fread($handle, $size - $offset);
        fclose($handle);

        // প্রথম (সম্ভবত অসম্পূর্ণ) লাইনটা বাদ — টেইল থেকে পড়ার কারণে মাঝপথ থেকে শুরু হতে পারে
        $lines = explode("\n", $chunk);
        array_shift($lines);

        $entries = [];
        $current = null;

        foreach ($lines as $line) {
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+\S+\.(ERROR|CRITICAL|ALERT|EMERGENCY|WARNING):\s*(.*)$/', $line, $m)) {
                if ($current) {
                    $entries[] = $current;
                }
                $current = [
                    'time' => $m[1],
                    'level' => $m[2],
                    'message' => mb_substr($m[3], 0, 300),
                    'detail' => $line,
                    'lines' => 1,
                ];
            } elseif ($current !== null && $current['lines'] < 15) {
                // একই এরর এন্ট্রির পরের লাইন (স্ট্যাক ট্রেস) — প্রথম ১৫ লাইন পর্যন্ত রাখা হয়
                $current['detail'] .= "\n" . $line;
                $current['lines']++;
            }
        }

        if ($current) {
            $entries[] = $current;
        }

        return array_slice(array_reverse($entries), 0, $limit);
    }

    public function diskUsagePercent(): int
    {
        $total = @disk_total_space('/');
        $free = @disk_free_space('/');

        if (! $total || $total <= 0) {
            return 0;
        }

        return (int) round((($total - $free) / $total) * 100);
    }

    public function diskFreeGb(): float
    {
        $free = @disk_free_space('/');

        return $free ? round($free / 1024 / 1024 / 1024, 1) : 0.0;
    }

    public function clearLog(): void
    {
        $path = $this->logPath();

        if (file_exists($path)) {
            file_put_contents($path, '');
        }
    }
}
