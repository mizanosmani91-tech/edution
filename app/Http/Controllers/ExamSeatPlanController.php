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
        $exam = $this->resolveExam($request);
        $rooms = $this->roomsWithAssignments($exam);

        $pdf = Pdf::loadView('pdf.exam-seat-plan', [
            'exam' => $exam,
            'institution' => auth()->user()->institution,
            'rooms' => $rooms,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("seat-plan-{$exam->name}.pdf");
    }

    /**
     * পরীক্ষার হলে উপস্থিতি/অনুপস্থিতি শীট — প্রতিটা হলের জন্য আলাদা পাতা,
     * পরীক্ষার্থীর স্বাক্ষর নেওয়ার কলাম সহ (invigilator attendance sheet)।
     */
    public function attendance(Request $request)
    {
        $exam = $this->resolveExam($request);
        $rooms = $this->roomsWithAssignments($exam);

        $pdf = Pdf::loadView('pdf.exam-attendance-sheet', [
            'exam' => $exam,
            'institution' => auth()->user()->institution,
            'rooms' => $rooms,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("attendance-sheet-{$exam->name}.pdf");
    }

    /**
     * হল-ওয়াইজ দায়িত্বরত শিক্ষকদের তালিকা — একই পাতায় সব হল, স্বাক্ষর
     * কলাম সহ (হলের বাইরে/অফিসে টাঙানোর জন্য একটাই পাতা যথেষ্ট)।
     */
    public function hallDuty(Request $request)
    {
        $exam = $this->resolveExam($request);

        $rooms = ExamSeatPlan::where('exam_id', $exam->id)
            ->orderBy('display_order')
            ->withCount('assignments')
            ->with('assignedTeacher')
            ->get();

        $pdf = Pdf::loadView('pdf.exam-hall-duty', [
            'exam' => $exam,
            'institution' => auth()->user()->institution,
            'rooms' => $rooms,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("hall-duty-{$exam->name}.pdf");
    }

    private function resolveExam(Request $request): Exam
    {
        $validated = $request->validate([
            'exam_id' => ['required', 'uuid', Rule::exists('exams', 'id')->where('institution_id', app('tenant.institution_id'))],
        ]);

        return Exam::findOrFail($validated['exam_id']);
    }

    private function roomsWithAssignments(Exam $exam)
    {
        return ExamSeatPlan::where('exam_id', $exam->id)
            ->orderBy('display_order')
            ->with(['assignments' => fn ($q) => $q->orderBy('seat_no'), 'assignments.student.schoolClass', 'assignments.student.section', 'assignedTeacher'])
            ->get();
    }
}
