<?php

namespace App\Http\Controllers;

use App\Support\ImportFieldMap;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * বাল্ক ইমপোর্ট উইজার্ডের "নমুনা ফাইল ডাউনলোড করুন" বাটনের জন্য —
 * ImportFieldMap-এ সংজ্ঞায়িত ফিল্ড লিস্ট + উদাহরণ সারি দিয়ে একটা
 * .xlsx ফাইল বানিয়ে ডাউনলোড করে দেয়, যাতে ব্যবহারকারী ঠিক কোন কলাম
 * কোন ফরম্যাটে লাগবে তা বুঝে নিজের আসল ডাটা দিয়ে ফাইলটা পূরণ করতে পারেন।
 *
 * (getCellByColumnAndRow/getColumnDimensionByColumn এর বদলে column-letter
 * ভিত্তিক API ব্যবহার করা হয়েছে — PhpSpreadsheet ভার্সন জুড়ে সবচেয়ে
 * স্থিতিশীল/নিশ্চিত পদ্ধতি।)
 */
class ImportSampleController extends Controller
{
    public function download(string $entity): StreamedResponse
    {
        $fields = ImportFieldMap::fields($entity);

        if (empty($fields)) {
            abort(404);
        }

        $sampleRows = ImportFieldMap::sampleRows($entity);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('নমুনা');

        // হেডার সারি
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

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
