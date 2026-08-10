<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\FeeCollection;
use App\Models\Student;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ExportController — native PHP fputcsv() ব্যবহার করে, কোনো composer
 * package লাগে না (maatwebsite/excel এর মতো)। স্ট্রিমড রেসপন্স, তাই
 * বড় লিস্টেও মেমরি সমস্যা হবে না — পুরো ডেটাসেট মেমরিতে না নিয়ে সরাসরি
 * response এ row-by-row লেখা হয়।
 */
class ExportController extends Controller
{
    public function students(): StreamedResponse
    {
        return $this->streamCsv('students.csv', ['নাম', 'আইডি', 'ক্লাস', 'শাখা', 'অভিভাবকের ফোন'], function ($handle) {
            Student::with(['schoolClass', 'section'])->orderBy('name')->chunk(200, function ($students) use ($handle) {
                foreach ($students as $s) {
                    fputcsv($handle, [
                        $s->name,
                        $s->student_id_no,
                        $s->schoolClass?->full_label,
                        $s->section?->name,
                        $s->guardian_phone,
                    ]);
                }
            });
        });
    }

    public function attendance(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        return $this->streamCsv('attendance.csv', ['তারিখ', 'ছাত্র', 'ক্লাস', 'স্ট্যাটাস'], function ($handle) use ($validated) {
            Attendance::with(['student', 'schoolClass'])
                ->whereBetween('date', [$validated['from'], $validated['to']])
                ->orderBy('date')
                ->chunk(200, function ($rows) use ($handle) {
                    foreach ($rows as $a) {
                        fputcsv($handle, [
                            $a->date->format('Y-m-d'),
                            $a->student->name,
                            $a->schoolClass->full_label,
                            $a->status,
                        ]);
                    }
                });
        });
    }

    public function fees(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string'],
        ]);

        return $this->streamCsv('fees.csv', ['ছাত্র', 'মাস', 'বকেয়া', 'জমা', 'জরিমানা', 'স্ট্যাটাস'], function ($handle) use ($validated) {
            FeeCollection::with('student')
                ->when($validated['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
                ->orderByDesc('due_month')
                ->chunk(200, function ($rows) use ($handle) {
                    foreach ($rows as $f) {
                        fputcsv($handle, [
                            $f->student->name,
                            $f->due_month,
                            $f->amount_due,
                            $f->amount_paid,
                            $f->fine_amount,
                            $f->status,
                        ]);
                    }
                });
        });
    }

    private function streamCsv(string $filename, array $headers, callable $writeRows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $writeRows) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM — এক্সেলে বাংলা টেক্সট সঠিকভাবে দেখানোর জন্য জরুরি,
            // BOM ছাড়া এক্সেল বাংলা ক্যারেক্টার garbled দেখায়
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);
            $writeRows($handle);
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
