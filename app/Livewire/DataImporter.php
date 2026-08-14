<?php

namespace App\Livewire;

use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\ExamSubject;
use App\Models\FeeCollection;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\ImportService;
use App\Support\ImportFieldMap;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class DataImporter extends Component
{
    use WithFileUploads;

    public string $entity = 'students';

    public string $step = 'upload'; // upload / mapping / preview / done

    public $file;

    public array $headers = [];
    public array $rows = [];

    public array $mapping = []; // field_key => column_index|null

    public ?string $examId = null; // শুধু exam-results এর জন্য দরকার

    public int $validCount = 0;
    public int $invalidCount = 0;
    public array $previewRows = []; // ['data' => [...], 'valid' => bool, 'reason' => string]

    public int $importedCount = 0;
    public int $failedCount = 0;
    public array $failedRows = [];

    public ?string $fileError = null;

    public function mount(string $entity = 'students'): void
    {
        $this->entity = in_array($entity, ['students', 'teachers', 'fees', 'exam-results'], true) ? $entity : 'students';
    }

    public function updatedFile(): void
    {
        $this->fileError = null;
        $this->headers = [];
        $this->rows = [];

        if (! $this->file) {
            return;
        }

        $extension = strtolower($this->file->getClientOriginalExtension());

        if (! in_array($extension, ['xlsx', 'xls', 'csv', 'docx'], true)) {
            $this->fileError = 'শুধুমাত্র .xlsx, .xls, .csv অথবা .docx ফাইল আপলোড করুন।';
            $this->file = null;

            return;
        }

        try {
            $parsed = app(ImportService::class)->parse($this->file->getRealPath(), $extension);
        } catch (\Throwable $e) {
            $this->fileError = 'ফাইলটি পড়া যায়নি — ফাইলের ফরম্যাট ঠিক আছে কিনা যাচাই করুন। (ত্রুটি: '.$e->getMessage().')';

            return;
        }

        if (empty($parsed['headers']) || empty($parsed['rows'])) {
            $this->fileError = 'ফাইলে কোনো টেবিল-আকৃতির ডাটা পাওয়া যায়নি।';

            return;
        }

        $this->headers = $parsed['headers'];
        $this->rows = $parsed['rows'];
        $this->mapping = ImportFieldMap::guessMapping($this->fields(), $this->headers);
    }

    public function fields(): array
    {
        return ImportFieldMap::fields($this->entity);
    }

    public function goToMapping(): void
    {
        if (empty($this->headers)) {
            $this->fileError = 'আগে একটা ফাইল আপলোড করুন।';

            return;
        }

        if ($this->entity === 'exam-results' && ! $this->examId) {
            $this->fileError = 'কোন পরীক্ষার মার্কস আপলোড করছেন সেটা নির্বাচন করুন।';

            return;
        }

        $this->step = 'mapping';
    }

    public function backToUpload(): void
    {
        $this->step = 'upload';
    }

    public function buildPreview(): void
    {
        foreach ($this->fields() as $field) {
            if ($field['required'] && ($this->mapping[$field['key']] ?? null) === null) {
                $this->fileError = "\"{$field['label']}\" ফিল্ডটা কোনো কলামের সাথে ম্যাপ করা হয়নি।";

                return;
            }
        }

        $this->fileError = null;
        $this->previewRows = [];
        $this->validCount = 0;
        $this->invalidCount = 0;

        foreach ($this->rows as $row) {
            $mapped = [];
            foreach ($this->mapping as $key => $colIndex) {
                $mapped[$key] = $colIndex !== null ? ($row[$colIndex] ?? null) : null;
            }

            $result = $this->validateRow($mapped);

            $this->previewRows[] = [
                'data' => $mapped,
                'valid' => $result['valid'],
                'reason' => $result['reason'],
            ];

            $result['valid'] ? $this->validCount++ : $this->invalidCount++;
        }

        $this->step = 'preview';
    }

    protected function validateRow(array $row): array
    {
        return match ($this->entity) {
            'students' => $this->validateStudentRow($row),
            'teachers' => $this->validateTeacherRow($row),
            'fees' => $this->validateFeeRow($row),
            'exam-results' => $this->validateExamResultRow($row),
            default => ['valid' => false, 'reason' => 'অজানা ধরন'],
        };
    }

    protected function validateStudentRow(array $row): array
    {
        if (empty(trim((string) ($row['name'] ?? '')))) {
            return ['valid' => false, 'reason' => 'নাম নেই'];
        }

        if (empty(trim((string) ($row['guardian_phone'] ?? '')))) {
            return ['valid' => false, 'reason' => 'অভিভাবকের ফোন নম্বর নেই'];
        }

        $className = trim((string) ($row['class_name'] ?? ''));
        if ($className === '') {
            return ['valid' => false, 'reason' => 'শ্রেণির নাম নেই'];
        }

        $class = SchoolClass::whereRaw('LOWER(name) = ?', [mb_strtolower($className)])->first();
        if (! $class) {
            return ['valid' => false, 'reason' => "\"{$className}\" নামে কোনো শ্রেণি খুঁজে পাওয়া যায়নি"];
        }

        return ['valid' => true, 'reason' => ''];
    }

    protected function validateTeacherRow(array $row): array
    {
        if (empty(trim((string) ($row['name'] ?? '')))) {
            return ['valid' => false, 'reason' => 'নাম নেই'];
        }

        if (empty(trim((string) ($row['phone'] ?? '')))) {
            return ['valid' => false, 'reason' => 'ফোন নম্বর নেই'];
        }

        return ['valid' => true, 'reason' => ''];
    }

    protected function validateFeeRow(array $row): array
    {
        $studentIdNo = trim((string) ($row['student_id_no'] ?? ''));
        if ($studentIdNo === '') {
            return ['valid' => false, 'reason' => 'শিক্ষার্থী আইডি নেই'];
        }

        if (! Student::where('student_id_no', $studentIdNo)->exists()) {
            return ['valid' => false, 'reason' => "আইডি \"{$studentIdNo}\" এর কোনো শিক্ষার্থী পাওয়া যায়নি"];
        }

        if (! preg_match('/^\d{4}-\d{2}$/', trim((string) ($row['due_month'] ?? '')))) {
            return ['valid' => false, 'reason' => 'মাস ফরম্যাট ভুল (হওয়া উচিত YYYY-MM)'];
        }

        if (! is_numeric($row['amount_due'] ?? null) || ! is_numeric($row['amount_paid'] ?? null)) {
            return ['valid' => false, 'reason' => 'বকেয়া/পরিশোধিত পরিমাণ সংখ্যা হতে হবে'];
        }

        return ['valid' => true, 'reason' => ''];
    }

    protected function validateExamResultRow(array $row): array
    {
        $studentIdNo = trim((string) ($row['student_id_no'] ?? ''));
        if ($studentIdNo === '') {
            return ['valid' => false, 'reason' => 'শিক্ষার্থী আইডি নেই'];
        }

        $student = Student::where('student_id_no', $studentIdNo)->first();
        if (! $student) {
            return ['valid' => false, 'reason' => "আইডি \"{$studentIdNo}\" এর কোনো শিক্ষার্থী পাওয়া যায়নি"];
        }

        $subjectName = trim((string) ($row['subject_name'] ?? ''));
        if ($subjectName === '') {
            return ['valid' => false, 'reason' => 'বিষয়ের নাম নেই'];
        }

        $examSubject = ExamSubject::where('exam_id', $this->examId)
            ->where('class_id', $student->class_id)
            ->whereHas('subject', fn ($q) => $q->whereRaw('LOWER(name) = ?', [mb_strtolower($subjectName)]))
            ->first();

        if (! $examSubject) {
            return ['valid' => false, 'reason' => "এই শিক্ষার্থীর শ্রেণির জন্য \"{$subjectName}\" বিষয়টা এই পরীক্ষায় নির্ধারিত নেই (আগে পরীক্ষার সময়সূচিতে যোগ করুন)"];
        }

        $isAbsent = $this->parseBool($row['is_absent'] ?? null);
        if (! $isAbsent && ! is_numeric($row['marks_obtained'] ?? null)) {
            return ['valid' => false, 'reason' => 'প্রাপ্ত নম্বর সংখ্যা হতে হবে (অথবা অনুপস্থিত চিহ্নিত করুন)'];
        }

        return ['valid' => true, 'reason' => ''];
    }

    protected function parseBool($value): bool
    {
        $value = mb_strtolower(trim((string) $value));

        return in_array($value, ['1', 'yes', 'true', 'হ্যাঁ', 'হ্যা', 'absent', 'অনুপস্থিত'], true);
    }

    public function runImport(): void
    {
        $this->importedCount = 0;
        $this->failedCount = 0;
        $this->failedRows = [];

        foreach ($this->previewRows as $item) {
            if (! $item['valid']) {
                $this->failedCount++;
                $this->failedRows[] = ['data' => $item['data'], 'reason' => $item['reason']];

                continue;
            }

            try {
                match ($this->entity) {
                    'students' => $this->importStudentRow($item['data']),
                    'teachers' => $this->importTeacherRow($item['data']),
                    'fees' => $this->importFeeRow($item['data']),
                    'exam-results' => $this->importExamResultRow($item['data']),
                    default => null,
                };
                $this->importedCount++;
            } catch (\Throwable $e) {
                $this->failedCount++;
                $this->failedRows[] = ['data' => $item['data'], 'reason' => 'সংরক্ষণ ব্যর্থ: '.$e->getMessage()];
            }
        }

        $this->step = 'done';
    }

    protected function generateStudentIdNo(): string
    {
        $institution = Institution::find(app('tenant.institution_id'));

        return strtoupper(Str::substr($institution->slug, 0, 3)).'-'.now()->year.'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    protected function generateTeacherIdNo(): string
    {
        $institution = Institution::find(app('tenant.institution_id'));

        return 'T-'.strtoupper(Str::substr($institution->slug, 0, 3)).'-'.now()->year.'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    protected function importStudentRow(array $row): void
    {
        $class = SchoolClass::whereRaw('LOWER(name) = ?', [mb_strtolower(trim($row['class_name']))])->first();

        $section = null;
        if (! empty(trim((string) ($row['section_name'] ?? '')))) {
            $section = Section::where('class_id', $class->id)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($row['section_name']))])
                ->first();
        }

        $studentIdNo = trim((string) ($row['student_id_no'] ?? '')) ?: $this->generateStudentIdNo();

        Student::updateOrCreate(
            ['student_id_no' => $studentIdNo],
            [
                'name' => trim($row['name']),
                'name_en' => $row['name_en'] ?? null,
                'class_id' => $class->id,
                'section_id' => $section?->id,
                'gender' => $row['gender'] ?? null,
                'date_of_birth' => $this->parseDate($row['date_of_birth'] ?? null),
                'guardian_phone' => trim($row['guardian_phone']),
                'blood_group' => $row['blood_group'] ?? null,
                'previous_school' => $row['previous_school'] ?? null,
                'admission_type' => 'imported',
            ]
        );
    }

    protected function importTeacherRow(array $row): void
    {
        $teacherIdNo = trim((string) ($row['teacher_id_no'] ?? '')) ?: $this->generateTeacherIdNo();

        Teacher::updateOrCreate(
            ['teacher_id_no' => $teacherIdNo],
            [
                'name' => trim($row['name']),
                'phone' => trim($row['phone']),
                'email' => $row['email'] ?? null,
                'designation' => $row['designation'] ?? null,
                'joining_date' => $this->parseDate($row['joining_date'] ?? null),
                'base_salary' => is_numeric($row['base_salary'] ?? null) ? $row['base_salary'] : 0,
                'status' => 'active',
            ]
        );
    }

    protected function importFeeRow(array $row): void
    {
        $student = Student::where('student_id_no', trim($row['student_id_no']))->first();

        $amountDue = (float) $row['amount_due'];
        $amountPaid = (float) $row['amount_paid'];
        $status = $amountPaid >= $amountDue ? 'paid' : ($amountPaid > 0 ? 'partial' : 'due');

        FeeCollection::create([
            'student_id' => $student->id,
            'fee_type' => $row['fee_type'] ?: 'monthly',
            'amount_due' => $amountDue,
            'amount_paid' => $amountPaid,
            'payment_method' => $row['payment_method'] ?: 'cash',
            'due_month' => trim($row['due_month']),
            'paid_at' => $amountPaid > 0 ? now() : null,
            'collected_by' => auth()->id(),
            'status' => $status,
        ]);
    }

    protected function importExamResultRow(array $row): void
    {
        $student = Student::where('student_id_no', trim($row['student_id_no']))->first();

        $examSubject = ExamSubject::where('exam_id', $this->examId)
            ->where('class_id', $student->class_id)
            ->whereHas('subject', fn ($q) => $q->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($row['subject_name']))]))
            ->first();

        $isAbsent = $this->parseBool($row['is_absent'] ?? null);

        ExamMark::updateOrCreate(
            ['exam_subject_id' => $examSubject->id, 'student_id' => $student->id],
            [
                'marks_obtained' => $isAbsent ? null : min((float) $row['marks_obtained'], (float) $examSubject->full_marks),
                'is_absent' => $isAbsent,
                'entered_by' => auth()->id(),
            ]
        );
    }

    protected function parseDate(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    public function startOver(): void
    {
        $this->reset(['file', 'headers', 'rows', 'mapping', 'previewRows', 'validCount', 'invalidCount', 'importedCount', 'failedCount', 'failedRows', 'fileError']);
        $this->step = 'upload';
    }

    public function render()
    {
        return view('livewire.data-importer', [
            'fields' => $this->fields(),
            'entityLabel' => ImportFieldMap::label($this->entity),
            'exams' => $this->entity === 'exam-results' ? Exam::orderByDesc('start_date')->get() : collect(),
        ])->layout('components.layouts.app', ['title' => ImportFieldMap::label($this->entity).' ইমপোর্ট']);
    }
}
