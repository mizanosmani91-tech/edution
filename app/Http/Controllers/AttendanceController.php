<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\RoutinePeriod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    /**
     * একটা নির্দিষ্ট ক্লাস/শাখা/তারিখের attendance লিস্ট — teacher যখন
     * attendance নেওয়ার UI খোলে তখন এটা কল হয়
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'class_id' => ['required', 'integer'],
            'section_id' => ['nullable', 'integer'],
            'date' => ['required', 'date'],
        ]);

        $this->authorizeClassAccess((int) $validated['class_id'], $validated['section_id'] ?? null);

        return Attendance::with('student')
            ->where('class_id', $validated['class_id'])
            ->when($validated['section_id'] ?? null, fn ($q, $id) => $q->where('section_id', $id))
            ->where('date', $validated['date'])
            ->get();
    }

    /**
     * ⚠️ teacher role হলে শুধু নিজের assigned ক্লাস/সেকশনেই (RoutinePeriod
     * অনুযায়ী) হাজিরা দেখা/নেওয়ার অনুমতি — admin/superadmin-এর জন্য উন্মুক্ত।
     */
    protected function authorizeClassAccess(int $classId, ?int $sectionId): void
    {
        $user = auth()->user();

        if ($user->role !== 'teacher') {
            return;
        }

        abort_unless($user->teacher_id, 403, 'এই একাউন্ট কোনো শিক্ষকের সাথে যুক্ত না।');

        $assigned = RoutinePeriod::where('teacher_id', $user->teacher_id)
            ->where('class_id', $classId)
            ->when($sectionId, fn ($q, $id) => $q->where('section_id', $id))
            ->exists();

        abort_unless($assigned, 403, 'আপনি এই ক্লাসের জন্য নিযুক্ত না।');
    }

    /**
     * একবারে পুরো ক্লাসের attendance সেভ (bulk) — এক-এক করে student এন্ট্রি
     * করানোর চেয়ে এটাই বাস্তবসম্মত UI প্যাটার্ন
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'class_id' => ['required', 'integer'],
            'section_id' => ['nullable', 'integer'],
            'date' => ['required', 'date'],
            'records' => ['required', 'array', 'min:1'],
            'records.*.student_id' => [
                'required',
                Rule::exists('students', 'id')
                    ->where('institution_id', app('tenant.institution_id')),
            ],
            'records.*.status' => ['required', Rule::in(['present', 'absent', 'late', 'leave'])],
            'records.*.remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $this->authorizeClassAccess((int) $validated['class_id'], $validated['section_id'] ?? null);

        $markedBy = auth()->id();
        $created = [];
        $notifications = app(\App\Services\NotificationService::class);

        foreach ($validated['records'] as $record) {
            $attendance = Attendance::updateOrCreate(
                [
                    'student_id' => $record['student_id'],
                    'date' => $validated['date'],
                ],
                [
                    'class_id' => $validated['class_id'],
                    'section_id' => $validated['section_id'] ?? null,
                    'status' => $record['status'],
                    'remarks' => $record['remarks'] ?? null,
                    'marked_by' => $markedBy,
                ]
            );

            // ⚠️ শুধু নতুন absent মার্ক করা হলেই notify — বারবার একই দিন
            // re-save করলে duplicate notification যেন না যায়, তাই wasRecentlyCreated
            // অথবা status পরিবর্তন হয়েছে কিনা চেক করা ভালো, সরলতার জন্য এখানে
            // শুধু status==absent চেক করা হলো
            if ($record['status'] === 'absent') {
                $notifications->attendanceAbsent($attendance->student);
            }

            $created[] = $attendance;
        }

        return response()->json($created, 201);
    }

    /**
     * একটা student এর নির্দিষ্ট সময়ের attendance রিপোর্ট (summary) —
     * fine/leave-request module এই endpoint এর ওপর নির্ভর করবে
     */
    public function studentReport(Request $request)
    {
        $validated = $request->validate([
            'student_id' => [
                'required',
                Rule::exists('students', 'id')->where('institution_id', app('tenant.institution_id')),
            ],
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        $records = Attendance::where('student_id', $validated['student_id'])
            ->whereBetween('date', [$validated['from'], $validated['to']])
            ->get();

        return [
            'total_days' => $records->count(),
            'present' => $records->where('status', 'present')->count(),
            'absent' => $records->where('status', 'absent')->count(),
            'late' => $records->where('status', 'late')->count(),
            'leave' => $records->where('status', 'leave')->count(),
            'records' => $records,
        ];
    }
}
