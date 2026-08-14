<?php

namespace App\Livewire;

use App\Models\AdmissionApplication;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class AdmissionApplicationManager extends Component
{
    use WithPagination;

    public string $view = 'all'; // all / test / waiting

    public string $statusFilter = '';
    public ?string $classFilter = null;

    public bool $showModal = false;
    public ?string $editingId = null;

    #[Validate('required|string|max:100')]
    public string $applicantName = '';

    public string $guardianName = '';

    #[Validate('required|string|max:20')]
    public string $guardianPhone = '';

    public string $dateOfBirth = '';
    public string $gender = '';
    public string $applyingClassId = '';
    public string $previousSchool = '';
    public string $address = '';

    public bool $showTestModal = false;
    public ?string $testingId = null;
    public string $testDate = '';
    public string $testTime = '';
    public string $testScore = '';
    public string $interviewNotes = '';

    public function mount(string $view = 'all'): void
    {
        $this->view = $view;
    }

    public function openModal(): void
    {
        $this->reset(['editingId', 'applicantName', 'guardianName', 'guardianPhone', 'dateOfBirth', 'gender', 'applyingClassId', 'previousSchool', 'address']);
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        AdmissionApplication::create([
            'applicant_name' => $this->applicantName,
            'guardian_name' => $this->guardianName ?: null,
            'guardian_phone' => $this->guardianPhone,
            'date_of_birth' => $this->dateOfBirth ?: null,
            'gender' => $this->gender ?: null,
            'applying_class_id' => $this->applyingClassId ?: null,
            'previous_school' => $this->previousSchool ?: null,
            'address' => $this->address ?: null,
            'status' => 'pending',
        ]);

        $this->showModal = false;
    }

    public function setStatus(string $id, string $status): void
    {
        AdmissionApplication::findOrFail($id)->update(['status' => $status]);
    }

    public function openTestModal(string $id): void
    {
        $app = AdmissionApplication::findOrFail($id);
        $this->testingId = $id;
        $this->testDate = $app->test_date?->toDateString() ?? '';
        $this->testTime = $app->test_time ?? '';
        $this->testScore = $app->test_score !== null ? (string) $app->test_score : '';
        $this->interviewNotes = $app->interview_notes ?? '';
        $this->showTestModal = true;
    }

    public function saveTest(): void
    {
        $app = AdmissionApplication::findOrFail($this->testingId);
        $app->update([
            'test_date' => $this->testDate ?: null,
            'test_time' => $this->testTime ?: null,
            'test_score' => $this->testScore !== '' ? $this->testScore : null,
            'interview_notes' => $this->interviewNotes ?: null,
            'status' => $app->status === 'pending' ? 'test_scheduled' : $app->status,
        ]);

        $this->showTestModal = false;
    }

    public function convertToStudent(string $id): void
    {
        $app = AdmissionApplication::findOrFail($id);

        if ($app->converted_student_id || ! $app->applying_class_id) {
            return;
        }

        $institutionId = app('tenant.institution_id');
        $institution = Institution::find($institutionId);
        $studentIdNo = strtoupper(Str::substr($institution->slug, 0, 3)).'-'.now()->year.'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);

        $student = Student::create([
            'name' => $app->applicant_name,
            'student_id_no' => $studentIdNo,
            'class_id' => $app->applying_class_id,
            'guardian_phone' => $app->guardian_phone,
            'date_of_birth' => $app->date_of_birth,
            'gender' => $app->gender,
            'previous_school' => $app->previous_school,
            'admission_type' => 'new',
        ]);

        $app->update(['status' => 'accepted', 'converted_student_id' => $student->id]);
    }

    public function delete(string $id): void
    {
        AdmissionApplication::findOrFail($id)->delete();
    }

    public function render()
    {
        $query = AdmissionApplication::with(['applyingClass', 'convertedStudent'])->latest();

        if ($this->view === 'waiting') {
            $query->where('status', 'waiting');
        } elseif ($this->view === 'test') {
            $query->whereIn('status', ['pending', 'test_scheduled', 'shortlisted']);
        } elseif ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->classFilter) {
            $query->where('applying_class_id', $this->classFilter);
        }

        return view('livewire.admission-application-manager', [
            'applications' => $query->paginate(20),
            'classes' => SchoolClass::orderBy('display_order')->get(),
        ])->layout('components.layouts.app', [
            'title' => match ($this->view) {
                'test' => 'ভর্তি পরীক্ষা / ইন্টারভিউ',
                'waiting' => 'Waiting List',
                default => 'ভর্তি আবেদন তালিকা',
            },
        ]);
    }
}
