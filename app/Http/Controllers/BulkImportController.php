<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * BulkImportController — CSV থেকে students bulk import। Institution
 * admin ভুল ফাইল দিলে (উদাহরণ: অন্য ফরম্যাট, কলাম মিসিং) সম্পূর্ণ ব্যর্থ না
 * হয়ে, row-by-row validate করে সফল/ব্যর্থ দুটোরই রিপোর্ট দেয়।
 *
 * প্রত্যাশিত CSV হেডার: name,student_id_no,class_name,section_name,guardian_phone,date_of_birth
 */
class BulkImportController extends Controller
{
    public function importStudents(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt']]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($handle);

        $success = 0;
        $errors = [];
        $rowNumber = 1;

        DB::transaction(function () use ($handle, $header, &$success, &$errors, &$rowNumber) {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;
                $data = array_combine($header, $row);

                $validator = Validator::make($data, [
                    'name' => ['required', 'string', 'max:255'],
                    'student_id_no' => ['required', 'string', 'max:50'],
                    'guardian_phone' => ['nullable', 'string'],
                    'date_of_birth' => ['nullable', 'date'],
                ]);

                if ($validator->fails()) {
                    $errors[] = "সারি {$rowNumber}: " . $validator->errors()->first();
                    continue;
                }

                // ⚠️ class_name/section_name দিয়ে খোঁজা — কিন্তু এই খোঁজা অবশ্যই
                // tenant-scoped, নাহলে অন্য institution এর একই নামের class এ
                // ভুল করে যুক্ত হয়ে যেতে পারে। SchoolClass এ BelongsToTenant
                // থাকায় এই where() নিজে থেকেই institution ফিল্টার করবে।
                $classId = !empty($data['class_name'])
                    ? \App\Models\SchoolClass::where('name', $data['class_name'])->value('id')
                    : null;

                $sectionId = !empty($data['section_name']) && $classId
                    ? \App\Models\Section::where('class_id', $classId)->where('name', $data['section_name'])->value('id')
                    : null;

                try {
                    // institution_id ইচ্ছাকৃতভাবে দেওয়া হয়নি — creating() hook থেকে আসে
                    Student::create([
                        'name' => $data['name'],
                        'student_id_no' => $data['student_id_no'],
                        'class_id' => $classId,
                        'section_id' => $sectionId,
                        'guardian_phone' => $data['guardian_phone'] ?? null,
                        'date_of_birth' => $data['date_of_birth'] ?? null,
                        'status' => 'active',
                    ]);
                    $success++;
                } catch (\Illuminate\Database\QueryException $e) {
                    // সাধারণত duplicate student_id_no — unique constraint ভাঙলে
                    $errors[] = "সারি {$rowNumber}: student_id_no সম্ভবত আগে থেকেই আছে।";
                }
            }
        });

        fclose($handle);

        return response()->json([
            'imported' => $success,
            'errors' => $errors,
        ]);
    }
}
