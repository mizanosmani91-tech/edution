<?php

namespace App\Livewire;

use App\Models\Exam;
use App\Models\QuestionPaper;
use App\Models\SchoolClass;
use App\Models\Subject;
use Livewire\Component;

/**
 * QuestionPaperBuilder
 *
 * শিক্ষক নিজের বিষয়ের প্রশ্নপত্র লিখে খসড়া (draft) হিসেবে সেভ করেন, তারপর
 * "রিভিউয়ের জন্য পাঠান" (submitted) করেন — এডমিন সেটা দেখে "অনুমোদন" (approved)
 * দিলে প্রিন্ট করা যায়। এডমিন সব শিক্ষকের সব প্রশ্নপত্র দেখেন, শিক্ষক শুধু
 * নিজেরটা দেখেন — role-ভিত্তিক ফিল্টার query-লেভেলেই করা হচ্ছে।
 */
class QuestionPaperBuilder extends Component
{
    public bool $showModal = false;
    public ?string $editingId = null;

    public string $examId = '';
    public string $classId = '';
    public string $subjectId = '';
    public string $title = '';
    public string $durationText = '১ ঘন্টা';
    public string $fullMarks = '20';

    /** @var array<int, array{heading: ?string, marks: string, content: string}> */
    public array $items = [];

    public function mount(): void
    {
        $latest = Exam::orderByDesc('start_date')->first();
        $this->examId = $latest?->id ?? '';
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'classId', 'subjectId', 'title', 'items']);
        $this->durationText = '১ ঘন্টা';
        $this->fullMarks = '20';
        $this->items = [['heading' => '', 'marks' => '5', 'content' => '']];
        $this->showModal = true;
    }

    public function openEdit(string $id): void
    {
        $paper = $this->authorizedPaper($id);

        $this->editingId = $paper->id;
        $this->examId = $paper->exam_id;
        $this->classId = $paper->class_id;
        $this->subjectId = $paper->subject_id;
        $this->title = (string) $paper->title;
        $this->durationText = $paper->duration_text;
        $this->fullMarks = (string) $paper->full_marks;
        $this->items = $paper->items->map(fn ($i) => [
            'heading' => $i->heading,
            'marks' => (string) $i->marks,
            'content' => $i->content,
        ])->toArray();

        if (empty($this->items)) {
            $this->items = [['heading' => '', 'marks' => '5', 'content' => '']];
        }

        $this->showModal = true;
    }

    public function addItem(): void
    {
        $this->items[] = ['heading' => '', 'marks' => '5', 'content' => ''];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save(bool $submit = false): void
    {
        $this->validate([
            'examId' => ['required', 'uuid'],
            'classId' => ['required', 'uuid'],
            'subjectId' => ['required', 'uuid'],
            'durationText' => ['required', 'string', 'max:50'],
            'fullMarks' => ['required', 'numeric', 'min:1'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.content' => ['required', 'string'],
            'items.*.marks' => ['required', 'numeric', 'min:0'],
        ]);

        $data = [
            'exam_id' => $this->examId,
            'class_id' => $this->classId,
            'subject_id' => $this->subjectId,
            'title' => $this->title ?: null,
            'duration_text' => $this->durationText,
            'full_marks' => $this->fullMarks,
            'created_by' => auth()->id(),
        ];

        if ($this->editingId) {
            $paper = $this->authorizedPaper($this->editingId);
            $paper->update($data);
            $paper->items()->delete();
        } else {
            $paper = QuestionPaper::create($data + ['status' => 'draft']);
        }

        foreach ($this->items as $idx => $item) {
            $paper->items()->create([
                'order_no' => $idx + 1,
                'heading' => $item['heading'] ?: null,
                'marks' => $item['marks'],
                'content' => $item['content'],
            ]);
        }

        if ($submit && $paper->status === 'draft') {
            $paper->update(['status' => 'submitted', 'submitted_at' => now()]);
        }

        $this->showModal = false;
        $this->dispatch('toast', message: $submit ? 'প্রশ্নপত্র রিভিউয়ের জন্য পাঠানো হয়েছে' : 'খসড়া সেভ করা হয়েছে');
    }

    public function approve(string $id): void
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        QuestionPaper::findOrFail($id)->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        $this->dispatch('toast', message: 'প্রশ্নপত্র অনুমোদন করা হয়েছে — এখন প্রিন্ট করা যাবে');
    }

    public function sendBackToDraft(string $id): void
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        QuestionPaper::findOrFail($id)->update(['status' => 'draft', 'submitted_at' => null]);
        $this->dispatch('toast', message: 'শিক্ষকের কাছে ফেরত পাঠানো হয়েছে');
    }

    public function delete(string $id): void
    {
        $this->authorizedPaper($id)->delete();
        $this->dispatch('toast', message: 'প্রশ্নপত্র মুছে ফেলা হয়েছে');
    }

    private function authorizedPaper(string $id): QuestionPaper
    {
        $paper = QuestionPaper::with('items')->findOrFail($id);
        $isAdmin = auth()->user()->role === 'admin';
        abort_unless($isAdmin || $paper->created_by === auth()->id(), 403);

        return $paper;
    }

    public function render()
    {
        $isAdmin = auth()->user()->role === 'admin';

        $papers = QuestionPaper::with(['exam', 'schoolClass', 'subject', 'creator'])
            ->when(! $isAdmin, fn ($q) => $q->where('created_by', auth()->id()))
            ->latest()
            ->get();

        return view('livewire.question-paper-builder', [
            'papers' => $papers,
            'isAdmin' => $isAdmin,
            'exams' => Exam::orderByDesc('start_date')->get(),
            'classes' => SchoolClass::orderBy('display_order')->get(),
            'subjects' => Subject::orderBy('name')->get(),
        ])->layout('components.layouts.app', ['title' => 'প্রশ্নপত্র']);
    }
}
