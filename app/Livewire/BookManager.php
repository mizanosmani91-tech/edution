<?php

namespace App\Livewire;

use App\Models\Book;
use Livewire\Attributes\Validate;
use Livewire\Component;

class BookManager extends Component
{
    public bool $showModal = false;
    public ?string $editingId = null;
    public string $search = '';

    #[Validate('required|string|max:150')]
    public string $title = '';

    public string $author = '';
    public string $isbn = '';
    public string $category = '';

    #[Validate('required|integer|min:1')]
    public string $totalCopies = '1';

    public function openModal(?string $id = null): void
    {
        $this->reset(['title', 'author', 'isbn', 'category']);
        $this->totalCopies = '1';
        $this->editingId = $id;

        if ($id) {
            $book = Book::findOrFail($id);
            $this->title = $book->title;
            $this->author = $book->author ?? '';
            $this->isbn = $book->isbn ?? '';
            $this->category = $book->category ?? '';
            $this->totalCopies = (string) $book->total_copies;
        }

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $book = Book::findOrFail($this->editingId);
            $diff = (int) $this->totalCopies - $book->total_copies;
            $book->update([
                'title' => $this->title,
                'author' => $this->author ?: null,
                'isbn' => $this->isbn ?: null,
                'category' => $this->category ?: null,
                'total_copies' => (int) $this->totalCopies,
                'available_copies' => max(0, $book->available_copies + $diff),
            ]);
        } else {
            Book::create([
                'title' => $this->title,
                'author' => $this->author ?: null,
                'isbn' => $this->isbn ?: null,
                'category' => $this->category ?: null,
                'total_copies' => (int) $this->totalCopies,
                'available_copies' => (int) $this->totalCopies,
            ]);
        }

        $this->showModal = false;
    }

    public function delete(string $id): void
    {
        Book::findOrFail($id)->delete();
    }

    public function render()
    {
        $books = Book::when($this->search, fn ($q) => $q->where('title', 'like', '%'.$this->search.'%'))
            ->orderBy('title')
            ->get();

        return view('livewire.book-manager', [
            'books' => $books,
        ])->layout('components.layouts.app', ['title' => 'বই তালিকা']);
    }
}
