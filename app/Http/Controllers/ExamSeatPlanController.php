<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamSeatPlan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExamSeatPlanController extends Controller
{
    /**
     * রুম-ভিত্তিক সিট প্ল্যান — প্রতিটা রুমের জন্য আলাদা পাতা, ছাত্রদের
     * সিট নং অনুযায়ী তালিকা (দরজায় লাগানোর জন্য প্রিন্ট-উপযোগী)।
     */
    public function print(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => ['required', 'uuid', Rule::exists('exams', 'id')->where('institution_id', app('tenant.institution_id'))],
        ]);

        $exam = Exam::findOrFail($validated['exam_id']);
        $institution = auth()->user()->institution;

        $rooms = ExamSeatPlan::where('exam_id', $exam->id)
            ->orderBy('display_order')
            ->with(['assignments' => fn ($q) => $q->orderBy('seat_no'), 'assignments.student.schoolClass', 'assignments.student.section'])
            ->get();

        $pdf = Pdf::loadView('pdf.exam-seat-plan', [
            'exam' => $exam,
            'institution' => $institution,
            'rooms' => $rooms,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("seat-plan-{$exam->name}.pdf");
    }
}
