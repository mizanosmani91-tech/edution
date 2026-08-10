<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExamController extends Controller
{
    public function index()
    {
        return \App\Models\Exam::orderByDesc('created_at')->get();
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin' && !auth()->user()->isSuperAdmin()) {
            abort(403, 'শুধু admin exam তৈরি করতে পারবেন।');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'exam_type' => ['nullable', 'string'],
            'academic_year' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        return response()->json(Exam::create($validated), 201);
    }

    /**
     * Publish করলেই ছাত্র/অভিভাবক পোর্টালে ফলাফল দেখা যাবে (এখন পর্যন্ত
     * StudentPortal শুধু is_published=true exam দেখায়) — তাই publish একটা
     * গুরুত্বপূর্ণ, ইচ্ছাকৃত অ্যাকশন, ভুলে হয়ে গেলে undo করার upsertOrCreate
     * সুবিধা নেই, তাই admin-only + confirmation UI-তে রাখা উচিত
     */
    public function publish(Exam $exam, NotificationService $notifications)
    {
        if (auth()->user()->role !== 'admin' && !auth()->user()->isSuperAdmin()) {
            abort(403, 'শুধু admin ফলাফল প্রকাশ করতে পারবেন।');
        }

        $exam->update(['is_published' => true]);
        $notifications->examPublished($exam);

        return response()->json($exam);
    }
}
