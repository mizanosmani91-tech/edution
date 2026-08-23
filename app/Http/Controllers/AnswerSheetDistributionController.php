<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\SchoolClass;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * AnswerSheetDistributionController
 *
 * পরীক্ষার দিন প্রতিটা বিষয়ের শিক্ষককে কতগুলো উত্তরপত্র দেওয়া হলো, তার
 * হিসাব রাখার শীট — বিষয়/শিক্ষক/মোট ছাত্র অটো-ফিল হয়ে যায় (ExamSubject
 * ডেটা থেকে), "মোট উত্তরপত্র" ও "স্বাক্ষর" কলাম হাতে লেখার জন্য ফাঁকা
 * রাখা হয় (কারণ প্রকৃত বন্টিত সংখ্যা পরীক্ষার দিনই ঠিক হয়)।
 */
class AnswerSheetDistributionController extends Controller
{
    public function print(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => ['required', 'uuid', Rule::exists('exams', 'id')->where('institution_id', app('tenant.institution_id'))],
            'class_id' => ['required', 'uuid', Rule::exists('classes', 'id')->where('institution_id', app('tenant.institution_id'))],
        ]);

        $exam = Exam::findOrFail($validated['exam_id']);
        $class = SchoolClass::findOrFail($validated['class_id']);

        $studentCount = Student::where('class_id', $class->id)->where('status', 'active')->count();

        $rows = ExamSubject::where('exam_id', $exam->id)
            ->where('class_id', $class->id)
            ->with(['subject', 'teacher'])
            ->orderBy('exam_date')
            ->get();

        $pdf = Pdf::loadView('pdf.answer-sheet-distribution', [
            'exam' => $exam,
            'class' => $class,
            'institution' => auth()->user()->institution,
            'rows' => $rows,
            'studentCount' => $studentCount,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("answer-sheet-distribution-{$class->name}-{$exam->name}.pdf");
    }
}
