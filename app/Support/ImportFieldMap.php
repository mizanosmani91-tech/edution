<?php

namespace App\Support;

/**
 * ImportFieldMap — প্রতিটা entity (students/teachers/fees/exam-results) এর
 * জন্য টার্গেট ফিল্ড লিস্ট + অটো-ম্যাপিং অনুমান করার জন্য সম্ভাব্য
 * কলাম-নামের (বাংলা+ইংরেজি) সমার্থক শব্দ তালিকা। DataImporter এই লিস্ট
 * দিয়ে আপলোড করা ফাইলের header এর সাথে মিলিয়ে auto-guess করে, admin
 * প্রয়োজনে সংশোধন করতে পারেন।
 */
class ImportFieldMap
{
    public static function fields(string $entity): array
    {
        return match ($entity) {
            'students' => [
                ['key' => 'name', 'label' => 'নাম', 'required' => true, 'synonyms' => ['name', 'নাম', 'student name', 'শিক্ষার্থীর নাম', 'ছাত্র/ছাত্রীর নাম', 'ছাত্রের নাম']],
                ['key' => 'name_en', 'label' => 'ইংরেজি নাম', 'required' => false, 'synonyms' => ['name (english)', 'english name', 'name_en']],
                ['key' => 'student_id_no', 'label' => 'শিক্ষার্থী আইডি (থাকলে)', 'required' => false, 'synonyms' => ['id', 'student id', 'roll', 'roll no', 'আইডি', 'রোল', 'শিক্ষার্থী আইডি']],
                ['key' => 'class_name', 'label' => 'শ্রেণি', 'required' => true, 'synonyms' => ['class', 'শ্রেণি', 'ক্লাস']],
                ['key' => 'section_name', 'label' => 'শাখা', 'required' => false, 'synonyms' => ['section', 'শাখা', 'সেকশন']],
                ['key' => 'gender', 'label' => 'লিঙ্গ', 'required' => false, 'synonyms' => ['gender', 'sex', 'লিঙ্গ']],
                ['key' => 'date_of_birth', 'label' => 'জন্ম তারিখ', 'required' => false, 'synonyms' => ['dob', 'date of birth', 'birth date', 'জন্ম তারিখ']],
                ['key' => 'guardian_phone', 'label' => 'অভিভাবকের ফোন', 'required' => true, 'synonyms' => ['phone', 'guardian phone', 'mobile', 'ফোন', 'মোবাইল', 'অভিভাবকের ফোন']],
                ['key' => 'blood_group', 'label' => 'রক্তের গ্রুপ', 'required' => false, 'synonyms' => ['blood group', 'রক্তের গ্রুপ']],
                ['key' => 'previous_school', 'label' => 'পূর্ববর্তী বিদ্যালয়', 'required' => false, 'synonyms' => ['previous school', 'পূর্ববর্তী বিদ্যালয়']],
            ],
            'teachers' => [
                ['key' => 'name', 'label' => 'নাম', 'required' => true, 'synonyms' => ['name', 'নাম', 'teacher name', 'শিক্ষকের নাম']],
                ['key' => 'teacher_id_no', 'label' => 'আইডি (থাকলে)', 'required' => false, 'synonyms' => ['id', 'teacher id', 'employee id', 'আইডি']],
                ['key' => 'phone', 'label' => 'ফোন', 'required' => true, 'synonyms' => ['phone', 'mobile', 'ফোন', 'মোবাইল']],
                ['key' => 'email', 'label' => 'ইমেইল', 'required' => false, 'synonyms' => ['email', 'ইমেইল']],
                ['key' => 'designation', 'label' => 'পদবি', 'required' => false, 'synonyms' => ['designation', 'পদবি', 'position']],
                ['key' => 'joining_date', 'label' => 'যোগদানের তারিখ', 'required' => false, 'synonyms' => ['joining date', 'যোগদানের তারিখ']],
                ['key' => 'base_salary', 'label' => 'মূল বেতন', 'required' => false, 'synonyms' => ['salary', 'base salary', 'বেতন', 'মূল বেতন']],
            ],
            'fees' => [
                ['key' => 'student_id_no', 'label' => 'শিক্ষার্থী আইডি', 'required' => true, 'synonyms' => ['student id', 'id', 'আইডি', 'শিক্ষার্থী আইডি']],
                ['key' => 'due_month', 'label' => 'মাস (YYYY-MM)', 'required' => true, 'synonyms' => ['month', 'due month', 'মাস']],
                ['key' => 'fee_type', 'label' => 'ফি এর ধরন', 'required' => false, 'synonyms' => ['fee type', 'type', 'ফি এর ধরন']],
                ['key' => 'amount_due', 'label' => 'বকেয়া পরিমাণ', 'required' => true, 'synonyms' => ['amount due', 'due', 'বকেয়া']],
                ['key' => 'amount_paid', 'label' => 'পরিশোধিত পরিমাণ', 'required' => true, 'synonyms' => ['amount paid', 'paid', 'পরিশোধিত']],
                ['key' => 'payment_method', 'label' => 'পেমেন্ট পদ্ধতি', 'required' => false, 'synonyms' => ['payment method', 'method', 'পেমেন্ট পদ্ধতি']],
            ],
            'exam-results' => [
                ['key' => 'student_id_no', 'label' => 'শিক্ষার্থী আইডি', 'required' => true, 'synonyms' => ['student id', 'id', 'আইডি', 'শিক্ষার্থী আইডি']],
                ['key' => 'subject_name', 'label' => 'বিষয়', 'required' => true, 'synonyms' => ['subject', 'বিষয়']],
                ['key' => 'marks_obtained', 'label' => 'প্রাপ্ত নম্বর', 'required' => true, 'synonyms' => ['marks', 'marks obtained', 'score', 'প্রাপ্ত নম্বর', 'নম্বর']],
                ['key' => 'is_absent', 'label' => 'অনুপস্থিত (yes/no)', 'required' => false, 'synonyms' => ['absent', 'is absent', 'অনুপস্থিত']],
            ],
            default => [],
        };
    }

    public static function label(string $entity): string
    {
        return match ($entity) {
            'students' => 'শিক্ষার্থী',
            'teachers' => 'শিক্ষক/স্টাফ',
            'fees' => 'ফি হিস্টোরি',
            'exam-results' => 'পরীক্ষার ফলাফল',
            default => 'ডাটা',
        };
    }

    /**
     * ফাইলের header থেকে auto-guess করে target field key => column index ম্যাপ তৈরি করে
     */
    public static function guessMapping(array $fields, array $headers): array
    {
        $mapping = [];

        foreach ($fields as $field) {
            $candidates = array_merge([$field['key'], $field['label']], $field['synonyms']);
            $candidates = array_map(fn ($c) => self::normalize($c), $candidates);

            $bestIndex = null;

            foreach ($headers as $i => $header) {
                $normalizedHeader = self::normalize($header);

                if (in_array($normalizedHeader, $candidates, true)) {
                    $bestIndex = $i;
                    break;
                }
            }

            if ($bestIndex === null) {
                foreach ($headers as $i => $header) {
                    $normalizedHeader = self::normalize($header);
                    foreach ($candidates as $c) {
                        if ($c !== '' && (str_contains($normalizedHeader, $c) || str_contains($c, $normalizedHeader))) {
                            $bestIndex = $i;
                            break 2;
                        }
                    }
                }
            }

            $mapping[$field['key']] = $bestIndex;
        }

        return $mapping;
    }

    protected static function normalize(string $value): string
    {
        return trim(mb_strtolower($value));
    }
}
