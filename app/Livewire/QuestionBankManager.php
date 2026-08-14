<?php

namespace App\Livewire;

use App\Models\QuestionBankItem;
use App\Models\SchoolClass;
use App\Models\Subject;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class QuestionBankManager extends Component
{
    use WithPagination;

    public ?string $classFilter = null;
    public ?string $subjectFilter = null;

    public bool $showModal = false;
    public ?string $editingId = null;

    public ?string $classId = null;
    public ?string $subjectId = null;

    #[Validate('required|string')]
    public string $questionType = 'short';

    #[Validate('required|string')]
    public string $difficulty = 'medium';

    #[Validate('required|string|min:3')]
    public string $questionText = '';

    public array $mcqOptions = ['', '', '', ''];
    public string $correctAnswer = '';

    #[Validate('required|integer|min:1|max:100')]
    public int $marks = 1;

    public function openModal(): void
    {
        $this->reset(['editingId', 'classId', 'subjectId', 'questionText', 'correctAnswer']);
        $this->questionType = 'short';
        $this->difficulty = 'medium';
        $this->marks = 1;
        $this->mcqOptions = ['', '', '', ''];
        $this->showModal = true;
    }

    public function edit(string $id): void
    {
        $q = QuestionBankItem::findOrFail($id);
        $this->editingId = $id;
        $this->classId = $q->class_id;
        $this->subjectId = $q->subject_id;
        $this->questionType = $q->question_type;
        $this->difficulty = $q->difficulty;
        $this->questionText = $q->question_text;
        $this->mcqOptions = $q->options ?? ['', '', '', ''];
        $this->correctAnswer = $q->correct_answer ?? '';
        $this->marks = $q->marks;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        QuestionBankItem::updateOrCreate(
            ['id' => $this->editingId],
            [
                'class_id' => $this->classId ?: null,
                'subject_id' => $this->subjectId ?: null,
                'question_type' => $this->questionType,
                'difficulty' => $this->difficulty,
                'question_text' => $this->questionText,
                'options' => $this->questionType === 'mcq' ? array_values(array_filter($this->mcqOptions)) : null,
                'correct_answer' => $this->correctAnswer ?: null,
                'marks' => $this->marks,
            ]
        );

        $this->showModal = false;
    }

    public function delete(string $id): void
    {
        QuestionBankItem::findOrFail($id)->delete();
    }

    public function render()
    {
        $questions = QuestionBankItem::with(['schoolClass', 'subject'])
            ->when($this->classFilter, fn ($q) => $q->where('class_id', $this->classFilter))
            ->when($this->subjectFilter, fn ($q) => $q->where('subject_id', $this->subjectFilter))
            ->latest()
            ->paginate(15);

        return view('livewire.question-bank-manager', [
            'questions' => $questions,
            'classes' => SchoolClass::orderBy('display_order')->get(),
            'subjects' => Subject::orderBy('name')->get(),
        ])->layout('components.layouts.app', ['title' => 'প্রশ্ন ব্যাংক']);
    }
}
