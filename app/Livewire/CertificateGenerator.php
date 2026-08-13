<?php

namespace App\Livewire;

use App\Models\Certificate;
use App\Models\SchoolClass;
use App\Models\Student;
use Livewire\Component;

class CertificateGenerator extends Component
{
    public string $type = 'transfer';

    public string $search = '';
    public ?string $classId = null;
    public ?string $selectedStudentId = null;

    public string $reason = '';
    public string $remarks = '';

    public ?string $generatedId = null;

    public function mount(string $type = 'transfer'): void
    {
        $this->type = $type;
    }

    public function selectStudent(string $id): void
    {
        $this->selectedStudentId = $id;
        $this->generatedId = null;
        $this->reason = '';
        $this->remarks = '';
    }

    public function getStudentsProperty()
    {
        return Student::where('status', 'active')
            ->when($this->classId, fn ($q) => $q->where('class_id', $this->classId))
            ->when($this->search, fn ($q) => $q->where(function ($q2) {
                $q2->where('name', 'ilike', '%'.$this->search.'%')
                    ->orWhere('student_id_no', 'ilike', '%'.$this->search.'%');
            }))
            ->orderBy('name')
            ->limit(30)
            ->get();
    }

    public function getSelectedStudentProperty()
    {
        return $this->selectedStudentId ? Student::with(['schoolClass', 'section'])->find($this->selectedStudentId) : null;
    }

    public function generate(): void
    {
        if (! $this->selectedStudentId) {
            return;
        }

        $this->validate([
            'reason' => $this->type === 'transfer' ? 'required|string|min:3' : 'nullable|string',
        ]);

        $seq = Certificate::where('type', $this->type)->count() + 1;
        $prefix = $this->type === 'character' ? 'CC' : 'TC';
        $certificateNo = $prefix.'-'.now()->format('Y').'-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

        $cert = Certificate::create([
            'student_id' => $this->selectedStudentId,
            'type' => $this->type,
            'certificate_no' => $certificateNo,
            'issue_date' => now()->toDateString(),
            'reason' => $this->reason ?: null,
            'remarks' => $this->remarks ?: null,
            'issued_by' => auth()->id(),
        ]);

        $this->generatedId = $cert->id;
    }

    public function render()
    {
        return view('livewire.certificate-generator', [
            'classes' => SchoolClass::orderBy('display_order')->get(),
            'students' => $this->students,
            'selected' => $this->selectedStudent,
            'generated' => $this->generatedId ? Certificate::with('student.schoolClass', 'student.section')->find($this->generatedId) : null,
            'history' => Certificate::with('student')->where('type', $this->type)->latest()->limit(15)->get(),
        ])->layout('components.layouts.app', [
            'title' => $this->type === 'character' ? 'চারিত্রিক সনদপত্র' : 'ছাড়পত্র (Transfer Certificate)',
        ]);
    }
}
