<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    $institution = \App\Models\Institution::resolveFromSubdomain(request()->getHost());
    return view('auth.login', ['institution' => $institution]);
})->name('login');
Route::post('/login', [LoginController::class, 'store']);
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth');

Route::middleware(['auth', 'tenant.context'])->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    // ── API-style JSON রুট — নাম আলাদা রাখা হলো 'api.' prefix দিয়ে, যেন
    // Livewire পেজ রুটের নামের সাথে conflict না হয় ──
    Route::resource('students', StudentController::class)
        ->except(['create', 'edit'])
        ->names('api.students');
    Route::resource('teachers', \App\Http\Controllers\TeacherController::class)
        ->except(['create', 'edit'])
        ->names('api.teachers');

    Route::get('fee-collections/due', [\App\Http\Controllers\FeeCollectionController::class, 'due']);
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
    Route::get('/teachers-page', \App\Livewire\TeacherList::class)->name('teachers.index');
    Route::get('/fees-page', \App\Livewire\FeeCollectionList::class)->name('fees.index');
    Route::get('/attendance-page', \App\Livewire\AttendanceTaker::class)->name('attendance.index');
    Route::get('/chat-page', \App\Livewire\ChatWindow::class)->name('chat.index');
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
});

Route::middleware(['auth', 'superadmin'])->prefix('superadmin')->group(function () {
    Route::get('/institutions', \App\Livewire\SuperadminInstitutionsList::class)->name('superadmin.institutions');
});
