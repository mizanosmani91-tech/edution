<?php

namespace App\Http\Controllers;

use App\Models\QuestionPaper;
use Barryvdh\DomPDF\Facade\Pdf;

class QuestionPaperController extends Controller
{
    /**
     * প্রশ্নপত্র PDF — একই কনটেন্ট পাতায় দুইবার (পাশাপাশি) ছাপা হয়, যাতে
     * একটা A4 শীট প্রিন্ট করে মাঝখান থেকে কেটে দুইটা প্রশ্নপত্র পাওয়া যায়
     * (বাংলাদেশি মাদরাসা/স্কুলে কমন প্র্যাকটিস, কাগজ সাশ্রয়ের জন্য)।
     */
    public function print(QuestionPaper $questionPaper)
    {
        $questionPaper->load(['exam', 'schoolClass', 'subject', 'items']);

        $user = auth()->user();
        abort_unless($user->role === 'admin' || $questionPaper->created_by === $user->id, 403);

        $pdf = Pdf::loadView('pdf.question-paper', [
            'paper' => $questionPaper,
            'institution' => $user->institution,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("question-paper-{$questionPaper->subject->name}-{$questionPaper->schoolClass->name}.pdf");
    }
}
