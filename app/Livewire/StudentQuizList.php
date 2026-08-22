<?php

namespace App\Livewire;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Livewire\Component;

/**
 * StudentQuizList — শিক্ষার্থীর নিজের ক্লাসের জন্য প্রকাশিত সব কুইজের তালিকা,
 * সাথে প্রতিটার স্ট্যাটাস (এখনো দেয়নি / চলছে / জমা দিয়েছে + স্কোর)।
 */
class StudentQuizList extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()->student_id, 403, 'এই একাউন্ট কোনো ছাত্রের সাথে যুক্ত না।');
    }

    public function render()
    {
        $student = auth()->user()->studentProfile;
        $studentId = auth()->user()->student_id;

        $quizzes = Quiz::with(['subject', 'schoolClass'])
            ->where('class_id', $student->class_id)
            ->where('is_published', true)
            ->latest()
            ->get();

        $attempts = QuizAttempt::where('student_id', $studentId)
            ->whereIn('quiz_id', $quizzes->pluck('id'))
            ->get()
            ->keyBy('quiz_id');

        $rows = $quizzes->map(function ($quiz) use ($attempts) {
            $attempt = $attempts->get($quiz->id);

            return [
                'quiz' => $quiz,
                'attempt' => $attempt,
                'is_open' => $quiz->isOpenNow(),
                'status' => $attempt
                    ? ($attempt->status === 'submitted' ? 'submitted' : 'in_progress')
                    : 'not_started',
            ];
        });

        return view('livewire.student-quiz-list', ['rows' => $rows])->layout('components.layouts.app', ['title' => 'অনলাইন কুইজ']);
    }
}
