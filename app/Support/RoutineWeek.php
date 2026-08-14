<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * routine_periods.day_of_week এর নাম্বারিং কনভেনশন — RoutineBoard যখন
 * পিরিয়ড তৈরি করে তখন এই নাম্বারিং ব্যবহার করে (RoutineBoard.php এর
 * $dayLabels দ্রষ্টব্য): 1=শনি, 2=রবি, 3=সোম, 4=মঙ্গল, 5=বুধ, 6=বৃহঃ, 7=শুক্র।
 *
 * ⚠️ এটাই একমাত্র জায়গা যেখান থেকে "আজ কত নাম্বার দিন" হিসাব করা উচিত।
 * আগে TeacherPortal সরাসরি Carbon::dayOfWeekIso() (1=সোম) ব্যবহার করতো,
 * যেটা RoutineBoard এর কনভেনশনের সাথে মিলতো না — ফলে সপ্তাহে ৬ দিনই
 * "আজকের রুটিন" ভুল দিন দেখাতো। নতুন যেকোনো কোডে এই হেল্পার ব্যবহার করুন।
 */
class RoutineWeek
{
    public static function numberFor(Carbon $date): int
    {
        // Carbon::dayOfWeek — রবি=0 ... শনি=6
        return ($date->dayOfWeek + 1) % 7 + 1;
    }

    public static function todayNumber(): int
    {
        return self::numberFor(now());
    }

    public static function labels(): array
    {
        return [1 => 'শনি', 2 => 'রবি', 3 => 'সোম', 4 => 'মঙ্গল', 5 => 'বুধ', 6 => 'বৃহঃ', 7 => 'শুক্র'];
    }
}
