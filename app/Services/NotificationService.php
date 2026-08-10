<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Exam;
use App\Models\FeeCollection;
use App\Models\Student;

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
}
