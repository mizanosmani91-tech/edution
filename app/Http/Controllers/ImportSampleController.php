<?php

namespace App\Http\Controllers;

use App\Support\ImportFieldMap;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * বাল্ক ইমপোর্ট উইজার্ডের "নমুনা ফাইল ডাউনলোড করুন" বাটনের জন্য —
 * ImportFieldMap-এ সংজ্ঞায়িত ফিল্ড লিস্ট + উদাহরণ সারি দিয়ে একটা
 * .xlsx ফাইল বানিয়ে ডাউনলোড করে দেয়, যাতে ব্যবহারকারী ঠিক কোন কলাম
 * কোন ফরম্যাটে লাগবে তা বুঝে নিজের আসল ডাটা দিয়ে ফাইলটা পূরণ করতে পারেন।
 */
class ImportSampleController extends Controller
{
    public function download(string $entity): StreamedResponse
    {
        $fields = ImportFieldMap::fields($entity);

        if (empty($fields)) {
            abort(404);
        }

        $label = ImportFieldMap::label($entity);
        $sampleRows = ImportFieldMap::sampleRows($entity);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('নমুনা');

        // হেডার সারি — ঠিক এই কলাম নামগুলোর দরকার নেই (ইমপোর্টের সময় নিজে
        // ম্যাপ করা যায়), কিন্তু এগুলোই সবচেয়ে সহজ, সরাসরি auto-detect হবে।
        foreach ($fields as $i => $field) {
            $col = $i + 1;
            $cell = $sheet->getCellByColumnAndRow($col, 1);
            $cell->setValue($field['label'].($field['required'] ? ' *' : ''));
            $cell->getStyle()->getFont()->setBold(true);
            $sheet->getColumnDimensionByColumn($col)->setWidth(22);
        }

        foreach ($sampleRows as $rowIndex => $row) {
            foreach ($fields as $i => $field) {
                $col = $i + 1;
                $value = $row[$field['key']] ?? '';
                $sheet->getCellByColumnAndRow($col, $rowIndex + 2)->setValue($value);
            }
        }

        $spreadsheet->getActiveSheet()->freezePane('A2');

        $filename = 'edution-namuna-'.$entity.'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
