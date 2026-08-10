<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// ─── Public / Auth রুট (tenant middleware ছাড়া — কারণ এখনো লগইন হয়নি) ───
Route::get('/login', fn () => view('auth.login'))->name('login');
Route::post('/login', [LoginController::class, 'store']);
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth');

// ─── Tenant-protected রুট ───────────────────────────────────────────────
// ⚠️ Middleware অর্ডার গুরুত্বপূর্ণ: 'auth' আগে (user resolve করে),
// তারপর 'tenant.context' (SetTenantContext — user->institution_id পড়ে
// session variable + app binding সেট করে)। অর্ডার উল্টো দিলে middleware
// crash করবে কারণ Auth::user() তখনো null থাকবে।
Route::middleware(['auth', 'tenant.context'])->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    // ── API-style JSON রুট (মোবাইল/AJAX থেকেও ব্যবহারযোগ্য) ──
    Route::resource('students', StudentController::class)
        ->except(['create', 'edit']);
    Route::resource('teachers', \App\Http\Controllers\TeacherController::class)
        ->except(['create', 'edit']);

    Route::get('fee-collections/due', [\App\Http\Controllers\FeeCollectionController::class, 'due']);
    Route::resource('fee-collections', \App\Http\Controllers\FeeCollectionController::class)
        ->except(['create', 'edit', 'destroy']);

    Route::get('attendance', [\App\Http\Controllers\AttendanceController::class, 'index']);
    Route::post('attendance/bulk', [\App\Http\Controllers\AttendanceController::class, 'bulkStore']);
    Route::get('attendance/report', [\App\Http\Controllers\AttendanceController::class, 'studentReport']);

    Route::get('exam-weightings/effective-marks', [\App\Http\Controllers\ExamResultWeightingController::class, 'effectiveMarks']);
    Route::apiResource('exam-weightings', \App\Http\Controllers\ExamResultWeightingController::class)
        ->only(['index', 'store', 'destroy']);

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

    // ── Livewire পেজ রুট (নামসহ, layout ব্যবহার করে — bottom-nav এর ফোকাস) ──
    Route::get('/students-page', \App\Livewire\StudentList::class)->name('students.index');
    Route::get('/teachers-page', \App\Livewire\TeacherList::class)->name('teachers.index');
    Route::get('/fees-page', \App\Livewire\FeeCollectionList::class)->name('fees.index');
    Route::get('/attendance-page', \App\Livewire\AttendanceTaker::class)->name('attendance.index');
    Route::get('/chat-page', \App\Livewire\ChatWindow::class)->name('chat.index');
    Route::get('/settings-page', \App\Livewire\InstitutionSettingsForm::class)->name('settings.index');
    Route::get('/routine-page', \App\Livewire\RoutineBoard::class)->name('routine.index');

    // পোর্টাল (guardian/teacher/student — role অনুযায়ী নিজের ডেটা)
    Route::get('/portal/guardian', \App\Livewire\GuardianPortal::class)->name('portal.guardian');
    Route::get('/portal/teacher', \App\Livewire\TeacherPortal::class)->name('portal.teacher');
    Route::get('/portal/student', \App\Livewire\StudentPortal::class)->name('portal.student');

    // PDF জেনারেশন
    Route::get('/marksheet/class', [\App\Http\Controllers\MarksheetController::class, 'classMarksheet'])->name('marksheet.class');
    Route::get('/marksheet/student/{student}', [\App\Http\Controllers\MarksheetController::class, 'studentMarksheet'])->name('marksheet.student');
    Route::get('/admit-cards/class', [\App\Http\Controllers\AdmitCardController::class, 'classAdmitCards'])->name('admit-cards.class');

    // Export (CSV)
    Route::get('/export/students', [\App\Http\Controllers\ExportController::class, 'students'])->name('export.students');
    Route::get('/export/attendance', [\App\Http\Controllers\ExportController::class, 'attendance'])->name('export.attendance');
    Route::get('/export/fees', [\App\Http\Controllers\ExportController::class, 'fees'])->name('export.fees');

    // Bulk import
    Route::post('/import/students', [\App\Http\Controllers\BulkImportController::class, 'importStudents'])->name('import.students');

    // Exam CRUD + publish
    Route::get('/exams', [\App\Http\Controllers\ExamController::class, 'index']);
    Route::post('/exams', [\App\Http\Controllers\ExamController::class, 'store']);
    Route::post('/exams/{exam}/publish', [\App\Http\Controllers\ExamController::class, 'publish'])->name('exams.publish');

    // ছুটির আবেদন অনুমোদন (admin/teacher)
    Route::get('/leave-requests', \App\Livewire\LeaveRequestsList::class)->name('leave-requests.index');
});

// ─── Superadmin রুট (তাদের institution_id null, তাই আলাদা middleware group) ───
Route::middleware(['auth', 'superadmin'])->prefix('superadmin')->group(function () {
    Route::get('/institutions', \App\Livewire\SuperadminInstitutionsList::class)->name('superadmin.institutions');
});
