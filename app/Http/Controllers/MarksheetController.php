<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\ExamResultService;
use App\Support\GradeCalculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * MarksheetController — composer require barryvdh/laravel-dompdf লাগবে।
 * Memory তে "Print orientation toggle" উল্লেখ ছিল — সেটা এখানে $orientation
 * প্যারামিটার দিয়ে সাপোর্ট করা হলো (portrait/landscape)।
 */
class MarksheetController extends Controller
{
    public function __construct(private ExamResultService $examResults)
    {
    }

    /**
     * একটা ক্লাসের সব ছাত্রের একটা exam-এর মার্কশিট — একসাথে PDF
     */
    public function classMarksheet(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => ['required', 'uuid', Rule::exists('exams', 'id')->where('institution_id', app('tenant.institution_id'))],
            'class_id' => ['required', 'uuid', Rule::exists('classes', 'id')->where('institution_id', app('tenant.institution_id'))],
            'orientation' => ['nullable', Rule::in(['portrait', 'landscape'])],
        ]);

        $exam = Exam::findOrFail($validated['exam_id']);
        $class = SchoolClass::findOrFail($validated['class_id']);

        // ⚠️ get_effective_exam_marks() ফাংশন থেকে raw রো আসে (student_id,
        // exam_subject_id, marks...) — এগুলোকে student-wise group করে সাজাতে
        // হবে ভিউ এর জন্য
        $rawMarks = $this->examResults->getEffectiveMarksForClass($exam->id, $class->id);

        $students = Student::where('class_id', $class->id)->where('status', 'active')->orderBy('name')->get();

        $examSubjects = \App\Models\ExamSubject::where('exam_id', $exam->id)
            ->where('class_id', $class->id)
            ->with('subject')
            ->get();

        $marksByStudent = collect($rawMarks)->groupBy('student_id');

        $pdf = Pdf::loadView('pdf.class-marksheet', [
            'exam' => $exam,
            'class' => $class,
            'students' => $students,
            'examSubjects' => $examSubjects,
            'marksByStudent' => $marksByStudent,
        ])->setPaper('a4', $validated['orientation'] ?? 'portrait');

        return $pdf->download("marksheet-{$class->name}-{$exam->name}.pdf");
    }

    /**
     * একজন ছাত্রের individual মার্কশিট (guardian/student portal থেকে ডাউনলোড)
     */
    public function studentMarksheet(Request $request, Student $student)
    {
        // ⚠️ guardian হলে নিজের সন্তানের মার্কশিটই শুধু ডাউনলোড করতে পারবে,
        // student হলে নিজেরটাই — route model binding + global scope এমনিতেই
        // institution আটকায়, কিন্তু owner-check এখানে আলাদা করে দরকার
        $user = auth()->user();
        $isOwnChild = $user->children()->where('students.id', $student->id)->exists();
        $isOwnProfile = $user->student_id === $student->id;
        $isStaff = in_array($user->role, ['admin', 'teacher']);

        abort_unless($isOwnChild || $isOwnProfile || $isStaff, 403);

        $validated = $request->validate([
            'exam_id' => ['required', 'uuid', Rule::exists('exams', 'id')->where('institution_id', app('tenant.institution_id'))],
        ]);

        $exam = Exam::findOrFail($validated['exam_id']);

        $subjectResults = \App\Models\ExamSubject::where('exam_id', $exam->id)
            ->where('class_id', $student->class_id)
            ->with('subject')
            ->get()
            ->map(fn ($es) => [
                'subject' => $es->subject->name,
                ...((array) $this->examResults->computeStudentSubjectResult($student->id, $exam->id, $es->subject_id)),
            ]);

        $pdf = Pdf::loadView('pdf.student-marksheet', [
            'exam' => $exam,
            'student' => $student,
            'results' => $subjectResults,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("marksheet-{$student->student_id_no}-{$exam->name}.pdf");
    }

    /**
     * ট্যাবুলেশন শীট — অফিসিয়াল রেকর্ডের জন্য, সব ছাত্রের সব বিষয়ের নম্বর
     * এক গ্রিডে, সাথে মোট/শতকরা/গ্রেড/মেধাক্রম ও পরীক্ষা নিয়ন্ত্রক ও
     * প্রধান শিক্ষকের স্বাক্ষরের জায়গা — ছাত্রকে দেওয়া মার্কশিট থেকে আলাদা,
     * এটা প্রতিষ্ঠানের নিজস্ব রেকর্ড রাখার জন্য।
     */
    public function classTabulation(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => ['required', 'uuid', Rule::exists('exams', 'id')->where('institution_id', app('tenant.institution_id'))],
            'class_id' => ['required', 'uuid', Rule::exists('classes', 'id')->where('institution_id', app('tenant.institution_id'))],
        ]);

        $exam = Exam::findOrFail($validated['exam_id']);
        $class = SchoolClass::findOrFail($validated['class_id']);
        $institution = auth()->user()->institution;

        $rawMarks = $this->examResults->getEffectiveMarksForClass($exam->id, $class->id);
        $marksByStudent = collect($rawMarks)->groupBy('student_id');

        $examSubjects = \App\Models\ExamSubject::where('exam_id', $exam->id)
            ->where('class_id', $class->id)
            ->with('subject')
            ->get();

        $students = Student::where('class_id', $class->id)->where('status', 'active')->orderBy('name')->get();

        $isQawmi = false; // ভবিষ্যতে চাইলে route param দিয়ে qawmi mode আলাদা করা যাবে

        $rows = $students->map(function ($student) use ($marksByStudent, $isQawmi) {
            $studentMarks = $marksByStudent->get($student->id, collect());
            $obtained = 0.0;
            $full = 0.0;
            $hasAbsent = false;

            foreach ($studentMarks as $m) {
                $full += (float) ($m->full_marks ?? 0);
                if ($m->is_absent) {
                    $hasAbsent = true;
                    continue;
                }
                $obtained += (float) ($m->marks_obtained ?? 0);
            }

            $percentage = $full > 0 ? round(($obtained / $full) * 100, 2) : 0;
            $grade = GradeCalculator::grade($percentage, $isQawmi);

            return [
                'student' => $student,
                'marks' => $studentMarks,
                'total' => $obtained,
                'totalMax' => $full,
                'percentage' => $percentage,
                'grade' => $grade['label'],
                'gpa' => $grade['gpa'],
                'is_pass' => $grade['pass'] && ! $hasAbsent,
            ];
        })->sortByDesc(fn ($r) => $r['is_pass'] ? $r['percentage'] : -1)->values();

        // মেধাক্রম (position) — একই শতকরা হলে একই position শেয়ার করবে
        $position = 0;
        $lastPercentage = null;
        $rows = $rows->map(function ($r) use (&$position, &$lastPercentage) {
            if ($r['is_pass']) {
                if ($lastPercentage === null || $r['percentage'] !== $lastPercentage) {
                    $position++;
                }
                $lastPercentage = $r['percentage'];
                $r['position'] = $position;
            } else {
                $r['position'] = null;
            }
            return $r;
        });

        $pdf = Pdf::loadView('pdf.class-tabulation', [
            'exam' => $exam,
            'class' => $class,
            'institution' => $institution,
            'examSubjects' => $examSubjects,
            'rows' => $rows,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("tabulation-{$class->name}-{$exam->name}.pdf");
    }
}
