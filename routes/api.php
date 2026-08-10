<?php

use App\Http\Controllers\Auth\ApiLoginController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

/**
 * এই ফাইলটা routes/web.php এর সমান্তরাল — একই Controller গুলো ব্যবহার করে,
 * শুধু middleware ভিন্ন: session-এর বদলে 'auth:sanctum' (token)।
 *
 * ভবিষ্যতে React Native/Flutter অ্যাপ এই একই endpoint গুলোই কল করবে।
 * নতুন কোনো ওয়েব মডিউল বানালে (routes/web.php এ) মনে করে এখানেও যোগ করবেন,
 * নাহলে মোবাইল অ্যাপ সেই ফিচার পাবে না — এটা ভুলে যাওয়ার মতো একটা common gotcha।
 */

// ─── Public ───
Route::post('/login', [ApiLoginController::class, 'store']);

// ─── Token-protected + tenant-scoped ───
Route::middleware(['auth:sanctum', 'tenant.context'])->group(function () {
    Route::post('/logout', [ApiLoginController::class, 'destroy']);

    Route::apiResource('students', StudentController::class);
    Route::apiResource('teachers', TeacherController::class);

    // বাকি সব মডিউল web.php এর একই pattern এ এখানেও যোগ করুন:
    // fee-collections, attendance, exam-weightings, chat, settings, routine
});
