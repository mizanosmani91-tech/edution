<?php

namespace App\Livewire;

use App\Models\ClassDiaryEntry;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ClassDiaryManager extends Component
{
    use WithPagination;

    public string $dateFilter = '';
    public ?string $classFilter = null;

    public bool $showModal = false;
    public ?string $editingId = null;

    public string $classId = '';
    public ?string $sectionId = null;
    public ?string $subjectId = null;
    public ?string $teacherId = null;
    public string $entryDate = '';
    public string $topic = '';
    public string $classwork = '';
    public string $homework = '';
    public string $remarks = '';

    public function mount(): void
    {
        $this->dateFilter = now()->toDateString();
    }

    private function user()
    {
        return auth()->user();
    }

    private function isTeacher(): bool
    {
        return $this->user()->role === 'teacher';
    }

    /** শিক্ষক ও অ্যাডমিন ছাড়া কেউ ডায়েরি লিখতে/দেখতে পারবে না। */
    private function authorizeAccess(): void
    {
        abort_unless(in_array($this->user()->role, ['admin', 'teacher'], true), 403);
    }

    /** শিক্ষক শুধু নিজের ডায়েরি এন্ট্রিতে হাত দিতে পারবেন। */
    private function findManageable(string $id): ClassDiaryEntry
    {
        $this->authorizeAccess();

        $entry = ClassDiaryEntry::findOrFail($id);

        if ($this->isTeacher()) {
            abort_unless($entry->teacher_id === $this->user()->teacher_id, 403);
        }

        return $entry;
    }

    public function updatedDateFilter(): void
    {
        $this->resetPage();
    }

    public function updatedClassFilter(): void
    {
        $this->resetPage();
    }

    public function updatedClassId(): void
    {
        $this->sectionId = null; // শ্রেণি বদলালে সেকশন নতুন করে বাছতে হবে
    }

    public function previousDay(): void
    {
        $this->dateFilter = \Carbon\Carbon::parse($this->dateFilter ?: now())->subDay()->toDateString();
        $this->resetPage();
    }

    public function nextDay(): void
    {
        $this->dateFilter = \Carbon\Carbon::parse($this->dateFilter ?: now())->addDay()->toDateString();
        $this->resetPage();
    }

    public function openModal(): void
    {
        $this->authorizeAccess();

        $this->resetValidation();
        $this->reset(['editingId', 'classId', 'sectionId', 'subjectId', 'teacherId', 'topic', 'classwork', 'homework', 'remarks']);
        $this->entryDate = $this->dateFilter ?: now()->toDateString();
        $this->showModal = true;
    }

    public function edit(string $id): void
    {
        $entry = $this->findManageable($id);

        $this->resetValidation();
        $this->editingId = $entry->id;
        $this->classId = $entry->class_id;
        $this->sectionId = $entry->section_id;
        $this->subjectId = $entry->subject_id;
        $this->teacherId = $entry->teacher_id;
        $this->entryDate = $entry->entry_date->toDateString();
        $this->topic = $entry->topic;
        $this->classwork = (string) $entry->classwork;
        $this->homework = (string) $entry->homework;
        $this->remarks = (string) $entry->remarks;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->authorizeAccess();

        $tenant = app('tenant.institution_id');

        // ⚠️ exists রুল global scope বাইপাস করে — তাই institution_id হাতে-কলমে দেওয়া হলো
        $this->validate([
            'classId' => ['required', Rule::exists('classes', 'id')->where('institution_id', $tenant)],
            'sectionId' => ['nullable', Rule::exists('sections', 'id')->where('institution_id', $tenant)->where('class_id', $this->classId)],
            'subjectId' => ['nullable', Rule::exists('subjects', 'id')->where('institution_id', $tenant)],
            'teacherId' => ['nullable', Rule::exists('teachers', 'id')->where('institution_id', $tenant)],
            'entryDate' => 'required|date',
            'topic' => 'required|string|min:2|max:200',
            'classwork' => 'nullable|string|max:5000',
            'homework' => 'nullable|string|max:5000',
            'remarks' => 'nullable|string|max:2000',
        ], [], [
            'classId' => 'শ্রেণি',
            'entryDate' => 'তারিখ',
            'topic' => 'আজকের বিষয়',
        ]);

        // শিক্ষক শুধু নিজের নামেই লিখতে পারবেন
        $teacherId = $this->isTeacher() ? $this->user()->teacher_id : ($this->teacherId ?: null);

        $data = [
            'class_id' => $this->classId,
            'section_id' => $this->sectionId ?: null,
            'subject_id' => $this->subjectId ?: null,
            'teacher_id' => $teacherId,
            'entry_date' => $this->entryDate,
            'topic' => $this->topic,
            'classwork' => $this->classwork ?: null,
            'homework' => $this->homework ?: null,
            'remarks' => $this->remarks ?: null,
        ];

        if ($this->editingId) {
            $this->findManageable($this->editingId)->update($data);
        } else {
            ClassDiaryEntry::create($data);
        }

        $this->dateFilter = $this->entryDate;
        $this->showModal = false;
    }

    public function delete(string $id): void
    {
        $this->findManageable($id)->delete();
        $this->showModal = false;
    }

    public function render()
    {
        $this->authorizeAccess();

        $entries = ClassDiaryEntry::with(['schoolClass', 'section', 'subject', 'teacher'])
            ->when($this->isTeacher(), fn ($q) => $q->where('teacher_id', $this->user()->teacher_id))
            ->when($this->dateFilter, fn ($q) => $q->whereDate('entry_date', $this->dateFilter))
            ->when($this->classFilter, fn ($q) => $q->where('class_id', $this->classFilter))
            ->latest('created_at')
            ->paginate(15);

        return view('livewire.class-diary-manager', [
            'entries' => $entries,
            'classes' => SchoolClass::orderBy('display_order')->get(),
            'sections' => $this->classId ? Section::where('class_id', $this->classId)->orderBy('name')->get() : collect(),
            'subjects' => Subject::orderBy('name')->get(),
            'teachers' => $this->isTeacher() ? collect() : Teacher::where('status', 'active')->orderBy('name')->get(),
            'isTeacher' => $this->isTeacher(),
        ])->layout('components.layouts.app', ['title' => 'ক্লাস ডায়েরি']);
    }
}
