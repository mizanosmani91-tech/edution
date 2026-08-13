<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('landing'))->name('landing');
Route::get('/register', [\App\Http\Controllers\Auth\RegistrationController::class, 'create'])->name('register');
Route::post('/register', [\App\Http\Controllers\Auth\RegistrationController::class, 'store'])->name('register.store');

Route::get('/login', function () {
    $institution = \App\Models\Institution::resolveFromSubdomain(request()->getHost());
    return view('auth.login', ['institution' => $institution]);
})->name('login');
Route::post('/login', [LoginController::class, 'store']);
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'tenant.context'])->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    // ── API-style JSON রুট — নাম আলাদা রাখা হলো 'api.' prefix দিয়ে, যেন
    Route::get('/students/admission', \App\Livewire\StudentAdmissionWizard::class)->name('students.admission');
    Route::get('/academic/classes', \App\Livewire\ClassSectionManager::class)->name('academic.classes');
    Route::get('/academic/departments', \App\Livewire\DepartmentManager::class)->name('academic.departments');
    Route::get('/academic/subjects', \App\Livewire\SubjectManager::class)->name('academic.subjects');
    // Livewire পেজ রুটের নামের সাথে conflict না হয় ──
    Route::resource('students', StudentController::class)
        ->except(['create', 'edit'])
        ->names('api.students');
    Route::get('/teachers/hire', \App\Livewire\TeacherAdmissionWizard::class)->name('teachers.hire');
    Route::resource('teachers', \App\Http\Controllers\TeacherController::class)
        ->except(['create', 'edit'])
        ->names('api.teachers');

    Route::get('fee-collections/due', [\App\Http\Controllers\FeeCollectionController::class, 'due']);
    Route::get('fee-collections/{feeCollection}/receipt', [\App\Http\Controllers\FeeCollectionController::class, 'receipt'])->name('fee-collections.receipt');
    Route::resource('fee-collections', \App\Http\Controllers\FeeCollectionController::class)
        ->except(['create', 'edit', 'destroy'])
        ->names('api.fee-collections');

    Route::get('attendance', [\App\Http\Controllers\AttendanceController::class, 'index']);
    Route::post('attendance/bulk', [\App\Http\Controllers\AttendanceController::class, 'bulkStore']);
    Route::get('attendance/report', [\App\Http\Controllers\AttendanceController::class, 'studentReport']);

    Route::get('exam-weightings/effective-marks', [\App\Http\Controllers\ExamResultWeightingController::class, 'effectiveMarks']);
    Route::apiResource('exam-weightings', \App\Http\Controllers\ExamResultWeightingController::class)
        ->only(['index', 'store', 'destroy'])
        ->names('api.exam-weightings');

    Route::prefix('chat')->controller(\App\Http\Controllers\ChatController::class)->group(function () {
        Route::get('conversations', 'index');
        Route::post('conversations/start', 'start');
        Route::get('conversations/{conversation}/messages', 'messages');
        Route::post('conversations/{conversation}/messages', 'sendMessage');
        Route::post('conversations/{conversation}/read', 'markRead');
    });

    Route::get('settings', [\App\Http\Controllers\InstitutionSettingController::class, 'show']);
    Route::put('settings', [\App\Http\Controllers\InstitutionSettingController::class, 'update']);

    Route::get('routine', [\App\Http\Controllers\RoutinePeriodController::class, 'index']);
    Route::post('routine', [\App\Http\Controllers\RoutinePeriodController::class, 'store']);
    Route::delete('routine/{routinePeriod}', [\App\Http\Controllers\RoutinePeriodController::class, 'destroy']);

    // ── Livewire পেজ রুট (bottom-nav/sidebar এর টার্গেট, নাম এখন ইউনিক) ──
    Route::get('/students-page', \App\Livewire\StudentList::class)->name('students.index');
    Route::get('/students-page/{student}', \App\Livewire\StudentProfile::class)->name('students.profile');
    Route::get('/teachers-page', \App\Livewire\TeacherList::class)->name('teachers.index');
    Route::get('/teachers-page/{teacher}', \App\Livewire\TeacherProfile::class)->name('teachers.profile');
    Route::get('/fees-page', \App\Livewire\FeeCollectionList::class)->name('fees.index');
    Route::get('/attendance-page', \App\Livewire\AttendanceTaker::class)->name('attendance.index');
    Route::get('/staff-attendance-page', \App\Livewire\StaffAttendanceTaker::class)->name('staff-attendance.index');
    Route::get('/chat-page', \App\Livewire\ChatWindow::class)->name('chat.index');
    Route::get('/notice-board-page', \App\Livewire\NoticeBoard::class)->name('notice-board.index');
    Route::get('/id-cards-page', \App\Livewire\IdCardGenerator::class)->name('id-cards.index');
    Route::get('/attendance-report-page', \App\Livewire\AttendanceReport::class)->name('attendance-report.index');
    Route::get('/fee-structures-page', \App\Livewire\FeeStructureManager::class)->name('fee-structures.index');
    Route::get('/expenses-page', \App\Livewire\ExpenseTracker::class)->name('expenses.index');
    Route::get('/income-expense-report-page', \App\Livewire\IncomeExpenseReport::class)->name('income-expense-report.index');
    Route::get('/books-page', \App\Livewire\BookManager::class)->name('books.index');
    Route::get('/book-issues-page', \App\Livewire\BookIssueManager::class)->name('book-issues.index');
    Route::get('/transport-page', \App\Livewire\TransportManager::class)->name('transport.index');
    Route::get('/hostel-page', \App\Livewire\HostelManager::class)->name('hostel.index');
    Route::get('/settings-page', \App\Livewire\InstitutionSettingsForm::class)->name('settings.index');
    Route::get('/routine-page', \App\Livewire\RoutineBoard::class)->name('routine.index');

    Route::get('/portal/guardian', \App\Livewire\GuardianPortal::class)->name('portal.guardian');
    Route::get('/portal/teacher', \App\Livewire\TeacherPortal::class)->name('portal.teacher');
    Route::get('/portal/student', \App\Livewire\StudentPortal::class)->name('portal.student');

    Route::get('/marksheet/class', [\App\Http\Controllers\MarksheetController::class, 'classMarksheet'])->name('marksheet.class');
    Route::get('/marksheet/student/{student}', [\App\Http\Controllers\MarksheetController::class, 'studentMarksheet'])->name('marksheet.student');
    Route::get('/admit-cards/class', [\App\Http\Controllers\AdmitCardController::class, 'classAdmitCards'])->name('admit-cards.class');

    Route::get('/export/students', [\App\Http\Controllers\ExportController::class, 'students'])->name('export.students');
    Route::get('/export/attendance', [\App\Http\Controllers\ExportController::class, 'attendance'])->name('export.attendance');
    Route::get('/export/fees', [\App\Http\Controllers\ExportController::class, 'fees'])->name('export.fees');

    Route::post('/import/students', [\App\Http\Controllers\BulkImportController::class, 'importStudents'])->name('import.students');

    Route::get('/exams', [\App\Http\Controllers\ExamController::class, 'index']);
    Route::post('/exams', [\App\Http\Controllers\ExamController::class, 'store']);
    Route::post('/exams/{exam}/publish', [\App\Http\Controllers\ExamController::class, 'publish'])->name('exams.publish');

    Route::get('/leave-requests', \App\Livewire\LeaveRequestsList::class)->name('leave-requests.index');
    Route::get('/coming-soon/{title}', [\App\Http\Controllers\ComingSoonController::class, 'show'])->name('stub');
});

Route::domain('panel.edution.xyz')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Auth\SuperadminLoginController::class, 'create'])->name('superadmin.login');
    Route::post('/login', [\App\Http\Controllers\Auth\SuperadminLoginController::class, 'store'])->name('superadmin.login.store');
});

Route::middleware(['auth', 'superadmin'])->domain('panel.edution.xyz')->group(function () {
    Route::get('/', \App\Livewire\SuperadminInstitutionsList::class)->name('superadmin.institutions');
});
