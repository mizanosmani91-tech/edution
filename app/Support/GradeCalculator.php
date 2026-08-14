<?php

namespace App\Support;

/**
 * GradeCalculator — শতকরা মার্কস থেকে গ্রেড/GPA বের করে।
 *
 * standard(): বাংলাদেশের প্রচলিত স্কুল গ্রেডিং (A+ থেকে F, GPA স্কেল ৫)।
 * qawmi(): কওমি মাদ্রাসায় প্রচলিত ফলাফল বিভাগ (মুমতাজ/জাইয়িদ জিদ্দান/
 * জাইয়িদ/মাকবুল/রাসিব) — এটা কোনো নির্দিষ্ট বোর্ডের অফিসিয়াল থ্রেশহোল্ড না,
 * সাধারণভাবে প্রচলিত বিভাজন; প্রতিষ্ঠান চাইলে ভবিষ্যতে কাস্টমাইজ করতে পারবে।
 */
class GradeCalculator
{
    public static function standard(float $percentage): array
    {
        return match (true) {
            $percentage >= 80 => ['label' => 'A+', 'gpa' => 5.0, 'pass' => true],
            $percentage >= 70 => ['label' => 'A', 'gpa' => 4.0, 'pass' => true],
            $percentage >= 60 => ['label' => 'A-', 'gpa' => 3.5, 'pass' => true],
            $percentage >= 50 => ['label' => 'B', 'gpa' => 3.0, 'pass' => true],
            $percentage >= 40 => ['label' => 'C', 'gpa' => 2.0, 'pass' => true],
            $percentage >= 33 => ['label' => 'D', 'gpa' => 1.0, 'pass' => true],
            default => ['label' => 'F', 'gpa' => 0.0, 'pass' => false],
        };
    }

    public static function qawmi(float $percentage): array
    {
        return match (true) {
            $percentage >= 80 => ['label' => 'মুমতাজ', 'gpa' => 5.0, 'pass' => true],
            $percentage >= 70 => ['label' => 'জাইয়িদ জিদ্দান', 'gpa' => 4.0, 'pass' => true],
            $percentage >= 60 => ['label' => 'জাইয়িদ', 'gpa' => 3.0, 'pass' => true],
            $percentage >= 50 => ['label' => 'মাকবুল', 'gpa' => 2.0, 'pass' => true],
            default => ['label' => 'রাসিব', 'gpa' => 0.0, 'pass' => false],
        };
    }

    public static function grade(float $percentage, bool $qawmi = false): array
    {
        return $qawmi ? self::qawmi($percentage) : self::standard($percentage);
    }
}
