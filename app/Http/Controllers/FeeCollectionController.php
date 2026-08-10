<?php

namespace App\Http\Controllers;

use App\Models\FeeCollection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeeCollectionController extends Controller
{
    public function index(Request $request)
    {
        // global scope অটোমেটিক institution_id ফিল্টার করছে
        return FeeCollection::with('student')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->student_id, fn ($q, $id) => $q->where('student_id', $id))
            ->latest('paid_at')
            ->paginate(25);
    }

    /**
     * Due/overdue ফি লিস্ট — dashboard এর জন্য কমন query, তাই আলাদা endpoint
     */
    public function due()
    {
        return FeeCollection::with('student')
            ->whereIn('status', ['due', 'overdue', 'partial'])
            ->get()
            ->map(fn ($fee) => [
                'id' => $fee->id,
                'student' => $fee->student->name,
                'due_amount' => $fee->due_amount,
                'due_month' => $fee->due_month,
                'status' => $fee->status,
            ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => [
                'required',
                // ⚠️ প্লেইন 'exists:students,id' যথেষ্ট না — এটা সরাসরি DB টেবিলে
                // query করে, Eloquent global scope respect করে না। তাই institution_id
                // ম্যানুয়ালি এখানে যোগ করা বাধ্যতামূলক, নাহলে অন্য institution এর
                // student_id দিয়েও validation pass করে যাবে (সিরিয়াস leak)।
                Rule::exists('students', 'id')
                    ->where('institution_id', app('tenant.institution_id')),
            ],
            'fee_type' => ['required', 'string', 'max:50'],
            'amount_due' => ['required', 'numeric', 'min:0'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:bkash,nagad,bank_transfer,cash'],
            'transaction_ref' => ['nullable', 'string', 'max:100'],
            'due_month' => ['required', 'date_format:Y-m'],
            // institution_id ইচ্ছাকৃতভাবে validate করা হয়নি — creating() hook থেকে আসে
        ]);

        $validated['status'] = $validated['amount_paid'] >= $validated['amount_due']
            ? 'paid'
            : ($validated['amount_paid'] > 0 ? 'partial' : 'due');
        $validated['paid_at'] = $validated['amount_paid'] > 0 ? now() : null;
        $validated['collected_by'] = auth()->id();

        return response()->json(FeeCollection::create($validated), 201);
    }

    public function show(FeeCollection $feeCollection)
    {
        return $feeCollection->load('student');
    }

    public function update(Request $request, FeeCollection $feeCollection)
    {
        $validated = $request->validate([
            'amount_paid' => ['sometimes', 'numeric', 'min:0'],
            'payment_method' => ['sometimes', 'in:bkash,nagad,bank_transfer,cash'],
            'transaction_ref' => ['nullable', 'string', 'max:100'],
        ]);

        if (isset($validated['amount_paid'])) {
            $validated['status'] = $validated['amount_paid'] >= $feeCollection->amount_due
                ? 'paid'
                : ($validated['amount_paid'] > 0 ? 'partial' : 'due');
            $validated['paid_at'] = $validated['amount_paid'] > 0 ? now() : null;
        }

        $feeCollection->update($validated);

        return response()->json($feeCollection);
    }
}
