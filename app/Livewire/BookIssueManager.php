<?php

namespace App\Livewire;

use App\Models\Book;
use App\Models\BookIssue;
use App\Models\Student;
use Livewire\Attributes\Validate;
use Livewire\Component;

class BookIssueManager extends Component
{
    public string $tab = 'issued'; // issued / overdue / returned

    public bool $showModal = false;

    #[Validate('required|exists:books,id')]
    public string $bookId = '';

    #[Validate('required|exists:students,id')]
    public string $studentId = '';

    #[Validate('required|date')]
    public string $dueDate = '';

    public function openModal(): void
    {
        $this->reset(['bookId', 'studentId']);
        $this->dueDate = now()->addDays(14)->toDateString();
        $this->showModal = true;
    }

    public function issue(): void
    {
        $this->validate();

        $book = Book::findOrFail($this->bookId);

        if ($book->available_copies < 1) {
            $this->addError('bookId', 'এই বইয়ের কোনো কপি এই মুহূর্তে উপলব্ধ নেই।');
            return;
        }

        BookIssue::create([
            'book_id' => $this->bookId,
            'student_id' => $this->studentId,
            'issued_at' => now()->toDateString(),
            'due_date' => $this->dueDate,
            'status' => 'issued',
        ]);

        $book->decrement('available_copies');

        $this->showModal = false;
    }

    public function markReturned(string $id): void
    {
        $issue = BookIssue::findOrFail($id);

        $fine = 0;
        if (now()->toDateString() > $issue->due_date->toDateString()) {
            $fine = now()->diffInDays($issue->due_date) * 5; // ৳৫ প্রতি দিন বিলম্বে — সাধারণ ডিফল্ট, প্রতিষ্ঠানভেদে পরে সেটিংসে কনফিগারযোগ্য করা যাবে
        }

        $issue->update([
            'returned_at' => now()->toDateString(),
            'status' => 'returned',
            'fine_amount' => $fine,
        ]);

        $issue->book->increment('available_copies');
    }

    public function render()
    {
        $query = BookIssue::with(['book', 'student']);

        if ($this->tab === 'issued') {
            $query->where('status', 'issued')->where('due_date', '>=', now()->toDateString());
        } elseif ($this->tab === 'overdue') {
            $query->where('status', 'issued')->where('due_date', '<', now()->toDateString());
        } else {
            $query->where('status', 'returned');
        }

        return view('livewire.book-issue-manager', [
            'issues' => $query->latest('issued_at')->limit(100)->get(),
            'books' => Book::where('available_copies', '>', 0)->orderBy('title')->get(),
            'students' => Student::orderBy('name')->limit(300)->get(),
        ])->layout('components.layouts.app', ['title' => 'ইস্যু ও রিটার্ন']);
    }
}
