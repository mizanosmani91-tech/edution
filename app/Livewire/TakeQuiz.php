<?php

namespace App\Livewire;

use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * TakeQuiz — শিক্ষার্থী এখানে একটা নির্দিষ্ট কুইজ দেয়। সময়সীমা সার্ভার-সাইডে
 * (started_at + duration_minutes) হিসাব হয়, ক্লায়েন্ট-সাইড টাইমার শুধু UI এর
 * জন্য — সময় শেষ হয়ে গেলে সাবমিট করতে চাইলেও সার্ভার আটকে দেবে না (grace
 * দেওয়া হয়েছে যাতে নেট স্লো হলে উত্তর হারিয়ে না যায়), কিন্তু quiz এর
 * ends_at পার হয়ে গেলে নতুন attempt শুরু করা যাবে না।
 *
 * ⚠️ একবার submit হয়ে গেলে আর বদলানো যায় না — QuizAttempt.status='submitted'
 * চেক করেই সেটা নিশ্চিত করা হয়, শুধু UI লুকিয়ে রাখা না।
 */
class TakeQuiz extends Component
{
    public string $quizId;

    public ?string $attemptId = null;

    public array $answers = []; // quiz_question_id => selected option text

    public bool $submitted = false;

    public function mount(string $quiz): void
    {
        abort_unless(auth()->user()->student_id, 403, 'এই একাউন্ট কোনো ছাত্রের সাথে যুক্ত না।');

        $this->quizId = $quiz;

        $quizModel = Quiz::with('questions.questionBankItem')->findOrFail($quiz);
        $student = auth()->user()->studentProfile;

        abort_unless($quizModel->class_id === $student->class_id, 403, 'এই কুইজ আপনার ক্লাসের জন্য না।');
        abort_unless($quizModel->is_published, 403, 'এই কুইজ এখনো প্রকাশিত হয়নি।');

        $studentId = auth()->user()->student_id;

        $attempt = QuizAttempt::where('quiz_id', $quiz)->where('student_id', $studentId)->first();

        if ($attempt && $attempt->status === 'submitted') {
            $this->attemptId = $attempt->id;
            $this->submitted = true;

            return;
        }

        abort_unless($quizModel->isOpenNow(), 403, 'এই কুইজ এখন খোলা নেই (সময়সীমার বাইরে অথবা এখনো শুরু হয়নি)।');

        if (! $attempt) {
            // ⚠️ দুই ট্যাবে একসাথে খুললে race condition হতে পারে — unique
            // constraint (quiz_id, student_id) থাকায় firstOrCreate নিরাপদ,
            // দ্বিতীয়বার চেষ্টা করলে এক্সেপশন ছোঁড়ার বদলে বিদ্যমান রো-ই পাবে
            try {
                $attempt = QuizAttempt::create([
                    'quiz_id' => $quiz,
                    'student_id' => $studentId,
                    'started_at' => now(),
                    'total_marks' => $quizModel->total_marks,
                    'status' => 'in_progress',
                ]);
            } catch (\Throwable $e) {
                $attempt = QuizAttempt::where('quiz_id', $quiz)->where('student_id', $studentId)->firstOrFail();
            }
        }

        $this->attemptId = $attempt->id;
    }

    public function submit(): void
    {
        if ($this->submitted) {
            return;
        }

        $attempt = QuizAttempt::findOrFail($this->attemptId);

        if ($attempt->status === 'submitted') {
            $this->submitted = true;

            return;
        }

        abort_unless($attempt->student_id === auth()->user()->student_id, 403);

        DB::transaction(function () use ($attempt) {
            $quiz = Quiz::with('questions.questionBankItem')->findOrFail($attempt->quiz_id);

            $totalScore = 0;

            foreach ($quiz->questions as $q) {
                $selected = $this->answers[$q->id] ?? null;
                $correct = $q->questionBankItem?->correct_answer;
                $isCorrect = $selected !== null && trim((string) $selected) === trim((string) $correct);
                $marksAwarded = $isCorrect ? $q->marks : 0;
                $totalScore += $marksAwarded;

                QuizAnswer::updateOrCreate(
                    ['quiz_attempt_id' => $attempt->id, 'quiz_question_id' => $q->id],
                    [
                        'selected_answer' => $selected,
                        'is_correct' => $isCorrect,
                        'marks_awarded' => $marksAwarded,
                    ]
                );
            }

            $attempt->update([
                'submitted_at' => now(),
                'score' => $totalScore,
                'total_marks' => $quiz->total_marks,
                'status' => 'submitted',
            ]);
        });

        $this->submitted = true;
        $this->dispatch('toast', message: 'কুইজ জমা হয়েছে।');
    }

    public function render()
    {
        $quiz = Quiz::with('questions.questionBankItem')->findOrFail($this->quizId);
        $attempt = QuizAttempt::with('answers')->find($this->attemptId);

        $questions = $quiz->questions;
        if ($quiz->shuffle_questions && ! $this->submitted) {
            $questions = $questions->shuffle();
        }

        $secondsRemaining = null;
        if (! $this->submitted && $attempt) {
            $deadline = $attempt->started_at->copy()->addMinutes($quiz->duration_minutes);
            // টাইমস্ট্যাম্প বিয়োগ করে সরাসরি হিসাব — Carbon diffInSeconds()
            // এর সাইন কনভেনশন ভার্সনভেদে ভিন্ন হতে পারে বলে এড়ানো হয়েছে
            $secondsRemaining = max(0, $deadline->getTimestamp() - now()->getTimestamp());
        }

        return view('livewire.take-quiz', [
            'quiz' => $quiz,
            'attempt' => $attempt,
            'questions' => $questions,
            'secondsRemaining' => $secondsRemaining,
        ])->layout('components.layouts.app', ['title' => $quiz->title]);
    }
}
