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
            'attendance-device' => [
                ['key' => 'device_user_id', 'label' => 'ডিভাইস ইউজার আইডি', 'required' => true, 'synonyms' => ['user id', 'userid', 'enroll no', 'enroll id', 'employee id', 'id', 'device user id', 'আইডি']],
                ['key' => 'date', 'label' => 'তারিখ (YYYY-MM-DD)', 'required' => true, 'synonyms' => ['date', 'attendance date', 'তারিখ']],
                ['key' => 'check_in', 'label' => 'প্রবেশের সময় (First In)', 'required' => false, 'synonyms' => ['check in', 'time in', 'first in', 'in time', 'প্রবেশ']],
                ['key' => 'check_out', 'label' => 'বাহিরের সময় (Last Out)', 'required' => false, 'synonyms' => ['check out', 'time out', 'last out', 'out time', 'বাহির']],
                ['key' => 'status', 'label' => 'স্ট্যাটাস (ঐচ্ছিক)', 'required' => false, 'synonyms' => ['status', 'attendance status', 'স্ট্যাটাস']],
            ],
            default => [],
        };
    }

    /**
     * নমুনা ফাইলে দেখানোর জন্য প্রতিটা entity-এর জন্য ১-২টা উদাহরণ সারি
     * (field key => উদাহরণ মান)। এগুলো শুধু ফরম্যাট বোঝানোর জন্য —
     * ইমপোর্টের সময় এই ডাটা নিজে থেকে সিস্টেমে ঢুকবে না, ব্যবহারকারী
     * ডাউনলোড করে নিজের আসল ডাটা দিয়ে সারিগুলো বদলে আপলোড করবেন।
     */
    public static function sampleRows(string $entity): array
    {
        return match ($entity) {
            'students' => [
                ['name' => 'মোহাম্মদ রাকিব হাসান', 'name_en' => 'Md Rakib Hasan', 'student_id_no' => 'S-1001', 'class_name' => 'ষষ্ঠ শ্রেণি', 'section_name' => 'ক', 'gender' => 'male', 'date_of_birth' => '2013-05-14', 'guardian_phone' => '01712345678', 'blood_group' => 'B+', 'previous_school' => 'আদর্শ প্রাথমিক বিদ্যালয়'],
                ['name' => 'ফাতেমা আক্তার', 'name_en' => 'Fatema Akter', 'student_id_no' => 'S-1002', 'class_name' => 'সপ্তম শ্রেণি', 'section_name' => 'খ', 'gender' => 'female', 'date_of_birth' => '2012-11-02', 'guardian_phone' => '01898765432', 'blood_group' => 'O+', 'previous_school' => ''],
            ],
            'teachers' => [
                ['name' => 'মোঃ আব্দুল করিম', 'teacher_id_no' => 'T-101', 'phone' => '01711112222', 'email' => 'karim@example.com', 'designation' => 'সহকারী শিক্ষক', 'joining_date' => '2020-01-15', 'base_salary' => '25000'],
                ['name' => 'নাসরিন সুলতানা', 'teacher_id_no' => 'T-102', 'phone' => '01922223333', 'email' => 'nasrin@example.com', 'designation' => 'সিনিয়র শিক্ষক', 'joining_date' => '2018-06-01', 'base_salary' => '32000'],
            ],
            'fees' => [
                ['student_id_no' => 'S-1001', 'due_month' => '2026-08', 'fee_type' => 'বেতন', 'amount_due' => '1500', 'amount_paid' => '1500', 'payment_method' => 'নগদ'],
                ['student_id_no' => 'S-1002', 'due_month' => '2026-08', 'fee_type' => 'পরিবহন', 'amount_due' => '800', 'amount_paid' => '300', 'payment_method' => 'বিকাশ'],
            ],
            'exam-results' => [
                ['student_id_no' => 'S-1001', 'subject_name' => 'বাংলা', 'marks_obtained' => '78', 'is_absent' => 'no'],
                ['student_id_no' => 'S-1002', 'subject_name' => 'গণিত', 'marks_obtained' => '', 'is_absent' => 'yes'],
            ],
            'attendance-device' => [
                ['device_user_id' => '1001', 'date' => '2026-08-20', 'check_in' => '08:02', 'check_out' => '16:05', 'status' => ''],
                ['device_user_id' => '1002', 'date' => '2026-08-20', 'check_in' => '08:15', 'check_out' => '16:00', 'status' => ''],
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
            'attendance-device' => 'অ্যাটেন্ডেন্স ডিভাইস',
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
