<?php

namespace App\Livewire;

use App\Models\Exam;
use App\Models\SchoolClass;
use Livewire\Component;

/**
 * ExamDocumentCenter — পরীক্ষার সব প্রিন্টযোগ্য ডকুমেন্টের (সিট প্ল্যান,
 * উপস্থিতি শীট, হল ডিউটি, উত্তরপত্র বন্টন, এডমিট কার্ড, মার্কশিট,
 * ট্যাবুলেশন, প্রশ্নপত্র) একটাই কেন্দ্রীয় হাব — প্রতিটার জন্য আলাদা
 * পেজে না গিয়ে এখান থেকেই এক্সাম+ক্লাস বেছে সব প্রিন্ট লিংক পাওয়া যায়।
 */
class ExamDocumentCenter extends Component
{
    public string $examId = '';
    public string $classId = '';

    public function mount(): void
    {
        $latest = Exam::orderByDesc('start_date')->first();
        $this->examId = $latest?->id ?? '';
    }

    public function render()
    {
        return view('livewire.exam-document-center', [
            'exams' => Exam::orderByDesc('start_date')->get(),
            'classes' => SchoolClass::orderBy('display_order')->get(),
        ])->layout('components.layouts.app', ['title' => 'পরীক্ষার ডকুমেন্ট সেন্টার']);
    }
}
