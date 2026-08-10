<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdmitCardController extends Controller
{
    /**
     * একটা ক্লাসের সব ছাত্রের admit card একসাথে (২টা কার্ড প্রতি পেজে,
     * প্রিন্ট করে কেটে দেওয়ার জন্য — এটাই বাংলাদেশি স্কুলগুলোর কমন প্র্যাকটিস)
     */
    public function classAdmitCards(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => ['required', 'uuid', Rule::exists('exams', 'id')->where('institution_id', app('tenant.institution_id'))],
            'class_id' => ['required', 'uuid', Rule::exists('classes', 'id')->where('institution_id', app('tenant.institution_id'))],
            'orientation' => ['nullable', Rule::in(['portrait', 'landscape'])],
        ]);

        $exam = Exam::findOrFail($validated['exam_id']);
        $class = SchoolClass::findOrFail($validated['class_id']);
        $students = Student::where('class_id', $class->id)->where('status', 'active')->orderBy('name')->get();

        $pdf = Pdf::loadView('pdf.admit-cards', [
            'exam' => $exam,
            'class' => $class,
            'institution' => auth()->user()->institution,
            'students' => $students,
        ])->setPaper('a4', $validated['orientation'] ?? 'portrait');

        return $pdf->download("admit-cards-{$class->name}-{$exam->name}.pdf");
    }
}
