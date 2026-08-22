<?php

namespace App\Livewire;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\QuestionBankItem;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * QuizManager — অ্যাডমিন/শিক্ষক এখান থেকে Question Bank এর mcq প্রশ্ন বেছে
 * অনলাইন কুইজ বানান। শিক্ষার্থী তার পোর্টাল থেকে নির্দিষ্ট সময়সীমার মধ্যে
 * দিয়ে সাথে সাথেই ফলাফল পায় — MCQ বলে ম্যানুয়াল মার্কিং লাগে না।
 */
class QuizManager extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public bool $showResultsModal = false;
    public ?string $editingId = null;
    public ?string $viewingResultsId = null;

    #[Validate('required|string|min:3')]
    public string $title = '';

    #[Validate('required|exists:classes,id')]
    public ?string $classId = null;

    public ?string $subjectId = null;

    #[Validate('required|integer|min:1|max:180')]
    public int $durationMinutes = 20;

    public string $startsAt = '';
    public string $endsAt = '';
    public bool $shuffleQuestions = true;

    public array $selectedQuestionIds = [];
    public array $questionMarks = []; // question_bank_item_id => marks

    public function openModal(): void
    {
        $this->reset(['editingId', 'title', 'classId', 'subjectId', 'startsAt', 'endsAt', 'selectedQuestionIds', 'questionMarks']);
        $this->durationMinutes = 20;
        $this->shuffleQuestions = true;
        $this->showModal = true;
    }

    public function edit(string $id): void
    {
        $quiz = Quiz::with('questions')->findOrFail($id);

        $this->editingId = $id;
        $this->title = $quiz->title;
        $this->classId = $quiz->class_id;
        $this->subjectId = $quiz->subject_id;
        $this->durationMinutes = $quiz->duration_minutes;
        $this->startsAt = $quiz->starts_at?->format('Y-m-d\TH:i') ?? '';
        $this->endsAt = $quiz->ends_at?->format('Y-m-d\TH:i') ?? '';
        $this->shuffleQuestions = $quiz->shuffle_questions;

        $this->selectedQuestionIds = $quiz->questions->pluck('question_bank_item_id')->map(fn ($id) => (string) $id)->toArray();
        $this->questionMarks = $quiz->questions->pluck('marks', 'question_bank_item_id')->toArray();

        $this->showModal = true;
    }

    public function availableQuestions()
    {
        if (! $this->classId) {
            return collect();
        }

        return QuestionBankItem::where('question_type', 'mcq')
            ->where('class_id', $this->classId)
            ->when($this->subjectId, fn ($q) => $q->where('subject_id', $this->subjectId))
            ->whereNotNull('correct_answer')
            ->with('subject')
            ->get();
    }

    public function save(): void
    {
        $this->validate();

        if (empty($this->selectedQuestionIds)) {
            $this->addError('selectedQuestionIds', 'কমপক্ষে একটা প্রশ্ন নির্বাচন করুন।');

            return;
        }

        DB::transaction(function () {
            $quiz = Quiz::updateOrCreate(
                ['id' => $this->editingId],
                [
                    'title' => $this->title,
                    'class_id' => $this->classId,
                    'subject_id' => $this->subjectId ?: null,
                    'created_by' => auth()->id(),
                    'duration_minutes' => $this->durationMinutes,
                    'starts_at' => $this->startsAt ?: null,
                    'ends_at' => $this->endsAt ?: null,
                    'shuffle_questions' => $this->shuffleQuestions,
                ]
            );

            // পুরনো প্রশ্ন-লিস্ট মুছে নতুন করে বসানো — এডিটের সময় সবচেয়ে সহজ পথ
            QuizQuestion::where('quiz_id', $quiz->id)->delete();

            foreach (array_values($this->selectedQuestionIds) as $i => $qbId) {
                QuizQuestion::create([
                    'quiz_id' => $quiz->id,
                    'question_bank_item_id' => $qbId,
                    'marks' => (int) ($this->questionMarks[$qbId] ?? 1),
                    'order_no' => $i,
                ]);
            }
        });

        $this->showModal = false;
        $this->dispatch('toast', message: 'কুইজ সংরক্ষণ করা হয়েছে।');
    }

    public function togglePublish(string $id): void
    {
        $quiz = Quiz::findOrFail($id);

        if (! $quiz->is_published && $quiz->questions()->count() === 0) {
            $this->dispatch('toast', message: 'প্রশ্ন ছাড়া কুইজ প্রকাশ করা যাবে না।');

            return;
        }

        $quiz->update(['is_published' => ! $quiz->is_published]);
    }

    public function delete(string $id): void
    {
        Quiz::findOrFail($id)->delete();
        $this->dispatch('toast', message: 'কুইজ মুছে ফেলা হয়েছে।');
    }

    public function viewResults(string $id): void
    {
        $this->viewingResultsId = $id;
        $this->showResultsModal = true;
    }

    public function render()
    {
        $user = auth()->user();

        $quizzes = Quiz::with(['schoolClass', 'subject'])
            ->when($user->role === 'teacher', fn ($q) => $q->where('created_by', $user->id))
            ->latest()
            ->paginate(15);

        $resultsAttempts = collect();
        $resultsQuiz = null;

        if ($this->viewingResultsId) {
            $resultsQuiz = Quiz::find($this->viewingResultsId);
            $resultsAttempts = QuizAttempt::with('student')
                ->where('quiz_id', $this->viewingResultsId)
                ->where('status', 'submitted')
                ->orderByDesc('score')
                ->get();
        }

        return view('livewire.quiz-manager', [
            'quizzes' => $quizzes,
            'classes' => SchoolClass::orderBy('display_order')->get(),
            'subjects' => Subject::orderBy('name')->get(),
            'availableQuestions' => $this->availableQuestions(),
            'resultsQuiz' => $resultsQuiz,
            'resultsAttempts' => $resultsAttempts,
            'hasAnyMcqQuestions' => QuestionBankItem::where('question_type', 'mcq')->exists(),
        ])->layout('components.layouts.app', ['title' => 'অনলাইন কুইজ']);
    }
}
