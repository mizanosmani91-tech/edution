<?php

namespace App\Http\Controllers;

use App\Support\ImportFieldMap;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * বাল্ক ইমপোর্ট উইজার্ডের "নমুনা ফাইল ডাউনলোড করুন" বাটনের জন্য —
 * ImportFieldMap-এ সংজ্ঞায়িত ফিল্ড লিস্ট + উদাহরণ সারি দিয়ে একটা
 * .csv ফাইল বানিয়ে ডাউনলোড করে দেয়, যাতে ব্যবহারকারী ঠিক কোন কলাম
 * কোন ফরম্যাটে লাগবে তা বুঝে নিজের আসল ডাটা দিয়ে ফাইলটা পূরণ করতে পারেন।
 *
 * নোট: প্রথমে এটা PhpSpreadsheet দিয়ে .xlsx বানাত, কিন্তু লাইভ VPS-এ
 * মেমরি/রিসোর্স সীমাবদ্ধতার কারণে মাঝেমধ্যে PHP-FPM ওয়ার্কার ক্র্যাশ
 * করে 503 দিচ্ছিল (স্বাভাবিক exception না হওয়ায় try/catch দিয়েও ধরা
 * যাচ্ছিল না)। ImportService::parse() এমনিতেই CSV সাপোর্ট করে, তাই
 * হালকা ও নির্ভরযোগ্য CSV স্ট্রিমিং এ পরিবর্তন করা হয়েছে।
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
        $filename = 'edution-namuna-'.$entity.'.csv';

        return response()->streamDownload(function () use ($fields, $sampleRows) {
            // এক্সেলে বাংলা ঠিকমতো দেখানোর জন্য UTF-8 BOM
            echo "\xEF\xBB\xBF";

            $out = fopen('php://output', 'w');

            $headerRow = array_map(
                fn ($field) => $field['label'].($field['required'] ? ' *' : ''),
                $fields
            );
            fputcsv($out, $headerRow);

            foreach ($sampleRows as $row) {
                $line = array_map(fn ($field) => $row[$field['key']] ?? '', $fields);
                fputcsv($out, $line);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
