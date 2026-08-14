<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;
use PhpOffice\PhpWord\Element\Table as WordTable;
use PhpOffice\PhpWord\Element\Text as WordText;
use PhpOffice\PhpWord\Element\TextRun as WordTextRun;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;

/**
 * ImportService — Excel/CSV (.xlsx/.xls/.csv) এবং Word (.docx) ফাইল থেকে
 * টেবিল-আকৃতির ডাটা (headers + rows) বের করে। প্রতিষ্ঠানভেদে কলামের ক্রম/নাম
 * ভিন্ন হতে পারে বলে এখানে কোনো fixed schema ধরে নেওয়া হয়নি — শুধু raw
 * headers+rows রিটার্ন করা হয়, ম্যাপিং পরের ধাপে (DataImporter Livewire) হয়।
 *
 * ⚠️ Word (.docx) থেকে ডাটা তোলা কম নির্ভরযোগ্য — ডকুমেন্টে যদি সত্যিকারের
 * টেবিল (Insert > Table) না থাকে, শুধু টেক্সট/ট্যাব দিয়ে সাজানো তালিকা থাকে,
 * তাহলে এটা কিছুই বের করতে পারবে না। ব্যবহারকারীকে UI-তে এই সীমাবদ্ধতা
 * জানিয়ে দেওয়া হয়েছে (Excel/CSV-ই সবচেয়ে নির্ভরযোগ্য)।
 */
class ImportService
{
    /**
     * @return array{headers: array<int, string>, rows: array<int, array<int, string|null>>}
     */
    public function parse(string $absolutePath, string $extension): array
    {
        $extension = strtolower($extension);

        return match ($extension) {
            'docx' => $this->parseDocx($absolutePath),
            default => $this->parseSpreadsheet($absolutePath),
        };
    }

    protected function parseSpreadsheet(string $path): array
    {
        $spreadsheet = SpreadsheetIOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();

        // false param এ কলাম key numeric index হবে, header row অনুযায়ী না
        $grid = $sheet->toArray(null, true, true, false);

        $grid = array_values(array_filter($grid, function ($row) {
            return collect($row)->contains(fn ($cell) => $cell !== null && trim((string) $cell) !== '');
        }));

        if (empty($grid)) {
            return ['headers' => [], 'rows' => []];
        }

        $headers = array_map(fn ($h) => trim((string) $h), array_shift($grid));

        $rows = array_map(function ($row) {
            return array_map(fn ($cell) => $cell === null ? null : trim((string) $cell), $row);
        }, $grid);

        return ['headers' => $headers, 'rows' => $rows];
    }

    protected function parseDocx(string $path): array
    {
        $phpWord = WordIOFactory::load($path);

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if ($element instanceof WordTable) {
                    return $this->extractDocxTable($element);
                }
            }
        }

        return ['headers' => [], 'rows' => []];
    }

    protected function extractDocxTable(WordTable $table): array
    {
        $grid = [];

        foreach ($table->getRows() as $row) {
            $cells = [];
            foreach ($row->getCells() as $cell) {
                $cells[] = $this->extractCellText($cell);
            }
            $grid[] = $cells;
        }

        $grid = array_values(array_filter($grid, function ($row) {
            return collect($row)->contains(fn ($cell) => trim((string) $cell) !== '');
        }));

        if (empty($grid)) {
            return ['headers' => [], 'rows' => []];
        }

        $headers = array_map('trim', array_shift($grid));

        return ['headers' => $headers, 'rows' => $grid];
    }

    protected function extractCellText($cell): string
    {
        $text = '';

        foreach ($cell->getElements() as $el) {
            if ($el instanceof WordText) {
                $text .= $el->getText();
            } elseif ($el instanceof WordTextRun) {
                foreach ($el->getElements() as $sub) {
                    if ($sub instanceof WordText) {
                        $text .= $sub->getText();
                    }
                }
            }
        }

        return trim($text);
    }
}
