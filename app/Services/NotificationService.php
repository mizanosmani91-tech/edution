<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Exam;
use App\Models\FeeCollection;
use App\Models\Student;
use App\Models\Institution;
use App\Models\User;
use App\Mail\BillingAlertMail;
use App\Services\SmsOtpService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * NotificationService — এখন শুধু in-app notification (ডেটাবেজে সেভ)।
 * SMS/push যোগ করতে চাইলে এখানে একটা করে চ্যানেল method যোগ করবেন
 * (sendSms(), sendPush()) — কল করার জায়গা বদলাতে হবে না।
 */
class NotificationService
{
    public function feeDue(FeeCollection $fee): void
    {
        foreach ($fee->student->guardians as $guardian) {
            AppNotification::create([
                'institution_id' => $fee->institution_id,
                'user_id' => $guardian->id,
                'type' => 'fee_due',
                'title' => "{$fee->student->name} এর ফি বকেয়া",
                'body' => "৳{$fee->due_amount} বকেয়া আছে {$fee->due_month} মাসের জন্য।",
                'link' => '/portal/guardian',
            ]);
        }
    }

    public function attendanceAbsent(Student $student): void
    {
        foreach ($student->guardians as $guardian) {
            AppNotification::create([
                'institution_id' => $student->institution_id,
                'user_id' => $guardian->id,
                'type' => 'attendance_absent',
                'title' => "{$student->name} আজ অনুপস্থিত",
                'link' => '/portal/guardian',
            ]);
        }
    }

    public function examPublished(Exam $exam): void
    {
        $classIds = \App\Models\ExamSubject::where('exam_id', $exam->id)->pluck('class_id');
        $students = Student::whereIn('class_id', $classIds)->with('guardians')->get();

        foreach ($students as $student) {
            \App\Models\User::where('student_id', $student->id)->get()->each(
                fn ($u) => AppNotification::create([
                    'institution_id' => $exam->institution_id,
                    'user_id' => $u->id,
                    'type' => 'exam_published',
                    'title' => "{$exam->name} এর ফলাফল প্রকাশিত হয়েছে",
                    'link' => '/portal/student',
                ])
            );

            foreach ($student->guardians as $guardian) {
                AppNotification::create([
                    'institution_id' => $exam->institution_id,
                    'user_id' => $guardian->id,
                    'type' => 'exam_published',
                    'title' => "{$student->name} এর {$exam->name} ফলাফল প্রকাশিত হয়েছে",
                    'link' => '/portal/guardian',
                ]);
            }
        }
    }

    /**
     * বিলিং সংক্রান্ত এলার্ট — in-app (AppNotification) + SMS + ইমেইল,
     * তিনটাই একসাথে (ইউজার যেভাবেই দেখুক, মিস না করে)। শুধু 'admin' রোলের
     * ইউজারদের পাঠানো হয় (guardian/teacher দের বিলিং নিয়ে জানানোর দরকার নেই)।
     */
    public function billingAlert(Institution $institution, string $type, string $title, string $body): void
    {
        $admins = User::where('institution_id', $institution->id)->where('role', 'admin')->get();

        foreach ($admins as $admin) {
            AppNotification::create([
                'institution_id' => $institution->id,
                'user_id' => $admin->id,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'link' => '/billing',
            ]);
        }

        if ($institution->phone) {
            try {
                app(SmsOtpService::class)->sendMessage($institution->phone, "EDUTION: {$title} — {$body}");
            } catch (\Throwable $e) {
                Log::warning('বিলিং SMS পাঠাতে ব্যর্থ: ' . $e->getMessage());
            }
        }

        if ($institution->registration_email) {
            try {
                Mail::to($institution->registration_email)->send(new BillingAlertMail($institution, $title, $body));
            } catch (\Throwable $e) {
                Log::warning('বিলিং ইমেইল পাঠাতে ব্যর্থ: ' . $e->getMessage());
            }
        }
    }

    /**
     * ⚠️ ডিপ্লয়-টাইম সেফটি নেট: edution:smoke-test কমান্ড কোনো Blade/PHP
     * সিনট্যাক্স এরর পেলে ডিপ্লয় থামিয়ে দেয় (deploy.yml এ) আর সাথে সাথে
     * সব সুপারএডমিনকে এসএমএস+ইমেইলে জানিয়ে দেয় — ঠিক কোন ফাইলে, কী সমস্যা।
     * এই কারণেই আজকে যেভাবে ঘণ্টার পর ঘণ্টা ম্যানুয়ালি লগ খুঁজে বের করতে
     * হয়েছে, ভবিষ্যতে আর লাগবে না — ডিপ্লয়ের সময়ই ধরা পড়বে, লাইভ সাইটে
     * পৌঁছানোর আগেই।
     */
    public function deploySmokeTestFailed(string $details): void
    {
        $superadmins = User::whereNull('institution_id')->where('role', 'superadmin')->get();
        $short = "EDUTION ডিপ্লয় আটকে গেছে! কোডে সিনট্যাক্স এরর পাওয়া গেছে, সাইট আপডেট হয়নি। বিস্তারিত ইমেইলে/সিস্টেম হেলথ পেজে দেখুন।";

        foreach ($superadmins as $admin) {
            if ($admin->phone) {
                try {
                    app(SmsOtpService::class)->sendMessage($admin->phone, $short);
                } catch (\Throwable $e) {
                    Log::warning('ডিপ্লয়-ফেইল SMS পাঠাতে ব্যর্থ: ' . $e->getMessage());
                }
            }
            if ($admin->email) {
                try {
                    Mail::raw("EDUTION ডিপ্লয় ব্যর্থ হয়েছে — কোডে সিনট্যাক্স এরর পাওয়া গেছে, তাই নতুন ভার্সন লাইভ সাইটে যায়নি (আগের ভালো ভার্সনই চলছে)।\n\nবিস্তারিত:\n\n{$details}", function ($m) use ($admin) {
                        $m->to($admin->email)->subject('⚠️ EDUTION ডিপ্লয় আটকে গেছে');
                    });
                } catch (\Throwable $e) {
                    Log::warning('ডিপ্লয়-ফেইল ইমেইল পাঠাতে ব্যর্থ: ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * ⚠️ রিয়েল-টাইম এরর অ্যালার্ট: প্রোডাকশনে যেকোনো অপ্রত্যাশিত এরর
     * (৫xx-লেভেল, ৪xx ভ্যালিডেশন/৪০৪ বাদে) ঘটলে সাথে সাথে সুপারএডমিনদের
     * এসএমএস+ইমেইলে জানিয়ে দেয় — ঠিক কোন এক্সেপশন, কোন ফাইলে, কোন লাইনে।
     * bootstrap/app.php এর withExceptions() থেকে কল হয়। একই এরর বারবার
     * এলে স্প্যাম আটকাতে ১৫ মিনিটে একবারই পাঠানো হয় (cache throttle)।
     */
    public function systemErrorAlert(\Throwable $e): void
    {
        $key = 'error-alert-' . md5(get_class($e) . $e->getFile() . $e->getLine());

        if (\Illuminate\Support\Facades\Cache::has($key)) {
            return;
        }
        \Illuminate\Support\Facades\Cache::put($key, true, now()->addMinutes(15));

        $host = request()?->getHost() ?? 'unknown-host';
        $summary = get_class($e) . ': ' . $e->getMessage();
        $location = $e->getFile() . ':' . $e->getLine();

        $superadmins = User::whereNull('institution_id')->where('role', 'superadmin')->get();

        foreach ($superadmins as $admin) {
            if ($admin->phone) {
                try {
                    app(SmsOtpService::class)->sendMessage(
                        $admin->phone,
                        "EDUTION এরর ({$host}): " . \Illuminate\Support\Str::limit($summary, 100)
                    );
                } catch (\Throwable $inner) {
                    Log::warning('সিস্টেম এরর SMS পাঠাতে ব্যর্থ: ' . $inner->getMessage());
                }
            }
            if ($admin->email) {
                try {
                    Mail::raw(
                        "সাইট: {$host}\nএরর: {$summary}\nঅবস্থান: {$location}\n\nবিস্তারিত সুপারএডমিন প্যানেলের 'সিস্টেম হেলথ' পেজে দেখুন।",
                        function ($m) use ($admin, $host) {
                            $m->to($admin->email)->subject("⚠️ EDUTION এরর — {$host}");
                        }
                    );
                } catch (\Throwable $inner) {
                    Log::warning('সিস্টেম এরর ইমেইল পাঠাতে ব্যর্থ: ' . $inner->getMessage());
                }
            }
        }
    }
}
