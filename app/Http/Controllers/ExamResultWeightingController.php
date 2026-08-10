<?php

namespace App\Http\Controllers;

use App\Models\ExamResultWeighting;
use App\Services\ExamResultService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExamResultWeightingController extends Controller
{
    public function __construct(private ExamResultService $examResults)
    {
    }

    public function index(Request $request)
    {
        return ExamResultWeighting::with(['targetExam', 'sourceExam'])
            ->when($request->target_exam_id, fn ($q, $id) => $q->where('target_exam_id', $id))
            ->get();
    }

    public function store(Request $request)
    {
        // ⚠️ RLS পলিসি (institution_admin_manage_weightings) is_user_institution_admin()
        // দিয়ে ডেটাবেজ-লেভেলে এমনিতেই enforce করে — একজন সাধারণ teacher role এর
        // ইউজার এই query চালালে DB নিজেই reject করবে। তবু app-লেভেলেও ফেল-ফাস্ট
        // চেক রাখা হলো, যাতে ভুল হলে DB error এর বদলে পরিষ্কার 403 মেসেজ যায়।
        if (!auth()->user()->isSuperAdmin() && auth()->user()->role !== 'admin') {
            abort(403, 'শুধু institution admin exam weighting কনফিগার করতে পারবেন।');
        }

        $validated = $request->validate([
            'target_exam_id' => ['required', 'uuid', Rule::exists('exams', 'id')->where('institution_id', app('tenant.institution_id'))],
            'source_exam_id' => [
                'required',
                'uuid',
                'different:target_exam_id', // chk_no_self_reference এর app-level echo
                Rule::exists('exams', 'id')->where('institution_id', app('tenant.institution_id')),
            ],
            'class_id' => ['nullable', 'uuid', Rule::exists('classes', 'id')->where('institution_id', app('tenant.institution_id'))],
            'subject_id' => ['nullable', 'uuid', Rule::exists('subjects', 'id')->where('institution_id', app('tenant.institution_id'))],
            'contribution_type' => ['required', Rule::in(['scale', 'percentage'])],
            'group_key' => ['nullable', 'string', 'max:100'],
            'converted_max_marks' => ['required_if:contribution_type,scale', 'nullable', 'numeric', 'min:0'],
            'weight_percentage' => ['required_if:contribution_type,percentage', 'nullable', 'numeric', 'min:0', 'max:100'],
            'require_source_pass' => ['boolean'],
        ]);

        // institution_id ইচ্ছাকৃতভাবে validate করা হয়নি — creating() hook থেকে আসে
        return response()->json(ExamResultWeighting::create($validated), 201);
    }

    public function destroy(ExamResultWeighting $examResultWeighting)
    {
        if (!auth()->user()->isSuperAdmin() && auth()->user()->role !== 'admin') {
            abort(403, 'শুধু institution admin exam weighting মুছতে পারবেন।');
        }

        $examResultWeighting->delete();

        return response()->noContent();
    }

    /**
     * একটা exam+class এর সব ছাত্রের effective marks — marksheet/result page
     * এই endpoint কল করবে
     */
    public function effectiveMarks(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => ['required', 'uuid', Rule::exists('exams', 'id')->where('institution_id', app('tenant.institution_id'))],
            'class_id' => ['required', 'uuid', Rule::exists('classes', 'id')->where('institution_id', app('tenant.institution_id'))],
        ]);

        return $this->examResults->getEffectiveMarksForClass(
            $validated['exam_id'],
            $validated['class_id']
        );
    }
}
