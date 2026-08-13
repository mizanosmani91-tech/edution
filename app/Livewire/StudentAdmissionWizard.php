<?php

namespace App\Livewire;

use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;

class StudentAdmissionWizard extends Component
{
    public int $currentStep = 1;
    public int $totalSteps = 5;

    // Step 1: শিক্ষার্থীর তথ্য
    public string $name = '';
    public string $name_en = '';
    public string $date_of_birth = '';
    public string $gender = '';
    public string $birth_reg_no = '';
    public string $blood_group = '';
    public string $religion = 'ইসলাম';
    public string $nationality = 'বাংলাদেশী';

    // Step 2: একাডেমিক তথ্য
    public string $admission_type = 'new';
    public string $previous_school = '';
    public ?string $class_id = null;
    public ?string $section_id = null;

    // Step 3: অভিভাবকের তথ্য
    public string $guardian_name = '';
    public string $guardian_phone = '';
    public string $guardian_relation = 'পিতা';
    public string $address = '';

    // Step 4: আবাসন (শুধু তথ্য, hostel মডিউল এখনো বাকি)
    public bool $residential = false;
    public bool $meal = false;

    // Step 5: ফলাফল
    public ?string $generatedStudentId = null;

    public function goToStep(int $step): void
    {
        if ($step >= 1 && $step <= $this->totalSteps) {
            $this->currentStep = $step;
        }
    }

    public function nextStep(): void
    {
        // প্রতিটা ধাপের জন্য ন্যূনতম validation
        if ($this->currentStep === 1) {
            $this->validate([
                'name' => 'required|string|max:255',
                'date_of_birth' => 'required|date',
                'gender' => 'required',
            ]);
        }
        if ($this->currentStep === 2) {
            $this->validate([
                'class_id' => 'required|uuid',
            ]);
        }
        if ($this->currentStep === 3) {
            $this->validate([
                'guardian_name' => 'required|string|max:255',
                'guardian_phone' => 'required|string|max:20',
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

    /**
     * চূড়ান্ত সাবমিট — student তৈরি, guardian find-or-create করে সংযুক্ত করা
     */
    public function submit(): void
    {
        $institutionId = app('tenant.institution_id');

        // student_id_no auto-generate: {INSTITUTION_SLUG}-{YEAR}-{RANDOM}
        $institution = Institution::find($institutionId);
        $studentIdNo = strtoupper(Str::substr($institution->slug, 0, 3)) . '-' . now()->year . '-' . str_pad(random_int(1, 999), 3, '0', STR_PAD_LEFT);

        $student = Student::create([
            'name' => $this->name,
            'name_en' => $this->name_en,
            'student_id_no' => $studentIdNo,
            'class_id' => $this->class_id,
            'section_id' => $this->section_id ?: null,
            'date_of_birth' => $this->date_of_birth,
            'gender' => $this->gender,
            'birth_reg_no' => $this->birth_reg_no,
            'blood_group' => $this->blood_group,
            'religion' => $this->religion,
            'nationality' => $this->nationality,
            'admission_type' => $this->admission_type,
            'previous_school' => $this->previous_school ?: null,
            'guardian_phone' => $this->guardian_phone,
            'status' => 'active',
        ]);

        // ⚠️ Guardian find-or-create — একই ফোন নম্বর দিয়ে আগে থেকে guardian
        // অ্যাকাউন্ট থাকলে (ভাই-বোন একই স্কুলে) নতুন করে না বানিয়ে reuse করা,
        // যেহেতু institution_id + email দিয়ে unique constraint আছে users টেবিলে
        $guardianEmail = 'guardian-' . preg_replace('/[^0-9]/', '', $this->guardian_phone) . '@' . $institution->slug . '.local';

        $guardianUser = User::where('institution_id', $institutionId)
            ->where('email', $guardianEmail)
            ->first();

        if (!$guardianUser) {
            $guardianUser = User::create([
                'institution_id' => $institutionId,
                'name' => $this->guardian_name,
                'email' => $guardianEmail,
                'password' => Hash::make(Str::random(12)), // পরে reset করে নেবে
                'role' => 'guardian',
            ]);
        }

        $student->guardians()->attach($guardianUser->id, ['relationship' => $this->guardian_relation, 'institution_id' => $institutionId]);

        $this->generatedStudentId = $studentIdNo;
        $this->currentStep = 5;
    }

    public function render()
    {
        return view('livewire.student-admission-wizard', [
            'classes' => SchoolClass::orderBy('display_order')->get(),
            'sections' => $this->class_id ? Section::where('class_id', $this->class_id)->get() : collect(),
        ])->layout('components.layouts.app', ['title' => 'নতুন শিক্ষার্থী ভর্তি']);
    }
}
