<?php

namespace App\Livewire;

use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;

class TeacherAdmissionWizard extends Component
{
    public int $currentStep = 1;
    public int $totalSteps = 5;

    // Step 1: ব্যক্তিগত তথ্য
    public string $name = '';
    public string $name_en = '';
    public string $date_of_birth = '';
    public string $gender = '';
    public string $nid = '';
    public string $phone = '';
    public string $email = '';
    public string $emergency_contact = '';
    public string $address = '';

    // Step 2: শিক্ষাগত ও পেশাগত
    public string $education = '';
    public string $passing_institution = '';
    public string $designation = '';
    public string $employee_type = 'permanent';
    public ?int $experience_years = null;
    public string $joining_date = '';
    public string $previous_workplace = '';

    // Step 3: বিষয় ও ক্লাস
    public array $subjects_taught = [];
    public array $assigned_classes = [];

    // Step 4: বেতন ও ব্যাংক
    public ?float $base_salary = null;
    public ?float $house_rent = null;
    public ?float $medical_allowance = null;
    public string $bank_name = '';
    public string $bank_branch = '';
    public string $bank_account = '';
    public string $mobile_banking = '';

    // Step 5
    public ?string $generatedStaffId = null;

    public function goToStep(int $step): void
    {
        if ($step >= 1 && $step <= $this->totalSteps) {
            $this->currentStep = $step;
        }
    }

    public function nextStep(): void
    {
        if ($this->currentStep === 1) {
            $this->validate([
                'name' => 'required|string|max:255',
                'date_of_birth' => 'required|date',
                'gender' => 'required',
                'phone' => 'required|string|max:20',
            ]);
        }
        if ($this->currentStep === 2) {
            $this->validate([
                'designation' => 'required|string',
                'joining_date' => 'required|date',
            ]);
        }

        if ($this->currentStep < 4) {
            $this->currentStep++;
        } else {
            $this->submit();
        }
    }

    public function prevStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function submit(): void
    {
        $institutionId = app('tenant.institution_id');
        $institution = Institution::find($institutionId);

        $staffIdNo = 'STF-' . now()->year . '-' . str_pad(random_int(1, 999), 3, '0', STR_PAD_LEFT);

        $teacher = Teacher::create([
            'name' => $this->name,
            'name_en' => $this->name_en,
            'gender' => $this->gender,
            'nid' => $this->nid,
            'address' => $this->address,
            'emergency_contact' => $this->emergency_contact,
            'education' => $this->education,
            'passing_institution' => $this->passing_institution,
            'teacher_id_no' => $staffIdNo,
            'phone' => $this->phone,
            'email' => $this->email ?: null,
            'designation' => $this->designation,
            'employee_type' => $this->employee_type,
            'experience_years' => $this->experience_years,
            'previous_workplace' => $this->previous_workplace ?: null,
            'joining_date' => $this->joining_date,
            'status' => 'active',
            'base_salary' => $this->base_salary,
            'house_rent' => $this->house_rent,
            'medical_allowance' => $this->medical_allowance,
            'bank_name' => $this->bank_name ?: null,
            'bank_branch' => $this->bank_branch ?: null,
            'bank_account' => $this->bank_account ?: null,
            'mobile_banking' => $this->mobile_banking ?: null,
            'subjects_taught' => $this->subjects_taught,
            'assigned_classes' => $this->assigned_classes,
        ]);

        // ⚠️ শিক্ষক পোর্টাল অ্যাকাউন্ট — users.teacher_id লিংক দিয়ে
        if ($this->email) {
            User::create([
                'institution_id' => $institutionId,
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make(Str::random(12)),
                'role' => 'teacher',
                'teacher_id' => $teacher->id,
                'must_change_password' => true,
            ]);
        }

        $this->generatedStaffId = $staffIdNo;
        $this->currentStep = 5;
    }

    public function render()
    {
        return view('livewire.teacher-admission-wizard', [
            'subjects' => Subject::orderBy('name')->get(),
            'classes' => SchoolClass::orderBy('display_order')->get(),
        ])->layout('components.layouts.app', ['title' => 'নতুন শিক্ষক নিয়োগ']);
    }
}
