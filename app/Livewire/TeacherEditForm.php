<?php

namespace App\Livewire;

use App\Models\Teacher;
use Livewire\Component;

class TeacherEditForm extends Component
  {
        public Teacher $teacher;

    public string $name = '';
        public string $name_en = '';
        public string $gender = '';
        public string $nid = '';
        public string $phone = '';
        public string $email = '';
        public string $address = '';
        public string $emergency_contact = '';
        public string $education = '';
        public string $passing_institution = '';
        public string $designation = '';
        public string $employee_type = 'permanent';
        public ?int $experience_years = null;
        public string $joining_date = '';
        public string $previous_workplace = '';
        public string $status = 'active';

    public ?float $base_salary = null;
        public ?float $house_rent = null;
        public ?float $medical_allowance = null;
        public string $bank_name = '';
        public string $bank_branch = '';
        public string $bank_account = '';
        public string $mobile_banking = '';

    public bool $saved = false;

    public function mount(Teacher $teacher): void
    {
              $this->teacher = $teacher;

            $this->name = $teacher->name ?? '';
              $this->name_en = $teacher->name_en ?? '';
              $this->gender = $teacher->gender ?? '';
              $this->nid = $teacher->nid ?? '';
              $this->phone = $teacher->phone ?? '';
              $this->email = $teacher->email ?? '';
              $this->address = $teacher->address ?? '';
              $this->emergency_contact = $teacher->emergency_contact ?? '';
              $this->education = $teacher->education ?? '';
              $this->passing_institution = $teacher->passing_institution ?? '';
              $this->designation = $teacher->designation ?? '';
              $this->employee_type = $teacher->employee_type ?? 'permanent';
              $this->experience_years = $teacher->experience_years;
              $this->joining_date = $teacher->joining_date ? $teacher->joining_date->format('Y-m-d') : '';
              $this->previous_workplace = $teacher->previous_workplace ?? '';
              $this->status = $teacher->status ?? 'active';

            $this->base_salary = $teacher->base_salary;
              $this->house_rent = $teacher->house_rent;
              $this->medical_allowance = $teacher->medical_allowance;
              $this->bank_name = $teacher->bank_name ?? '';
              $this->bank_branch = $teacher->bank_branch ?? '';
              $this->bank_account = $teacher->bank_account ?? '';
              $this->mobile_banking = $teacher->mobile_banking ?? '';
    }

    public function save(): void
    {
              $this->validate([
                                          'name' => 'required|string|max:255',
                                          'phone' => 'nullable|string|max:20',
                                          'email' => 'nullable|email',
                                          'gender' => 'nullable|string',
                                          'designation' => 'nullable|string|max:100',
                                          'joining_date' => 'nullable|date',
                                      ]);

            $this->teacher->update([
                                               'name' => $this->name,
                                               'name_en' => $this->name_en ?: null,
                                               'gender' => $this->gender ?: null,
            'nid' => $this->nid ?: null,
                                               'phone' => $this->phone ?: null,
                                               'email' => $this->email ?: null,
                                               'address' => $this->address ?: null,
                                               'emergency_contact' => $this->emergency_contact ?: null,
                                               'education' => $this->education ?: null,
                                               'passing_institution' => $this->passing_institution ?: null,
                                               'designation' => $this->designation ?: null,
                                               'employee_type' => $this->employee_type ?: null,
                                               'experience_years' => $this->experience_years,
                                               'joining_date' => $this->joining_date ?: null,
                                               'previous_workplace' => $this->previous_workplace ?: null,
                                               'status' => $this->status,
                                               'base_salary' => $this->base_salary,
                                               'house_rent' => $this->house_rent,
                                               'medical_allowance' => $this->medical_allowance,
                                               'bank_name' => $this->bank_name ?: null,
                                               'bank_branch' => $this->bank_branch ?: null,
                                               'bank_account' => $this->bank_account ?: null,
                                               'mobile_banking' => $this->mobile_banking ?: null,
                                           ]);

            $this->saved = true;
              $this->dispatch('toast', message: 'শিক্ষকের তথ্য সংরক্ষণ করা হয়েছে');
    }

    public function render()
    {
              return view('livewire.teacher-edit-form')
                            ->layout('components.layouts.app', ['title' => 'শিক্ষক তথ্য সম্পাদনা']);
    }
  }
