<?php

namespace App\Http\Controllers;

use App\Models\RoutinePeriod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RoutinePeriodController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'class_id' => ['required', 'uuid'],
            'section_id' => ['nullable', 'uuid'],
        ]);

        return RoutinePeriod::with(['teacher', 'schoolClass'])
            ->where('class_id', $validated['class_id'])
            ->when($validated['section_id'] ?? null, fn ($q, $id) => $q->where('section_id', $id))
            ->orderBy('day_of_week')
            ->orderBy('period_number')
            ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_id' => ['required', 'uuid', Rule::exists('classes', 'id')->where('institution_id', app('tenant.institution_id'))],
            'section_id' => ['nullable', 'uuid', Rule::exists('sections', 'id')->where('institution_id', app('tenant.institution_id'))],
            'teacher_id' => ['required', 'uuid', Rule::exists('teachers', 'id')->where('institution_id', app('tenant.institution_id'))],
            'subject_id' => ['required', 'uuid', Rule::exists('subjects', 'id')->where('institution_id', app('tenant.institution_id'))],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'period_number' => ['required', 'integer', 'min:1'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        // ⚠️ Institution এর setting অনুযায়ী consecutive-period চেক — আগে এটা
        // hard rule ছিল, এখন settings->consecutive_period_blocking দিয়ে
        // কনফিগারযোগ্য (memory অনুযায়ী রিকোয়ারমেন্ট)
        $institution = auth()->user()->institution;

        if ($institution->blocksConsecutivePeriods()) {
            $hasAdjacent = RoutinePeriod::where('teacher_id', $validated['teacher_id'])
                ->where('day_of_week', $validated['day_of_week'])
                ->whereIn('period_number', [
                    $validated['period_number'] - 1,
                    $validated['period_number'] + 1,
                ])
                ->exists();

            if ($hasAdjacent) {
                throw ValidationException::withMessages([
                    'period_number' => 'এই শিক্ষকের পরপর দুই পিরিয়ড থাকতে পারবে না। '
                        . '(সেটিংস থেকে এই রুল বন্ধ করা যায়, যদি প্রয়োজন হয়।)',
                ]);
            }
        }

        // institution_id ইচ্ছাকৃতভাবে validate করা হয়নি — creating() hook থেকে আসে
        return response()->json(RoutinePeriod::create($validated), 201);
    }

    public function destroy(RoutinePeriod $routinePeriod)
    {
        $routinePeriod->delete();

        return response()->noContent();
    }
}
