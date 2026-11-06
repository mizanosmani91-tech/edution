<?php

namespace App\Livewire;

use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * শিক্ষার্থীর তথ্য সম্পাদনা — শুধু অ্যাডমিন। শিক্ষার্থী-আইডি বদলানো যায় না।
 * "নিষ্ক্রিয়/বদলি/প্রাক্তন" স্ট্যাটাস এখান থেকেই বদলানো হয় (রেকর্ড মোছা হয় না,
 * কারণ ফি, নম্বর ও হাজিরার ইতিহাস ঠিক রাখতে হবে)।
 */
class StudentEditForm extends Component
{
    public const STATUSES = [
        'active' => 'সক্রিয়',
        'inactive' => 'নিষ্ক্রিয়',
        'transferred' => 'বদলি হয়ে গেছে',
        'alumni' => 'প্রাক্তন (পাশ করেছে)',
    ];

    public Student $student;

    public string $name = '';
    public string $name_en = '';
    public string $date_of_birth = '';
    public string $gender = '';
    public string $birth_reg_no = '';
    public string $blood_group = '';
    public string $religion = '';
    public string $nationality = '';
    public string $admission_type = 'new';
    public string $previous_school = '';
    public string $class_id = '';
    public ?string $section_id = null;
    public string $guardian_phone = '';
    public string $status = 'active';

    public bool $saved = false;

    public function mount(Student $student): void
    {
        $this->authorizeAdmin();

        $this->student = $student;
        $this->name = (string) $student->name;
        $this->name_en = (string) $student->name_en;
        $this->date_of_birth = $student->date_of_birth ? \Illuminate\Support\Carbon::parse($student->date_of_birth)->format('Y-m-d') : '';
        $this->gender = (string) $student->gender;
        $this->birth_reg_no = (string) $student->birth_reg_no;
        $this->blood_group = (string) $student->blood_group;
        $this->religion = (string) $student->religion;
        $this->nationality = (string) $student->nationality;
        $this->admission_type = (string) ($student->admission_type ?: 'new');
        $this->previous_school = (string) $student->previous_school;
        $this->class_id = (string) $student->class_id;
        $this->section_id = $student->section_id;
        $this->guardian_phone = (string) $student->guardian_phone;
        $this->status = (string) ($student->status ?: 'active');
    }

    /** শিক্ষার্থী এডিট শুধু অ্যাডমিনের কাজ — প্রতিটা অ্যাকশনে আবার চেক হয়। */
    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->role === 'admin', 403, 'শুধু অ্যাডমিন শিক্ষার্থীর তথ্য বদলাতে পারবেন।');
    }

    /** শ্রেণি বদলালে আগের সেকশন আর বৈধ নয়। */
    public function updatedClassId(): void
    {
        $this->section_id = null;
    }

    public function save(): void
    {
        $this->authorizeAdmin();

        $tenant = app('tenant.institution_id');

        // ⚠️ exists/unique রুল global scope বাইপাস করে — তাই institution_id হাতে দেওয়া
        $this->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date|before_or_equal:today',
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'birth_reg_no' => 'nullable|string|max:50',
            'blood_group' => 'nullable|string|max:5',
            'religion' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:50',
            'admission_type' => ['nullable', Rule::in(['new', 'transfer'])],
            'previous_school' => 'nullable|string|max:255',
            'class_id' => ['required', Rule::exists('classes', 'id')->where('institution_id', $tenant)],
            'section_id' => ['nullable', Rule::exists('sections', 'id')->where('institution_id', $tenant)->where('class_id', $this->class_id)],
            'guardian_phone' => 'nullable|string|max:20',
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ], [], [
            'name' => 'নাম',
            'class_id' => 'শ্রেণি',
            'section_id' => 'সেকশন',
            'date_of_birth' => 'জন্মতারিখ',
            'guardian_phone' => 'অভিভাবকের ফোন',
            'status' => 'স্ট্যাটাস',
        ]);

        $this->student->update([
            'name' => $this->name,
            'name_en' => $this->name_en ?: null,
            'date_of_birth' => $this->date_of_birth ?: null,
            'gender' => $this->gender ?: null,
            'birth_reg_no' => $this->birth_reg_no ?: null,
            'blood_group' => $this->blood_group ?: null,
            'religion' => $this->religion ?: null,
            // এই দুটো কলাম NOT NULL (ডিফল্ট আছে) — ফাঁকা রাখলে null নয়, ডিফল্টে ফেরে।
            // (CI ধরেছে: আগে null লেখার চেষ্টায় সেভ ক্র্যাশ করত।)
            'nationality' => $this->nationality !== '' ? $this->nationality : 'বাংলাদেশী',
            'admission_type' => $this->admission_type !== '' ? $this->admission_type : 'new',
            'previous_school' => $this->previous_school ?: null,
            'class_id' => $this->class_id,
            'section_id' => $this->section_id ?: null,
            'guardian_phone' => $this->guardian_phone ?: null,
        ]);

        // status মাস-অ্যাসাইনেবল নয় (fillable-এ নেই), তাই আলাদা, স্পষ্টভাবে
        $this->student->forceFill(['status' => $this->status])->save();

        $this->saved = true;
        $this->dispatch('toast', message: 'শিক্ষার্থীর তথ্য সংরক্ষণ করা হয়েছে');
    }

    public function render()
    {
        $this->authorizeAdmin();

        return view('livewire.student-edit-form', [
            'classes' => SchoolClass::orderBy('display_order')->get(),
            'sections' => $this->class_id ? Section::where('class_id', $this->class_id)->orderBy('name')->get() : collect(),
            'statuses' => self::STATUSES,
        ])->layout('components.layouts.app', ['title' => 'শিক্ষার্থীর তথ্য সম্পাদনা']);
    }
}
