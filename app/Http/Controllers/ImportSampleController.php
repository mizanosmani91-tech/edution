<?php

namespace App\Http\Controllers;

use App\Support\ImportFieldMap;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

/**
 * বাল্ক ইমপোর্ট উইজার্ডের "নমুনা ফাইল ডাউনলোড করুন" বাটনের জন্য —
 * ImportFieldMap-এ সংজ্ঞায়িত ফিল্ড লিস্ট + উদাহরণ সারি দিয়ে একটা
 * .xlsx ফাইল বানিয়ে ডাউনলোড করে দেয়, যাতে ব্যবহারকারী ঠিক কোন কলাম
 * কোন ফরম্যাটে লাগবে তা বুঝে নিজের আসল ডাটা দিয়ে ফাইলটা পূরণ করতে পারেন।
 */
class ImportSampleController extends Controller
{
    public function download(string $entity): Response
    {
        $fields = ImportFieldMap::fields($entity);

        if (empty($fields)) {
            abort(404);
        }

        // ⚠️ TEMPORARY DEBUG: লাইভে এই রুট 500 দিচ্ছিল, কারণ চিহ্নিত করার
        // জন্য সাময়িকভাবে exception টা সরাসরি রেসপন্সে দেখানো হচ্ছে। রুটটা
        // auth+tenant middleware এর পেছনে (শুধু লগইন করা এডমিন দেখতে পারবে),
        // কারণ বের হওয়ার সাথে সাথে এই ব্লক সরিয়ে ফেলা হবে।
        try {
            $sampleRows = ImportFieldMap::sampleRows($entity);

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('নমুনা');

            foreach ($fields as $i => $field) {
                $colLetter = Coordinate::stringFromColumnIndex($i + 1);
                $sheet->setCellValue($colLetter.'1', $field['label'].($field['required'] ? ' *' : ''));
                $sheet->getStyle($colLetter.'1')->getFont()->setBold(true);
                $sheet->getColumnDimension($colLetter)->setWidth(22);
            }

            foreach ($sampleRows as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2;
                foreach ($fields as $i => $field) {
                    $colLetter = Coordinate::stringFromColumnIndex($i + 1);
                    $value = $row[$field['key']] ?? '';
                    $sheet->setCellValue($colLetter.$rowNumber, $value);
                }
            }

            $sheet->freezePane('A2');

            $filename = 'edution-namuna-'.$entity.'.xlsx';

            $writer = new Xlsx($spreadsheet);
            $tmpPath = tempnam(sys_get_temp_dir(), 'edu_sample_').'.xlsx';
            $writer->save($tmpPath);
            $contents = file_get_contents($tmpPath);
            @unlink($tmpPath);

            return response($contents, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'debug_error' => $e->getMessage(),
                'debug_file' => $e->getFile().':'.$e->getLine(),
                'debug_trace' => explode("\n", $e->getTraceAsString()),
            ], 500);
        }
    }
}
