<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\AdmissionApplication;
use App\Models\Book;
use App\Models\Certificate;
use App\Models\Complaint;
use App\Models\DisciplineRecord;
use App\Models\Exam;
use App\Models\ExamSeatPlan;
use App\Models\ExamSubject;
use App\Models\Expense;
use App\Models\FeeStructure;
use App\Models\Homework;
use App\Models\HostelRoom;
use App\Models\Institution;
use App\Models\LeaveRequest;
use App\Models\LessonPlan;
use App\Models\Notice;
use App\Models\PayrollRecord;
use App\Models\PerformanceReview;
use App\Models\QuestionBankItem;
use App\Models\RoutinePeriod;
use App\Models\Scholarship;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentHealthRecord;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TransportRoute;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * DemoDataSeeder — edution.xyz-এর পাবলিক ডেমো একাউন্টের (demo@edution.xyz)
 * জন্য প্রতিটা মডিউলে বাস্তবসম্মত নমুনা ডেটা ভরে দেয়।
 *
 * প্রতি ডিপ্লয়ে চলে (DatabaseSeeder থেকে) — তাই idempotent রাখতে প্রথমে
 * ডেমো ইনস্টিটিউশনের সব পুরনো ডেটা মুছে আবার ফ্রেশ করে বানানো হয় (relative
 * date যেমন "আজকের হাজিরা" সবসময় আপ-টু-ডেট থাকার জন্য এটাই দরকার)।
 */
class DemoDataSeeder extends Seeder
{
    private array $bnFirstM = ['রাফি','সাকিব','তানভীর','আরিফ','ইমরান','ফাহিম','নাফিস','রাকিব','সজীব','হাসান','মেহেদী','তাওহীদ','জিসান','রায়হান','সাদমান','ফারহান','আসিফ','নাহিদ','সিয়াম','তানজিম'];
    private array $bnFirstF = ['ফাতিমা','আয়েশা','জান্নাত','সাদিয়া','নুসরাত','মাহিয়া','তাসনিম','রিয়া','সুমাইয়া','নাদিয়া','ইসরাত','মিম','তানিয়া','রুমা','লামিয়া','সাবিহা','প্রিয়া','মৌসুমী','জেরিন','ইতি'];
    private array $bnLast = ['ইসলাম','হোসেন','আহমেদ','রহমান','খান','চৌধুরী','আলী','হক','সরকার','মোল্লা','শেখ','মিয়া','তালুকদার','প্রামাণিক','বিশ্বাস'];

    public function run(): void
    {
        $institution = Institution::where('slug', DemoSeeder::SLUG)->first();
        if (!$institution) {
            return; // DemoSeeder আগে চলার কথা; না চললে এই সিডার কিছু করবে না
        }

        $instId = $institution->id;
        $adminUser = \App\Models\User::where('institution_id', $instId)->where('email', DemoSeeder::EMAIL)->first();
        $adminId = $adminUser?->id;

        $this->wipe($instId);

        try {
            DB::transaction(function () use ($instId, $adminId) {
                $this->seedAll($instId, $adminId);
            });
        } catch (\Throwable $e) {
            // ডেমো ডেটা সিডিং ফেইল করলেও পুরো ডিপ্লয় যেন আটকে না যায় — লগ করে
            // এগিয়ে যাওয়া হচ্ছে। আসল প্রতিষ্ঠানের ডেটার সাথে এই সিডারের কোনো
            // সম্পর্ক নেই (শুধু demo institution), তাই fail-soft এখানে নিরাপদ।
            report($e);
            // deploy.yml এর SSH আউটপুটে (GitHub Actions log) সরাসরি দেখা যাওয়ার
            // জন্য STDERR এ ও লিখে রাখা হচ্ছে — storage/logs এ SSH করে ঢুকতে
            // না হয়েও এখান থেকেই আসল এরর মেসেজ পড়া যাবে।
            fwrite(STDERR, "\n=== DemoDataSeeder FAILED ===\n" . $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString() . "\n=============================\n");
        }
    }

    private function seedAll(string $instId, ?string $adminId): void
    {
        {
            $subjects = $this->seedSubjects($instId);
            [$classes, $sections] = $this->seedClassesAndSections($instId);
            $teachers = $this->seedTeachers($instId, $subjects, $classes);
            $students = $this->seedStudents($instId, $classes, $sections);
            $this->seedPortalLogins($instId, $teachers, $students, $sections);
            $this->seedRoutine($instId, $classes, $sections, $subjects, $teachers);

            $this->seedAttendance($instId, $students, $adminId);
            $this->seedStaffAttendance($instId, $teachers, $adminId);
            $this->seedFees($instId, $students, $classes, $adminId);
            $this->seedExams($instId, $subjects, $classes, $teachers, $students, $adminId);
            $this->seedNotices($instId, $adminId);
            $this->seedExpenses($instId, $adminId);
            $this->seedLibrary($instId, $students, $teachers);
            $this->seedTransport($instId, $students);
            $this->seedHostel($instId, $students);
            $this->seedLeaveRequests($instId, $students, $teachers, $adminId);
            $this->seedComplaints($instId, $adminId);
            $this->seedLifecycle($instId, $students, $adminId);
            $this->seedPayrollAndPerformance($instId, $teachers, $adminId);
            $this->seedAdmissionApplications($instId, $classes);
            $this->seedAcademicSession($instId);
            $this->seedHomeworkAndPlans($instId, $classes, $sections, $subjects, $teachers);
            $this->seedQuestionBank($instId, $classes, $subjects);
            $this->seedScholarships($instId, $students, $adminId);
        }
    }

    /**
     * পাবলিক ডেমোতে শুধু এডমিন লগইন থাকলে শিক্ষক/অভিভাবক পোর্টাল দুটো
     * দেখানো যেত না — তাই একজন শিক্ষক (যিনি প্রধান শিক্ষকও) আর একজন
     * অভিভাবকের লগইন তৈরি করে দেওয়া হচ্ছে। প্রতি ডিপ্লয়ে teacher/student
     * রো নতুন UUID নিয়ে আবার তৈরি হয় (wipe()), তাই এখানে updateOrCreate
     * দিয়ে fixed ইমেইলের ইউজারের FK-গুলো প্রতিবার নতুন ID দিয়ে
     * রিফ্রেশ করা হচ্ছে — নাহলে stale reference থেকে যেত।
     */
    private function seedPortalLogins(string $instId, array $teachers, array $students, array $sections): void
    {
        $headmaster = collect($teachers)->firstWhere('designation', 'প্রধান শিক্ষক') ?? $teachers[0];

        \App\Models\User::updateOrCreate(
            ['institution_id' => $instId, 'email' => DemoSeeder::TEACHER_EMAIL],
            [
                'name' => $headmaster->name,
                'password' => \Illuminate\Support\Facades\Hash::make(DemoSeeder::STAFF_PASSWORD),
                'role' => 'teacher',
                'teacher_id' => $headmaster->id,
                'must_change_password' => false,
            ]
        );

        // এই শিক্ষককেই প্রথম সেকশনের ক্লাস শিক্ষক বানিয়ে দেওয়া হলো, যাতে
        // অভিভাবক পোর্টালের "বার্তা" ট্যাবে ক্লাস শিক্ষক ও প্রধান শিক্ষক —
        // দুটো যোগাযোগ অপশনই বাস্তবে কাজ করা অবস্থায় দেখা যায়।
        $demoSection = $sections[0] ?? null;
        $demoSection?->update(['class_teacher_id' => $headmaster->id]);

        $demoChild = collect($students)->firstWhere('section_id', $demoSection?->id) ?? ($students[0] ?? null);

        if (! $demoChild) {
            return;
        }

        $guardianUser = \App\Models\User::updateOrCreate(
            ['institution_id' => $instId, 'email' => DemoSeeder::GUARDIAN_EMAIL],
            [
                'name' => 'ডেমো অভিভাবক',
                'password' => \Illuminate\Support\Facades\Hash::make(DemoSeeder::STAFF_PASSWORD),
                'role' => 'guardian',
                'must_change_password' => false,
            ]
        );

        DB::table('guardian_student')->updateOrInsert(
            ['guardian_id' => $guardianUser->id, 'student_id' => $demoChild->id],
            [
                'id' => (string) Str::uuid(),
                'institution_id' => $instId,
                'relationship' => 'পিতা',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function wipe(string $instId): void
    {
        $tables = [
            'exam_marks', 'exam_subjects', 'exams',
            'attendances', 'staff_attendances',
            'fee_collections', 'fee_structures',
            'book_issues', 'books',
            'student_transports', 'transport_routes',
            'student_hostels', 'hostel_rooms',
            'leave_requests', 'complaints',
            'certificates', 'discipline_records', 'student_health_records',
            'payroll_records', 'performance_reviews',
            'admission_applications', 'academic_sessions',
            'homeworks', 'lesson_plans', 'question_bank_items', 'scholarships',
            'sections', 'classes', 'subjects', 'teachers', 'students',
        ];
        foreach ($tables as $t) {
            DB::table($t)->where('institution_id', $instId)->delete();
        }
    }

    private function bnName(bool $male = true): string
    {
        $first = $male ? $this->bnFirstM[array_rand($this->bnFirstM)] : $this->bnFirstF[array_rand($this->bnFirstF)];
        return $first . ' ' . $this->bnLast[array_rand($this->bnLast)];
    }

    private function phone(): string
    {
        return '01' . collect(range(1, 9))->random() . rand(10000000, 99999999);
    }

    private function seedSubjects(string $instId): array
    {
        $names = ['বাংলা', 'ইংরেজি', 'গণিত', 'বিজ্ঞান', 'সামাজিক বিজ্ঞান', 'ধর্ম শিক্ষা', 'তথ্য ও যোগাযোগ প্রযুক্তি', 'শারীরিক শিক্ষা'];
        $out = [];
        foreach ($names as $i => $n) {
            $out[] = Subject::create(['institution_id' => $instId, 'name' => $n, 'code' => 'SUB' . ($i + 1)]);
        }
        return $out;
    }

    private function seedClassesAndSections(string $instId): array
    {
        $classNames = ['ষষ্ঠ শ্রেণি', 'সপ্তম শ্রেণি', 'অষ্টম শ্রেণি', 'নবম শ্রেণি', 'দশম শ্রেণি'];
        $classes = [];
        $sections = [];
        foreach ($classNames as $i => $n) {
            $c = SchoolClass::create(['institution_id' => $instId, 'name' => $n, 'display_order' => $i + 1]);
            $classes[] = $c;
            foreach (['ক', 'খ'] as $sn) {
                $sections[] = Section::create(['institution_id' => $instId, 'class_id' => $c->id, 'name' => $sn, 'capacity' => 40]);
            }
        }
        return [$classes, $sections];
    }

    private function seedTeachers(string $instId, array $subjects, array $classes): array
    {
        $designations = ['প্রধান শিক্ষক', 'সহকারী শিক্ষক', 'সিনিয়র শিক্ষক', 'বিষয় শিক্ষক'];
        $teachers = [];
        for ($i = 0; $i < 16; $i++) {
            $male = $i % 3 !== 0;
            $subject = $subjects[$i % count($subjects)];
            $teachers[] = Teacher::create([
                'institution_id' => $instId,
                'name' => $this->bnName($male),
                'gender' => $male ? 'male' : 'female',
                'teacher_id_no' => 'T' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'phone' => $this->phone(),
                'email' => 'teacher' . ($i + 1) . '@demo-edution.test',
                'designation' => $i === 0 ? 'প্রধান শিক্ষক' : $designations[array_rand($designations)],
                'employee_type' => 'full_time',
                'experience_years' => rand(1, 20),
                'joining_date' => Carbon::now()->subYears(rand(1, 8))->subMonths(rand(0, 11)),
                'status' => 'active',
                'base_salary' => rand(18, 45) * 1000,
                'house_rent' => rand(3, 10) * 1000,
                'medical_allowance' => rand(1, 3) * 1000,
                'subjects_taught' => [$subject->id],
                'assigned_classes' => [$classes[array_rand($classes)]->id],
            ]);
        }
        return $teachers;
    }

    private function seedStudents(string $instId, array $classes, array $sections): array
    {
        $students = [];
        $seq = 1;
        foreach ($sections as $section) {
            $classId = $section->class_id;
            for ($i = 0; $i < 9; $i++) {
                $male = rand(0, 1) === 1;
                $students[] = Student::create([
                    'institution_id' => $instId,
                    'name' => $this->bnName($male),
                    'student_id_no' => 'S' . date('y') . str_pad((string) $seq, 4, '0', STR_PAD_LEFT),
                    'class_id' => $classId,
                    'section_id' => $section->id,
                    'gender' => $male ? 'male' : 'female',
                    'guardian_phone' => $this->phone(),
                    'blood_group' => ['A+', 'B+', 'O+', 'AB+', 'A-', 'O-'][array_rand(['A+', 'B+', 'O+', 'AB+', 'A-', 'O-'])],
                    'admission_type' => 'regular',
                    'date_of_birth' => Carbon::now()->subYears(rand(11, 16))->subDays(rand(0, 300)),
                ]);
                $seq++;
            }
        }
        return $students;
    }

    private function seedAttendance(string $instId, array $students, ?string $adminId): void
    {
        $rows = [];
        $days = 60;
        for ($d = $days; $d >= 0; $d--) {
            $date = Carbon::now()->subDays($d);
            if ($d !== 0 && ($date->isFriday() || $date->isSaturday())) {
                continue; // ঐতিহাসিক সাপ্তাহিক ছুটি বাদ, কিন্তু আজকের দিন সবসময় ডেটা থাকবে
            }
            foreach ($students as $s) {
                $roll = rand(1, 100);
                $status = $roll <= 88 ? 'present' : ($roll <= 94 ? 'late' : ($roll <= 98 ? 'absent' : 'leave'));
                $rows[] = [
                    'id' => (string) Str::uuid(),
                    'institution_id' => $instId,
                    'student_id' => $s->id,
                    'class_id' => $s->class_id,
                    'section_id' => $s->section_id,
                    'date' => $date->toDateString(),
                    'status' => $status,
                    'marked_by' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                if (count($rows) >= 500) {
                    DB::table('attendances')->insert($rows);
                    $rows = [];
                }
            }
        }
        if ($rows) {
            DB::table('attendances')->insert($rows);
        }
    }

    private function seedStaffAttendance(string $instId, array $teachers, ?string $adminId): void
    {
        $rows = [];
        $days = 60;
        for ($d = $days; $d >= 0; $d--) {
            $date = Carbon::now()->subDays($d);
            if ($d !== 0 && ($date->isFriday() || $date->isSaturday())) {
                continue; // ঐতিহাসিক সাপ্তাহিক ছুটি বাদ, কিন্তু আজকের দিন সবসময় ডেটা থাকবে
            }
            foreach ($teachers as $t) {
                $roll = rand(1, 100);
                $status = $roll <= 92 ? 'present' : ($roll <= 97 ? 'late' : 'absent');
                $rows[] = [
                    'id' => (string) Str::uuid(),
                    'institution_id' => $instId,
                    'teacher_id' => $t->id,
                    'date' => $date->toDateString(),
                    'status' => $status,
                    'check_in' => $status !== 'absent' ? $date->copy()->setTime(8, rand(0, 30)) : null,
                    'check_out' => $status !== 'absent' ? $date->copy()->setTime(16, rand(0, 30)) : null,
                    'marked_by' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('staff_attendances')->insert($chunk);
        }
    }

    private function seedFees(string $instId, array $students, array $classes, ?string $adminId): void
    {
        foreach ($classes as $c) {
            FeeStructure::create(['institution_id' => $instId, 'class_id' => $c->id, 'fee_type' => 'মাসিক বেতন', 'amount' => rand(8, 20) * 100, 'frequency' => 'monthly']);
            FeeStructure::create(['institution_id' => $instId, 'class_id' => $c->id, 'fee_type' => 'পরীক্ষার ফি', 'amount' => rand(3, 6) * 100, 'frequency' => 'termly']);
        }
        FeeStructure::create(['institution_id' => $instId, 'class_id' => null, 'fee_type' => 'ভর্তি ফি', 'amount' => 3000, 'frequency' => 'one_time']);

        $rows = [];
        for ($m = 5; $m >= 0; $m--) {
            $month = Carbon::now()->subMonths($m);
            $dueMonth = $month->format('Y-m');
            foreach ($students as $s) {
                $amountDue = rand(8, 20) * 100;
                $roll = rand(1, 100);
                if ($roll <= 68) {
                    $status = 'paid';
                    $paid = $amountDue;
                    $paidAt = $month->copy()->addDays(rand(1, 20));
                } elseif ($roll <= 85) {
                    $status = 'partial';
                    $paid = (int) ($amountDue * (rand(30, 70) / 100));
                    $paidAt = $month->copy()->addDays(rand(1, 20));
                } else {
                    $status = $m === 0 ? 'due' : 'overdue';
                    $paid = 0;
                    $paidAt = null;
                }
                $rows[] = [
                    'id' => (string) Str::uuid(),
                    'institution_id' => $instId,
                    'student_id' => $s->id,
                    'fee_type' => 'monthly',
                    'amount_due' => $amountDue,
                    'amount_paid' => $paid,
                    'fine_amount' => 0,
                    'payment_method' => ['bkash', 'nagad', 'bank_transfer', 'cash'][array_rand(['bkash', 'nagad', 'bank_transfer', 'cash'])],
                    'due_month' => $dueMonth,
                    'paid_at' => $paidAt,
                    'collected_by' => $adminId,
                    'status' => $status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('fee_collections')->insert($chunk);
        }
    }

    private function seedExams(string $instId, array $subjects, array $classes, array $teachers, array $students, ?string $adminId): void
    {
        $mid = Exam::create([
            'institution_id' => $instId, 'name' => 'মধ্যবর্তী পরীক্ষা ২০২৬', 'exam_type' => 'term',
            'academic_year' => '2026', 'start_date' => Carbon::now()->subDays(35), 'end_date' => Carbon::now()->subDays(28),
            'is_published' => true,
        ]);
        $final = Exam::create([
            'institution_id' => $instId, 'name' => 'বার্ষিক পরীক্ষা ২০২৬', 'exam_type' => 'final',
            'academic_year' => '2026', 'start_date' => Carbon::now()->addDays(25), 'end_date' => Carbon::now()->addDays(32),
            'is_published' => false,
        ]);

        $coreSubjects = array_slice($subjects, 0, 5);
        $markRows = [];
        foreach ($classes as $c) {
            $classStudents = array_values(array_filter($students, fn ($s) => $s->class_id === $c->id));
            foreach ($coreSubjects as $subj) {
                $es = ExamSubject::create([
                    'institution_id' => $instId, 'exam_id' => $mid->id, 'subject_id' => $subj->id,
                    'class_id' => $c->id, 'teacher_id' => $teachers[array_rand($teachers)]->id,
                    'full_marks' => 100, 'pass_marks' => 33,
                ]);
                ExamSubject::create([
                    'institution_id' => $instId, 'exam_id' => $final->id, 'subject_id' => $subj->id,
                    'class_id' => $c->id, 'teacher_id' => $teachers[array_rand($teachers)]->id,
                    'full_marks' => 100, 'pass_marks' => 33,
                ]);
                foreach ($classStudents as $s) {
                    $absent = rand(1, 100) <= 3;
                    $markRows[] = [
                        'id' => (string) Str::uuid(),
                        'institution_id' => $instId,
                        'exam_subject_id' => $es->id,
                        'student_id' => $s->id,
                        'marks_obtained' => $absent ? null : rand(28, 98),
                        'is_absent' => $absent,
                        'entered_by' => $adminId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }
        foreach (array_chunk($markRows, 500) as $chunk) {
            DB::table('exam_marks')->insert($chunk);
        }

        $roomNames = ['রুম ১০১', 'রুম ১০২', 'রুম ২০১'];
                $seatPlans = [];
                foreach ($roomNames as $i => $roomName) {
                                $seatPlans[] = ExamSeatPlan::create([
                                                                                    'institution_id' => $instId,
                                                                                    'exam_id' => $mid->id,
                                                                                    'room_name' => $roomName,
                                                                                    'capacity' => 30,
                                                                                    'display_order' => $i + 1,
                                                                                ]);
                }
                $seatRows = [];
                $planIndex = 0;
                $seatInRoom = 0;
                foreach ($students as $s) {
                                $plan = $seatPlans[$planIndex];
                                $seatInRoom++;
                                $seatRows[] = [
                                                    'id' => (string) Str::uuid(),
                                                    'institution_id' => $instId,
                                                    'exam_id' => $mid->id,
                                                    'exam_seat_plan_id' => $plan->id,
                                                    'student_id' => $s->id,
                                                                        'seat_no' => $seatInRoom,
                                                    'created_at' => now(),
                                                    'updated_at' => now(),
                                                ];
                                if ($seatInRoom >= $plan->capacity) {
                                                    $planIndex = min($planIndex + 1, count($seatPlans) - 1);
                                                    $seatInRoom = 0;
                                }
                }
                foreach (array_chunk($seatRows, 500) as $chunk) {
                                DB::table('exam_seat_assignments')->insert($chunk);
                }
    }

    private function seedRoutine(string $instId, array $classes, array $sections, array $subjects, array $teachers): void
    {
                $days = [1, 2, 3, 4, 5, 6];
                $periodsPerDay = 6;
                $rows = [];
                foreach ($sections as $si => $section) {
                                foreach ($days as $day) {
                                                    for ($period = 1; $period <= $periodsPerDay; $period++) {
                                                                            $teacher = $teachers[($si + $day + $period) % count($teachers)];
                                                                            $subject = $subjects[($si + $period) % count($subjects)];
                                                                            $start = Carbon::createFromTime(8, 0)->addMinutes(($period - 1) * 50);
                                                                            $end = (clone $start)->addMinutes(40);
                                                                            $rows[] = [
                                                                                                        'id' => (string) Str::uuid(),
                                                                                                        'institution_id' => $instId,
                                                                                                        'class_id' => $section->class_id,
                                                                                                        'section_id' => $section->id,
                                                                                                        'teacher_id' => $teacher->id,
                                                                                                        'subject_id' => $subject->id,
                                                                                                        'day_of_week' => $day,
                                                                                                        'period_number' => $period,
                                                                                                        'start_time' => $start->format('H:i:s'),
                                                                                                        'end_time' => $end->format('H:i:s'),
                                                                                                        'created_at' => now(),
                                                                                                        'updated_at' => now(),
                                                                                                    ];
                                                    }
                                }
                }
                foreach (array_chunk($rows, 500) as $chunk) {
                                DB::table('routine_periods')->insert($chunk);
                }
    }

    private function seedNotices(string $instId, ?string $adminId): void
    {
        $items = [
            ['বার্ষিক ক্রীড়া প্রতিযোগিতা', 'আগামী মাসে বার্ষিক ক্রীড়া প্রতিযোগিতা অনুষ্ঠিত হবে। সকল শিক্ষার্থীর অংশগ্রহণ বাধ্যতামূলক।', 'event', true, false],
            ['মধ্যবর্তী পরীক্ষার রুটিন প্রকাশ', 'মধ্যবর্তী পরীক্ষার সময়সূচি নোটিশ বোর্ডে প্রকাশ করা হয়েছে।', 'academic', true, false],
            ['মাসিক বেতন পরিশোধের সময়সীমা', 'চলতি মাসের বেতন ১০ তারিখের মধ্যে পরিশোধ করার জন্য অনুরোধ করা হচ্ছে।', 'finance', false, true],
            ['অভিভাবক সমাবেশ', 'আগামী শুক্রবার অভিভাবক সমাবেশ অনুষ্ঠিত হবে, সকাল ১০টায়।', 'event', false, false],
            ['ছুটির ঘোষণা', 'সরকারি ছুটির কারণে আগামীকাল প্রতিষ্ঠান বন্ধ থাকবে।', 'general', false, true],
            ['নতুন লাইব্রেরি বই সংযোজন', 'লাইব্রেরিতে ৫০টি নতুন বই যুক্ত করা হয়েছে।', 'general', false, false],
            ['ভর্তি কার্যক্রম শুরু', 'আগামী শিক্ষাবর্ষের ভর্তি কার্যক্রম শুরু হয়েছে।', 'academic', false, false],
            ['প্রথম সাময়িক মূল্যায়ন ফলাফল', 'প্রথম সাময়িক মূল্যায়নের ফলাফল প্রকাশিত হয়েছে।', 'academic', false, false],
        ];
        foreach ($items as $i => [$title, $body, $cat, $pinned, $urgent]) {
            Notice::create([
                'institution_id' => $instId, 'title' => $title, 'body' => $body, 'category' => $cat,
                'audience' => null, 'is_pinned' => $pinned, 'is_urgent' => $urgent,
                'publish_at' => Carbon::now()->subDays($i * 3), 'views' => rand(20, 300), 'created_by' => $adminId,
            ]);
        }
    }

    private function seedExpenses(string $instId, ?string $adminId): void
    {
        $categories = ['বেতন', 'ইউটিলিটি', 'রক্ষণাবেক্ষণ', 'স্টেশনারি', 'অন্যান্য'];
        for ($i = 0; $i < 20; $i++) {
            Expense::create([
                'institution_id' => $instId,
                'category' => $categories[array_rand($categories)],
                'amount' => rand(2, 60) * 500,
                'date' => Carbon::now()->subDays(rand(0, 180)),
                'description' => 'নিয়মিত খরচ',
                'recorded_by' => $adminId,
            ]);
        }
    }

    private function seedLibrary(string $instId, array $students, array $teachers): void
    {
        $titles = ['বাংলা ব্যাকরণ ও রচনা', 'সাধারণ বিজ্ঞান', 'ইতিহাস ও বিশ্বসভ্যতা', 'গণিত অনুশীলন', 'ইংরেজি গ্রামার', 'ভূগোল পরিচিতি', 'ইসলাম শিক্ষা', 'কম্পিউটার বিজ্ঞান', 'রবীন্দ্র রচনাবলী', 'নজরুল রচনাসমগ্র'];
        $books = [];
        for ($i = 0; $i < 40; $i++) {
            $books[] = Book::create([
                'institution_id' => $instId,
                'title' => $titles[$i % count($titles)] . ' - ' . ($i + 1),
                'author' => 'লেখক ' . chr(65 + ($i % 20)),
                'category' => ['একাডেমিক', 'সাহিত্য', 'বিজ্ঞান', 'সাধারণ জ্ঞান'][array_rand(['একাডেমিক', 'সাহিত্য', 'বিজ্ঞান', 'সাধারণ জ্ঞান'])],
                'total_copies' => rand(2, 6),
                'available_copies' => rand(0, 4),
            ]);
        }
        for ($i = 0; $i < 25; $i++) {
            $issued = Carbon::now()->subDays(rand(1, 40));
            $due = $issued->copy()->addDays(14);
            $returned = $i < 18 ? $due->copy()->subDays(rand(0, 10)) : null;
            \App\Models\BookIssue::create([
                'institution_id' => $instId,
                'book_id' => $books[array_rand($books)]->id,
                'student_id' => $students[array_rand($students)]->id,
                'issued_at' => $issued,
                'due_date' => $due,
                'returned_at' => $returned,
                'fine_amount' => (!$returned && $due->isPast()) ? rand(10, 50) : 0,
                'status' => $returned ? 'returned' : ($due->isPast() ? 'overdue' : 'issued'),
            ]);
        }
    }

    private function seedTransport(string $instId, array $students): void
    {
        $routes = [];
        foreach ([['উত্তরা রুট', 'ঢাকা মেট্রো-চ-১১২২৩৩'], ['মিরপুর রুট', 'ঢাকা মেট্রো-চ-৪৪৫৫৬৬'], ['গাজীপুর রুট', 'ঢাকা মেট্রো-চ-৭৭৮৮৯৯']] as [$name, $vehicle]) {
            $routes[] = TransportRoute::create([
                'institution_id' => $instId, 'route_name' => $name, 'vehicle_no' => $vehicle,
                'driver_name' => $this->bnName(true), 'driver_phone' => $this->phone(),
                'capacity' => 40, 'monthly_fee' => rand(8, 18) * 100,
            ]);
        }
        $picked = collect($students)->shuffle()->take(25);
        foreach ($picked as $s) {
            \App\Models\StudentTransport::create([
                'institution_id' => $instId, 'student_id' => $s->id,
                'route_id' => $routes[array_rand($routes)]->id, 'assigned_at' => Carbon::now()->subDays(rand(10, 200)),
            ]);
        }
    }

    private function seedHostel(string $instId, array $students): void
    {
        $rooms = [];
        for ($i = 1; $i <= 6; $i++) {
            $rooms[] = HostelRoom::create([
                'institution_id' => $instId, 'room_no' => 'রুম-' . (100 + $i),
                'room_type' => $i % 2 === 0 ? 'ডাবল' : 'সাধারণ', 'capacity' => 4,
                'monthly_fee' => rand(15, 30) * 100,
            ]);
        }
        $picked = collect($students)->shuffle()->take(12);
        foreach ($picked as $s) {
            \App\Models\StudentHostel::create([
                'institution_id' => $instId, 'student_id' => $s->id,
                'room_id' => $rooms[array_rand($rooms)]->id, 'check_in_date' => Carbon::now()->subDays(rand(10, 200)),
            ]);
        }
    }

    private function seedLeaveRequests(string $instId, array $students, array $teachers, ?string $adminId): void
    {
        $reasons = ['পারিবারিক অনুষ্ঠান', 'অসুস্থতা', 'ব্যক্তিগত কারণ', 'জরুরি প্রয়োজন'];
        $statuses = ['pending', 'approved', 'approved', 'rejected'];
        for ($i = 0; $i < 12; $i++) {
            $s = $students[array_rand($students)];
            $from = Carbon::now()->subDays(rand(0, 40));
            LeaveRequest::create([
                'institution_id' => $instId, 'applicant_type' => 'student', 'student_id' => $s->id,
                'requested_by' => $adminId, 'leave_type' => ['casual', 'sick', 'personal'][array_rand(['casual', 'sick', 'personal'])],
                'date_from' => $from, 'date_to' => $from->copy()->addDays(rand(0, 3)),
                'reason' => $reasons[array_rand($reasons)], 'status' => $statuses[array_rand($statuses)],
            ]);
        }
        for ($i = 0; $i < 6; $i++) {
            $t = $teachers[array_rand($teachers)];
            $from = Carbon::now()->subDays(rand(0, 40));
            LeaveRequest::create([
                'institution_id' => $instId, 'applicant_type' => 'teacher', 'teacher_id' => $t->id,
                'requested_by' => $adminId, 'leave_type' => ['casual', 'sick', 'family'][array_rand(['casual', 'sick', 'family'])],
                'date_from' => $from, 'date_to' => $from->copy()->addDays(rand(0, 2)),
                'reason' => $reasons[array_rand($reasons)], 'status' => $statuses[array_rand($statuses)],
            ]);
        }
    }

    private function seedComplaints(string $instId, ?string $adminId): void
    {
        $items = [
            ['শ্রেণিকক্ষে ফ্যান নষ্ট', 'facility', 'resolved'],
            ['বেতন সংক্রান্ত জিজ্ঞাসা', 'financial', 'in_progress'],
            ['শিক্ষকের অনুপস্থিতি', 'staff', 'open'],
            ['লাইব্রেরি সময়সূচি সমস্যা', 'facility', 'open'],
            ['পরীক্ষার ফলাফল নিয়ে জিজ্ঞাসা', 'academic', 'resolved'],
            ['পরিবহন সময়সূচি', 'facility', 'in_progress'],
        ];
        foreach ($items as [$subject, $cat, $status]) {
            Complaint::create([
                'institution_id' => $instId, 'category' => $cat, 'subject' => $subject,
                'description' => $subject . ' সংক্রান্ত বিস্তারিত অভিযোগ।', 'submitted_by' => $adminId,
                'status' => $status, 'response' => $status !== 'open' ? 'বিষয়টি খতিয়ে দেখা হয়েছে।' : null,
                'resolved_by' => $status === 'resolved' ? $adminId : null,
                'resolved_at' => $status === 'resolved' ? Carbon::now()->subDays(rand(1, 10)) : null,
            ]);
        }
    }

    private function seedLifecycle(string $instId, array $students, ?string $adminId): void
    {
        foreach (collect($students)->shuffle()->take(3) as $i => $s) {
            Certificate::create([
                'institution_id' => $instId, 'student_id' => $s->id, 'type' => $i === 0 ? 'transfer' : 'character',
                'certificate_no' => 'CERT-' . date('Y') . '-' . rand(1000, 9999), 'issue_date' => Carbon::now()->subDays(rand(5, 60)),
                'reason' => 'অভিভাবকের আবেদনক্রমে', 'issued_by' => $adminId,
            ]);
        }
        foreach (collect($students)->shuffle()->take(5) as $s) {
            DisciplineRecord::create([
                'institution_id' => $instId, 'student_id' => $s->id, 'date' => Carbon::now()->subDays(rand(1, 60)),
                'category' => ['attendance', 'behavior', 'academic'][array_rand(['attendance', 'behavior', 'academic'])],
                'severity' => ['minor', 'moderate'][array_rand(['minor', 'moderate'])],
                'description' => 'শ্রেণিকক্ষে শৃঙ্খলা ভঙ্গের ঘটনা।', 'action_taken' => 'অভিভাবককে অবহিত করা হয়েছে।', 'recorded_by' => $adminId,
            ]);
        }
        foreach (collect($students)->shuffle()->take(25) as $s) {
            StudentHealthRecord::create([
                'institution_id' => $instId, 'student_id' => $s->id,
                'height_cm' => rand(130, 165), 'weight_kg' => rand(30, 55),
                'blood_group' => ['A+', 'B+', 'O+', 'AB+'][array_rand(['A+', 'B+', 'O+', 'AB+'])],
                'last_checkup_date' => Carbon::now()->subDays(rand(10, 200)),
            ]);
        }
    }

    private function seedPayrollAndPerformance(string $instId, array $teachers, ?string $adminId): void
    {
        foreach ($teachers as $t) {
            for ($m = 2; $m >= 0; $m--) {
                $month = Carbon::now()->subMonths($m);
                $net = $t->base_salary + $t->house_rent + $t->medical_allowance;
                PayrollRecord::create([
                    'institution_id' => $instId, 'teacher_id' => $t->id,
                    'month' => (int) $month->format('n'), 'year' => (int) $month->format('Y'),
                    'base_salary' => $t->base_salary, 'house_rent' => $t->house_rent, 'medical_allowance' => $t->medical_allowance,
                    'net_pay' => $net, 'status' => $m === 0 ? 'pending' : 'paid',
                    'paid_date' => $m === 0 ? null : $month->copy()->endOfMonth(), 'paid_by' => $adminId,
                ]);
            }
            PerformanceReview::create([
                'institution_id' => $instId, 'teacher_id' => $t->id, 'review_period' => '২০২৬ - প্রথম প্রান্তিক',
                'review_date' => Carbon::now()->subDays(rand(10, 90)),
                'teaching_quality' => rand(3, 5), 'punctuality' => rand(3, 5), 'discipline' => rand(3, 5), 'cooperation' => rand(3, 5),
                'reviewed_by' => $adminId,
            ]);
        }
    }

    private function seedAdmissionApplications(string $instId, array $classes): void
    {
        $statuses = ['pending', 'test_scheduled', 'shortlisted', 'waiting', 'accepted', 'rejected'];
        for ($i = 0; $i < 15; $i++) {
            $male = rand(0, 1) === 1;
            AdmissionApplication::create([
                'institution_id' => $instId, 'applicant_name' => $this->bnName($male),
                'guardian_name' => $this->bnName(true), 'guardian_phone' => $this->phone(),
                'date_of_birth' => Carbon::now()->subYears(rand(6, 12)), 'gender' => $male ? 'male' : 'female',
                'applying_class_id' => $classes[array_rand($classes)]->id,
                'status' => $statuses[array_rand($statuses)],
            ]);
        }
    }

    private function seedAcademicSession(string $instId): void
    {
        AcademicSession::create([
            'institution_id' => $instId, 'name' => '২০২৬ শিক্ষাবর্ষ',
            'start_date' => Carbon::create(2026, 1, 1), 'end_date' => Carbon::create(2026, 12, 31), 'is_current' => true,
        ]);
    }

    private function seedHomeworkAndPlans(string $instId, array $classes, array $sections, array $subjects, array $teachers): void
    {
        for ($i = 0; $i < 10; $i++) {
            $c = $classes[array_rand($classes)];
            Homework::create([
                'institution_id' => $instId, 'title' => $subjects[$i % count($subjects)]->name . ' — অনুশীলনী',
                'class_id' => $c->id, 'subject_id' => $subjects[$i % count($subjects)]->id,
                'teacher_id' => $teachers[array_rand($teachers)]->id,
                'assigned_date' => Carbon::now()->subDays(rand(0, 10)), 'due_date' => Carbon::now()->addDays(rand(1, 7)),
            ]);
            LessonPlan::create([
                'institution_id' => $instId, 'title' => $subjects[$i % count($subjects)]->name . ' — পাঠ পরিকল্পনা',
                'class_id' => $c->id, 'subject_id' => $subjects[$i % count($subjects)]->id,
                'teacher_id' => $teachers[array_rand($teachers)]->id, 'date' => Carbon::now()->addDays(rand(1, 14)),
                'objectives' => 'শিক্ষার্থীরা মূল ধারণা বুঝতে পারবে।',
            ]);
        }
    }

    private function seedQuestionBank(string $instId, array $classes, array $subjects): void
    {
        for ($i = 0; $i < 20; $i++) {
            QuestionBankItem::create([
                'institution_id' => $instId, 'class_id' => $classes[array_rand($classes)]->id,
                'subject_id' => $subjects[array_rand($subjects)]->id,
                'question_type' => ['mcq', 'short', 'essay'][array_rand(['mcq', 'short', 'essay'])],
                'difficulty' => ['easy', 'medium', 'hard'][array_rand(['easy', 'medium', 'hard'])],
                'question_text' => 'নমুনা প্রশ্ন নম্বর ' . ($i + 1),
                'marks' => rand(1, 10),
            ]);
        }
    }

    private function seedScholarships(string $instId, array $students, ?string $adminId): void
    {
        foreach (collect($students)->shuffle()->take(8) as $s) {
            Scholarship::create([
                'institution_id' => $instId, 'student_id' => $s->id,
                'type' => ['scholarship', 'waiver'][array_rand(['scholarship', 'waiver'])],
                'discount_mode' => 'percentage', 'discount_value' => [25, 50, 100][array_rand([25, 50, 100])],
                'reason' => 'শিক্ষাবৃত্তি — মেধাক্রম ভিত্তিক', 'status' => 'active',
                'valid_from' => Carbon::now()->subMonths(3), 'approved_by' => $adminId,
            ]);
        }
    }
}
