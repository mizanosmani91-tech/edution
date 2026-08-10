<?php

use App\Http\Controllers\Auth\ApiLoginController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

/**
 * এই ফাইলটা routes/web.php এর সমান্তরাল — একই Controller গুলো ব্যবহার করে,
 * শুধু middleware ভিন্ন: session-এর বদলে 'auth:sanctum' (token)।
 *
 * ⚠️ route নাম 'mobile.' prefix দিয়ে আলাদা রাখা হয়েছে যেন web.php এর
 * Livewire পেজ route নামের (students.index ইত্যাদি) সাথে conflict না হয় —
 * এই bug টা একবার প্রোডাকশনে ধরা পড়েছিল, তাই এই নোট রাখা হলো।
 */

Route::post('/login', [ApiLoginController::class, 'store']);

Route::middleware(['auth:sanctum', 'tenant.context'])->group(function () {
    Route::post('/logout', [ApiLoginController::class, 'destroy']);

    Route::apiResource('students', StudentController::class)->names('mobile.students');
    Route::apiResource('teachers', TeacherController::class)->names('mobile.teachers');

    // বাকি সব মডিউল web.php এর একই pattern এ এখানেও যোগ করুন (নাম prefix সহ):
    // fee-collections, attendance, exam-weightings, chat, settings, routine
});
