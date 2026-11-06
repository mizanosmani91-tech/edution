<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// ⚠️ panel.edution.xyz domain-scoped রুট সবার আগে রেজিস্টার করা জরুরি —
// Laravel domain-restricted রুটকে বেশি স্পেসিফিক ধরে অগ্রাধিকার দেয় না,
// শুধু রেজিস্ট্রেশন অর্ডার অনুযায়ী প্রথম ম্যাচ জেতে। এটা নিচে থাকলে
// panel.edution.xyz/login ও ভুলভাবে সাধারণ (নন-ডোমেইন) /login রুটে চলে যেত।
Route::domain('panel.edution.xyz')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Auth\SuperadminLoginController::class, 'create'])->name('superadmin.login');
    Route::post('/login', [\App\Http\Controllers\Auth\SuperadminLoginController::class, 'store'])->name('superadmin.login.store');

    Route::get('/forgot-password', [\App\Http\Controllers\Auth\SuperadminForgotPasswordController::class, 'create'])->name('superadmin.password.forgot');
    Route::post('/forgot-password/send-code', [\App\Http\Controllers\Auth\SuperadminForgotPasswordController::class, 'sendCode'])
        ->middleware('throttle:5,1')->name('superadmin.password.forgot.send');
    Route::post('/forgot-password/reset', [\App\Http\Controllers\Auth\SuperadminForgotPasswordController::class, 'reset'])
        ->middleware('throttle:10,1')->name('superadmin.password.forgot.reset');
});

Route::middleware(['auth', 'superadmin'])->domain('panel.edution.xyz')->group(function () {
    Route::get('/password/change', \App\Livewire\ForcePasswordChange::class)->name('superadmin.password.force-change');
});

Route::middleware(['auth', 'superadmin', 'password.change'])->domain('panel.edution.xyz')->group(function () {
    Route::get('/', \App\Livewire\SuperadminDashboard::class)->name('superadmin.institutions');
});


// PWA manifest — কোনো auth/tenant middleware ছাড়াই, যেকোনো সাবডোমেইন/ডোমেইন
// থেকে অ্যাক্সেসযোগ্য হতে হবে (লগইন পেজ থেকেও ইনস্টল করা যাবে বলে)
Route::get('/manifest.webmanifest', [\App\Http\Controllers\PwaController::class, 'manifest'])->name('pwa.manifest');

Route::get('/locale/{locale}', function (string $locale) {
        if (in_array($locale, ['bn', 'en'], true)) {
                    session(['locale' => $locale]);
        }
                return back();
})->name('locale.switch');

// প্রতিষ্ঠানের পাবলিক প্রোফাইল পেজ (লগইন ছাড়াই দেখা যায়) — Facebook/গুগলে
// শেয়ার করার জন্য। মূল '/' রুট ইচ্ছাকৃতভাবে বদলানো হয়নি, এটা আলাদা path।
Route::get('/school-profile', [\App\Http\Controllers\PublicSiteController::class, 'show'])->name('public-site.show');

// গাড়ির লাইভ GPS ট্র্যাকিং — ড্রাইভারের পাবলিক শেয়ার লিংক (auth ছাড়াই খোলা যায়,
// tracking_token একটা ৩২-ক্যারেক্টার র‍্যান্ডম, unique টোকেন দিয়ে সুরক্ষিত)
Route::get('/transport-tracking/{token}', [\App\Http\Controllers\VehicleTrackingController::class, 'share'])->name('transport-tracking.share');
Route::post('/transport-tracking/{token}/update', [\App\Http\Controllers\VehicleTrackingController::class, 'update'])->name('transport-tracking.update');

// সার্টিফিকেটের QR কোড স্ক্যান করলে এখানে আসে — লগইন ছাড়াই যেকোনো
// ব্যক্তি (নিয়োগকর্তা, অন্য প্রতিষ্ঠান) সার্টিফিকেটের সত্যতা যাচাই করতে পারবে।
Route::get('/verify/certificate/{id}', [\App\Http\Controllers\CertificateVerificationController::class, 'show'])->name('certificate.verify');

// ⚠️ প্রতিষ্ঠানের নিজস্ব সাবডোমেইনে (যেমন annazah.edution.xyz) রুট পেজে
// সাধারণ landing page না দেখিয়ে সরাসরি সেই প্রতিষ্ঠানের লগইন পেজ দেখানো হয়।
// মূল ডোমেইন (edution.xyz/www) বা অচেনা সাবডোমেইনে যথারীতি landing page।
Route::get('/', function () {
    $institution = \App\Models\Institution::resolveFromSubdomain(request()->getHost());

    if ($institution) {
        return view('auth.login', ['institution' => $institution]);
    }

    return view('landing');
})->name('landing');
Route::get('/register', [\App\Http\Controllers\Auth\RegistrationController::class, 'create'])->name('register');
Route::post('/register', [\App\Http\Controllers\Auth\RegistrationController::class, 'store'])->name('register.store');

// ⚠️ রেজিস্ট্রেশনের সময় মোবাইল নম্বর OTP যাচাই — throttle দিয়ে abuse ঠেকানো হলো
Route::post('/register/send-otp', [\App\Http\Controllers\Auth\OtpController::class, 'send'])
    ->middleware('throttle:5,1')->name('register.otp.send');
Route::post('/register/verify-otp', [\App\Http\Controllers\Auth\OtpController::class, 'verify'])
    ->middleware('throttle:10,1')->name('register.otp.verify');
Route::get('/register/check-slug', [\App\Http\Controllers\Auth\RegistrationController::class, 'checkSlug'])
    ->middleware('throttle:30,1')->name('register.check-slug');

Route::get('/login', function () {
    $institution = \App\Models\Institution::resolveFromSubdomain(request()->getHost());
    return view('auth.login', ['institution' => $institution]);
})->name('login');
Route::post('/login', [LoginController::class, 'store']);

// ⚠️ পাবলিক ডেমো — সফট রেজিস্ট্রেশন + অভিভাবক/শিক্ষক ডেমো এক্সেস রিকোয়েস্ট।
// প্লেইন controller, guest-facing, সবগুলোতে throttle (abuse ঠেকাতে)।
Route::post('/demo/register', [\App\Http\Controllers\DemoAccessController::class, 'register'])
    ->middleware('throttle:10,1')->name('demo.register');
Route::get('/demo/status', [\App\Http\Controllers\DemoAccessController::class, 'status'])
    ->middleware('throttle:30,1')->name('demo.status');
Route::post('/demo/request-access', [\App\Http\Controllers\DemoAccessController::class, 'requestAccess'])
    ->middleware('throttle:10,1')->name('demo.request-access');
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// ⚠️ প্লেইন controller + fetch() (Livewire না) — কারণ Livewire-এর shared
// /livewire/update রুটে 'auth' middleware বাধ্যতামূলক, কিন্তু এই ফ্লো
// ব্যবহার করে লগইন-ই না করা ইউজার (ঠিক registration OTP-র মতো কারণে)।
Route::get('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'create'])->name('password.forgot');
Route::post('/forgot-password/send-code', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendCode'])
    ->middleware('throttle:5,1')->name('password.forgot.send');
Route::post('/forgot-password/send-sms-backup', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendSmsBackup'])
    ->middleware('throttle:5,1')->name('password.forgot.sms');
Route::post('/forgot-password/reset', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'reset'])
    ->middleware('throttle:10,1')->name('password.forgot.reset');

Route::middleware(['auth', 'tenant.context'])->group(function () {
    Route::get('/password/change', \App\Livewire\ForcePasswordChange::class)->name('password.force-change');
});

Route::middleware(['auth', 'tenant.context', 'password.change'])->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
    Route::get('/my-profile', \App\Livewire\MyProfile::class)->name('my-profile');
    Route::get('/users', \App\Livewire\UserManager::class)->name('users.index')->middleware('permission:users.manage');

    // ── API-style JSON রুট — নাম আলাদা রাখা হলো 'api.' prefix দিয়ে, যেন
    Route::get('/students/admission', \App\Livewire\StudentAdmissionWizard::class)->name('students.admission')->middleware('permission:admission.manage');
    Route::get('/academic/classes', \App\Livewire\ClassSectionManager::class)->name('academic.classes')->middleware('permission:academic.manage');
    Route::get('/academic/departments', \App\Livewire\DepartmentManager::class)->name('academic.departments')->middleware('permission:academic.manage');
    Route::get('/academic/subjects', \App\Livewire\SubjectManager::class)->name('academic.subjects')->middleware('permission:academic.manage');
    // Livewire পেজ রুটের নামের সাথে conflict না হয় ──
    Route::resource('students', StudentController::class)
        ->except(['create', 'edit'])
        ->names('api.students')->middleware('permission:students.manage');
    Route::get('/teachers/hire', \App\Livewire\TeacherAdmissionWizard::class)->name('teachers.hire')->middleware('permission:teachers.manage');
    Route::resource('teachers', \App\Http\Controllers\TeacherController::class)
        ->except(['create', 'edit'])
        ->names('api.teachers')->middleware('permission:teachers.manage');

    Route::get('fee-collections/due', [\App\Http\Controllers\FeeCollectionController::class, 'due'])->middleware('permission:fees.manage');
    Route::get('fee-collections/{feeCollection}/receipt', [\App\Http\Controllers\FeeCollectionController::class, 'receipt'])->name('fee-collections.receipt')->middleware('permission:fees.receipt');
    Route::resource('fee-collections', \App\Http\Controllers\FeeCollectionController::class)
        ->except(['create', 'edit', 'destroy'])
        ->names('api.fee-collections')->middleware('permission:fees.manage');

    Route::get('attendance', [\App\Http\Controllers\AttendanceController::class, 'index'])->middleware('permission:attendance.take');
    Route::post('attendance/bulk', [\App\Http\Controllers\AttendanceController::class, 'bulkStore'])->middleware('permission:attendance.take');
    Route::get('attendance/report', [\App\Http\Controllers\AttendanceController::class, 'studentReport'])->middleware('permission:attendance.take');

    Route::get('exam-weightings/effective-marks', [\App\Http\Controllers\ExamResultWeightingController::class, 'effectiveMarks'])->middleware('permission:exams.results');
    Route::apiResource('exam-weightings', \App\Http\Controllers\ExamResultWeightingController::class)
        ->only(['index', 'store', 'destroy'])
        ->names('api.exam-weightings')->middleware('permission:exams.results');

    Route::prefix('chat')->middleware('permission:chat.use')->controller(\App\Http\Controllers\ChatController::class)->group(function () {
        Route::get('conversations', 'index');
        Route::post('conversations/start', 'start');
        Route::get('conversations/{conversation}/messages', 'messages');
        Route::post('conversations/{conversation}/messages', 'sendMessage');
        Route::post('conversations/{conversation}/read', 'markRead');
    });

    Route::get('settings', [\App\Http\Controllers\InstitutionSettingController::class, 'show']);
    Route::put('settings', [\App\Http\Controllers\InstitutionSettingController::class, 'update'])->middleware('permission:settings.manage');

    Route::get('routine', [\App\Http\Controllers\RoutinePeriodController::class, 'index']);
    Route::post('routine', [\App\Http\Controllers\RoutinePeriodController::class, 'store'])->middleware('permission:academic.manage');
    Route::delete('routine/{routinePeriod}', [\App\Http\Controllers\RoutinePeriodController::class, 'destroy'])->middleware('permission:academic.manage');

    // ── Livewire পেজ রুট (bottom-nav/sidebar এর টার্গেট, নাম এখন ইউনিক) ──
    Route::get('/students-page', \App\Livewire\StudentList::class)->name('students.index')->middleware('permission:students.manage');
    Route::get('/students-page/{student}', \App\Livewire\StudentProfile::class)->name('students.profile')->middleware('permission:students.profile');
    Route::get('/students-page/{student}/edit', \App\Livewire\StudentEditForm::class)->name('students.edit')->middleware('permission:students.manage');
    Route::get('/teachers-page', \App\Livewire\TeacherList::class)->name('teachers.index')->middleware('permission:teachers.manage');
    Route::get('/teachers-page/{teacher}', \App\Livewire\TeacherProfile::class)->name('teachers.profile')->middleware('permission:teachers.manage');
    Route::get('/teachers-page/{teacher}/edit', \App\Livewire\TeacherEditForm::class)->name('teachers.edit')->middleware('permission:teachers.manage');
    Route::get('/fees-page', \App\Livewire\FeeCollectionList::class)->name('fees.index')->middleware('permission:fees.manage');
    Route::get('/fee-installment-plans', \App\Livewire\FeeInstallmentPlans::class)->name('fee-installments.index')->middleware('permission:fees.manage');
    Route::get('/attendance-page', \App\Livewire\AttendanceTaker::class)->name('attendance.index')->middleware('permission:attendance.take');
    Route::get('/staff-attendance-page', \App\Livewire\StaffAttendanceTaker::class)->name('staff-attendance.index')->middleware('permission:staff-attendance.manage');
    Route::get('/chat-page', \App\Livewire\ChatWindow::class)->name('chat.index')->middleware('permission:chat.use');
    Route::get('/notice-board-page', \App\Livewire\NoticeBoard::class)->name('notice-board.index')->middleware('permission:notices.manage');
    Route::get('/academic-calendar', \App\Livewire\AcademicCalendar::class)->name('academic-calendar.index')->middleware('permission:calendar.view');
    Route::get('/id-cards-page', \App\Livewire\IdCardGenerator::class)->name('id-cards.index')->middleware('permission:students.manage');
    Route::get('/attendance-report-page', \App\Livewire\AttendanceReport::class)->name('attendance-report.index')->middleware('permission:attendance.take');
    Route::get('/fee-structures-page', \App\Livewire\FeeStructureManager::class)->name('fee-structures.index')->middleware('permission:fees.manage');
    Route::get('/expenses-page', \App\Livewire\ExpenseTracker::class)->name('expenses.index')->middleware('permission:finance.manage');
    Route::get('/income-expense-report-page', \App\Livewire\IncomeExpenseReport::class)->name('income-expense-report.index')->middleware('permission:finance.manage');
    Route::get('/budget-page', \App\Livewire\BudgetManager::class)->name('budget.index')->middleware('permission:finance.manage');
    Route::get('/books-page', \App\Livewire\BookManager::class)->name('books.index')->middleware('permission:library.manage');
    Route::get('/book-issues-page', \App\Livewire\BookIssueManager::class)->name('book-issues.index')->middleware('permission:library.manage');
    Route::get('/book-fines-page', \App\Livewire\BookIssueManager::class)->name('book-fines.index')->defaults('tab', 'overdue')->middleware('permission:library.manage');
    Route::get('/transport-page', \App\Livewire\TransportManager::class)->name('transport.index')->middleware('permission:transport.manage');
    Route::get('/transport-assignment-page', \App\Livewire\TransportManager::class)->name('transport-assignment.index')->defaults('tab', 'assignments')->middleware('permission:transport.manage');
    Route::get('/transport-tracking-page', \App\Livewire\VehicleTrackingMap::class)->name('transport-tracking.index')->middleware('permission:transport.track');
    Route::get('/transport-tracking/positions', [\App\Http\Controllers\VehicleTrackingController::class, 'positions'])->name('transport-tracking.positions')->middleware('permission:transport.track');
    Route::get('/hostel-page', \App\Livewire\HostelManager::class)->name('hostel.index')->middleware('permission:hostel.manage');
    Route::get('/hostel-fees-page', \App\Livewire\HostelManager::class)->name('hostel-fees.index')->defaults('tab', 'fees')->middleware('permission:hostel.manage');
    Route::get('/complaints-page', \App\Livewire\ComplaintBox::class)->name('complaints.index')->middleware('permission:complaints.use');
    Route::get('/support-tickets', \App\Livewire\TenantSupportTickets::class)->name('support-tickets.index')->middleware('permission:support.manage');
    Route::get('/student-promotion', \App\Livewire\StudentPromotion::class)->name('student-promotion.index')->middleware('permission:students.manage');
    Route::get('/certificate/transfer', \App\Livewire\CertificateGenerator::class)->name('certificates.transfer')->defaults('type', 'transfer')->middleware('permission:students.manage');
    Route::get('/certificate/character', \App\Livewire\CertificateGenerator::class)->name('certificates.character')->defaults('type', 'character')->middleware('permission:students.manage');
    Route::get('/discipline-records', \App\Livewire\DisciplineRecords::class)->name('discipline.index')->middleware('permission:students.manage');
    Route::get('/student-health', \App\Livewire\StudentHealth::class)->name('student-health.index')->middleware('permission:students.manage');
    Route::get('/alumni', \App\Livewire\AlumniDirectory::class)->name('alumni.index')->middleware('permission:students.manage');
    Route::get('/payroll', \App\Livewire\PayrollManager::class)->name('payroll.index')->middleware('permission:payroll.manage');
    Route::get('/performance-review', \App\Livewire\PerformanceReviewManager::class)->name('performance.index')->middleware('permission:performance.manage');
    Route::get('/exam-schedule', \App\Livewire\ExamScheduleManager::class)->name('exam-schedule.index')->middleware('permission:exams.schedule');
    Route::get('/marks-entry', \App\Livewire\MarksEntry::class)->name('marks-entry.index')->middleware('permission:exams.marks');
    Route::get('/merit-list', \App\Livewire\MeritList::class)->name('merit-list.index')->defaults('mode', 'standard')->middleware('permission:exams.results');
    Route::get('/qawmi-grading', \App\Livewire\MeritList::class)->name('qawmi-grading.index')->defaults('mode', 'qawmi')->middleware('permission:exams.results');
    Route::get('/admission-applications', \App\Livewire\AdmissionApplicationManager::class)->name('admission-applications.index')->defaults('view', 'all')->middleware('permission:admission.manage');
    Route::get('/seat-management', \App\Livewire\SeatManagement::class)->name('seat-management.index')->middleware('permission:admission.manage');
    Route::get('/entrance-test', \App\Livewire\AdmissionApplicationManager::class)->name('entrance-test.index')->defaults('view', 'test')->middleware('permission:admission.manage');
    Route::get('/admission-waiting-list', \App\Livewire\AdmissionApplicationManager::class)->name('admission-waiting.index')->defaults('view', 'waiting')->middleware('permission:admission.manage');
    Route::get('/payment-gateway-settings', \App\Livewire\PaymentGatewaySettings::class)->name('payment-gateway.index')->middleware('permission:gateway.manage');
    Route::get('/notification-gateway-settings', \App\Livewire\NotificationGatewaySettings::class)->name('notification-gateway.index')->middleware('permission:gateway.manage');
    Route::get('/settings-page', \App\Livewire\InstitutionSettingsForm::class)->name('settings.index')->middleware('permission:settings.manage');
    Route::get('/audit-log', \App\Livewire\AuditLogViewer::class)->name('audit-log.index')->middleware('permission:audit.view');
    Route::get('/billing', \App\Livewire\BillingCenter::class)->name('billing.index')->middleware('permission:billing.manage');
    Route::get('/routine-page', \App\Livewire\RoutineBoard::class)->name('routine.index')->middleware('permission:academic.manage');
    Route::get('/live-class-monitor', \App\Livewire\LiveClassMonitor::class)->name('live-class-monitor.index')->middleware('permission:academic.manage');

    Route::get('/portal/guardian', \App\Livewire\GuardianPortal::class)->name('portal.guardian')->middleware('permission:portal.guardian');
    Route::get('/online-payment/{feeCollection}/initiate', [\App\Http\Controllers\OnlinePaymentController::class, 'initiate'])->name('online-payment.initiate')->middleware('permission:fees.pay');
    Route::get('/online-payment/callback', [\App\Http\Controllers\OnlinePaymentController::class, 'callback'])->name('online-payment.callback')->middleware('permission:fees.pay');
    Route::get('/portal/teacher', \App\Livewire\TeacherPortal::class)->name('portal.teacher')->middleware('permission:portal.teacher');
    Route::get('/portal/student', \App\Livewire\StudentPortal::class)->name('portal.student')->middleware('permission:portal.student');

    Route::get('/marksheet/class', [\App\Http\Controllers\MarksheetController::class, 'classMarksheet'])->name('marksheet.class')->middleware('permission:exams.results');
    Route::get('/marksheet/class-tabulation', [\App\Http\Controllers\MarksheetController::class, 'classTabulation'])->name('marksheet.class-tabulation')->middleware('permission:exams.results');
    Route::get('/exam-seat-plan', \App\Livewire\ExamSeatPlanManager::class)->name('exam-seat-plan.index')->middleware('permission:exams.documents');
    Route::get('/exam-seat-plan/print', [\App\Http\Controllers\ExamSeatPlanController::class, 'print'])->name('exam-seat-plan.print')->middleware('permission:exams.documents');
    Route::get('/exam-attendance/print', [\App\Http\Controllers\ExamSeatPlanController::class, 'attendance'])->name('exam-attendance.print')->middleware('permission:exams.documents');
    Route::get('/exam-hall-duty/print', [\App\Http\Controllers\ExamSeatPlanController::class, 'hallDuty'])->name('exam-hall-duty.print')->middleware('permission:exams.documents');
    Route::get('/answer-sheet-distribution/print', [\App\Http\Controllers\AnswerSheetDistributionController::class, 'print'])->name('answer-sheet-distribution.print')->middleware('permission:exams.documents');
    Route::get('/question-papers', \App\Livewire\QuestionPaperBuilder::class)->name('question-papers.index')->middleware('permission:exams.papers');
    Route::get('/question-papers/{questionPaper}/print', [\App\Http\Controllers\QuestionPaperController::class, 'print'])->name('question-papers.print')->middleware('permission:exams.papers');
    Route::get('/exam-documents', \App\Livewire\ExamDocumentCenter::class)->name('exam-documents.index')->middleware('permission:exams.documents');
    Route::get('/marksheet/student/{student}', [\App\Http\Controllers\MarksheetController::class, 'studentMarksheet'])->name('marksheet.student')->middleware('permission:marksheet.student');
    Route::get('/admit-cards/class', [\App\Http\Controllers\AdmitCardController::class, 'classAdmitCards'])->name('admit-cards.class')->middleware('permission:exams.documents');
    Route::get('/report-cards', \App\Livewire\ReportCardCenter::class)->name('report-cards.index')->middleware('permission:exams.results');
    Route::get('/import/students', \App\Livewire\DataImporter::class)->name('import.students')->defaults('entity', 'students')->middleware('permission:data.import');
    Route::get('/import/teachers', \App\Livewire\DataImporter::class)->name('import.teachers')->defaults('entity', 'teachers')->middleware('permission:data.import');
    Route::get('/import/fees', \App\Livewire\DataImporter::class)->name('import.fees')->defaults('entity', 'fees')->middleware('permission:data.import');
    Route::get('/import/exam-results', \App\Livewire\DataImporter::class)->name('import.exam-results')->defaults('entity', 'exam-results')->middleware('permission:data.import');
    Route::get('/import/attendance-device', \App\Livewire\DataImporter::class)->name('import.attendance-device')->defaults('entity', 'attendance-device')->middleware('permission:data.import');
    Route::get('/import/{entity}/sample', [\App\Http\Controllers\ImportSampleController::class, 'download'])->name('import.sample')->middleware('permission:data.import');
    Route::get('/academic-sessions', \App\Livewire\AcademicSessionManager::class)->name('academic-sessions.index')->middleware('permission:academic.manage');
    Route::get('/homework', \App\Livewire\HomeworkManager::class)->name('homework.index')->middleware('permission:homework.manage');
    Route::get('/class-diary', \App\Livewire\ClassDiaryManager::class)->name('class-diary.index')->middleware('permission:classdiary.manage');
    Route::get('/lesson-plans', \App\Livewire\LessonPlanManager::class)->name('lesson-plans.index')->middleware('permission:lessons.manage');
    Route::get('/hifz-progress', \App\Livewire\HifzProgressManager::class)->name('hifz-progress.index')->middleware('permission:hifz.manage');
    Route::get('/question-bank', \App\Livewire\QuestionBankManager::class)->name('question-bank.index')->middleware('permission:questionbank.manage');
    Route::get('/quizzes', \App\Livewire\QuizManager::class)->name('quizzes.index')->middleware('permission:quizzes.manage');
    Route::get('/visitors', \App\Livewire\VisitorLog::class)->name('visitors.index')->middleware('permission:visitors.manage');
    Route::get('/inventory', \App\Livewire\InventoryManager::class)->name('inventory.index')->middleware('permission:inventory.manage');
    Route::get('/inventory-issues', \App\Livewire\InventoryIssueManager::class)->name('inventory-issues.index')->middleware('permission:inventory.manage');
    Route::get('/my-quizzes', \App\Livewire\StudentQuizList::class)->name('student-quizzes.index')->middleware('permission:quizzes.take');
    Route::get('/my-quizzes/{quiz}', \App\Livewire\TakeQuiz::class)->name('student-quizzes.take')->middleware('permission:quizzes.take');
    Route::get('/result-weighting', \App\Livewire\ResultWeightingManager::class)->name('result-weighting.index')->middleware('permission:exams.results');
    Route::get('/scholarships', \App\Livewire\ScholarshipManager::class)->name('scholarships.index')->middleware('permission:fees.manage');

    Route::get('/export/students', [\App\Http\Controllers\ExportController::class, 'students'])->name('export.students')->middleware('permission:data.export');
    Route::get('/export/attendance', [\App\Http\Controllers\ExportController::class, 'attendance'])->name('export.attendance')->middleware('permission:data.export');
    Route::get('/export/fees', [\App\Http\Controllers\ExportController::class, 'fees'])->name('export.fees')->middleware('permission:data.export');


    Route::get('/exams', [\App\Http\Controllers\ExamController::class, 'index'])->middleware('permission:exams.schedule');
    Route::post('/exams', [\App\Http\Controllers\ExamController::class, 'store'])->middleware('permission:exams.schedule');
    Route::post('/exams/{exam}/publish', [\App\Http\Controllers\ExamController::class, 'publish'])->name('exams.publish')->middleware('permission:exams.schedule');

    Route::get('/leave-requests', \App\Livewire\LeaveRequestsList::class)->name('leave-requests.index')->middleware('permission:leave.manage');
    Route::get('/coming-soon/{title}', [\App\Http\Controllers\ComingSoonController::class, 'show'])->name('stub');
});
