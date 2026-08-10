<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * ExamResultService
 *
 * get_effective_exam_marks / compute_exam_subject_result — এগুলো Postgres
 * function হিসেবেই থেকে যাচ্ছে (PHP-তে রিরাইট করা হয়নি) কারণ:
 *   ১. এটা রিকার্সিভ লজিক, DB-তে থাকলে performant (N+1 avoid হয়)
 *   ২. এটাই আসল, প্রমাণিত (battle-tested) business logic — নতুন করে PHP-তে
 *      লিখলে সূক্ষ্ম বাগ ঢোকার ঝুঁকি থাকত
 *
 * এই ক্লাসটা শুধু একটা clean PHP interface দেয়, যাতে Controller-এ raw
 * DB::select() ছড়িয়ে না থাকে।
 */
class ExamResultService
{
    /**
     * একটা exam+class এর সব ছাত্রের effective (weighted) মার্ক।
     * results/page.tsx ও Marksheet.tsx যেভাবে RPC কল করত, এখানে সেটারই সমতুল্য।
     */
    public function getEffectiveMarksForClass(string $examId, string $classId): array
    {
        return DB::select(
            'SELECT * FROM get_effective_exam_marks(?, ?)',
            [$examId, $classId]
        );
    }

    /**
     * একজন নির্দিষ্ট ছাত্রের একটা নির্দিষ্ট subject-এর চূড়ান্ত (weighted) ফলাফল।
     * marks entry validation বা individual marksheet regenerate করার সময় কাজে লাগে।
     */
    public function computeStudentSubjectResult(string $studentId, string $examId, string $subjectId): object
    {
        $result = DB::selectOne(
            'SELECT * FROM compute_exam_subject_result(?, ?, ?)',
            [$studentId, $examId, $subjectId]
        );

        return (object) [
            'final_marks' => $result->final_marks ?? null,
            'final_max_marks' => $result->final_max_marks ?? 0,
            'is_absent' => (bool) ($result->is_absent ?? false),
            'is_pass' => (bool) ($result->is_pass ?? false),
        ];
    }
}
