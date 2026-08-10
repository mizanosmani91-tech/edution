<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

/**
 * StudentController — রেফারেন্স প্যাটার্ন
 *
 * লক্ষ্য করুন: কোথাও `$request->institution_id` ব্যবহার করা হয়নি।
 * institution_id সবসময় logged-in user থেকে আসে (middleware → global scope
 * → creating() hook), কখনো request body থেকে না — কারণ request body
 * client নিয়ন্ত্রণ করে, সেটা বিশ্বাসযোগ্য না (spoofing risk)।
 */
class StudentController extends Controller
{
    public function index()
    {
        // global scope অটোমেটিক institution_id ফিল্টার করে দিচ্ছে —
        // এখানে ম্যানুয়ালি কিছু লিখতে হয়নি
        return Student::with(['institution'])->paginate(25);
    }

    public function show(Student $student)
    {
        // Route model binding + global scope combo:
        // অন্য institution এর student ID দিয়ে URL হিট করলে global scope
        // এটাকে "not found" বানিয়ে দেবে (কারণ query তে institution_id
        // match করবে না) — 403 এর বদলে 404 আসবে, যা তথ্য-ফাঁসের দিক
        // থেকে আসলে ভালো (student আছে কিনা সেটাও outsider বুঝতে পারবে না)
        return $student;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'student_id_no' => ['required', 'string', 'max:50'],
            'class_id' => ['required', 'uuid'],
            'section_id' => ['nullable', 'integer'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            // 👇 institution_id ইচ্ছা করেই validate/accept করা হয়নি।
            // request এ কেউ institution_id পাঠালেও সেটা $validated এ থাকবে না,
            // creating() hook টাই সেট করবে ইউজারের নিজের institution_id দিয়ে।
        ]);

        $student = Student::create($validated);

        return response()->json($student, 201);
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'class_id' => ['sometimes', 'uuid'],
            'section_id' => ['nullable', 'integer'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
        ]);

        // $student ইতিমধ্যে route-model-binding এর সময় global scope দিয়ে
        // resolve হয়েছে, তাই এটা নিশ্চিতভাবেই বর্তমান institution এর student
        $student->update($validated);

        return response()->json($student);
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return response()->noContent();
    }
}
