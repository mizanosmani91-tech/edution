<?php

namespace App\Livewire;

use App\Models\Exam;
use App\Models\ExamSeatAssignment;
use App\Models\ExamSeatPlan;
use App\Models\ExamSubject;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * ExamSeatPlanManager — পরীক্ষার সিট প্ল্যান তৈরি ও ম্যানেজ করার পেজ।
 *
 * কাজের ধাপ:
 *  ১. পরীক্ষা নির্বাচন
 *  ২. রুম/হল যোগ করা (নাম + ধারণক্ষমতা)
 *  ৩. "স্বয়ংক্রিয়ভাবে সিট বিন্যাস তৈরি করুন" — যেসব ক্লাস এই পরীক্ষা দিচ্ছে
 *     (ExamSubject থেকে বের করা), তাদের সব ছাত্রকে ক্লাস-ক্রম অনুযায়ী
 *     রুমগুলোতে ধারণক্ষমতা মেনে সিরিয়ালি বসিয়ে দেয়
 *  ৪. প্রয়োজনে কোনো ছাত্রের রুম ম্যানুয়ালি বদলে দেওয়া যায়
 *  ৫. রুম-ভিত্তিক প্রিন্ট (PDF)
 */
class ExamSeatPlanManager extends Component
{
    public string $examId = '';
    public string $newRoomName = '';
    public int $newRoomCapacity = 30;

    public function mount(): void
    {
        $latest = Exam::orderByDesc('start_date')->first();
        $this->examId = $latest?->id ?? '';
    }

    public function addRoom(): void
    {
        $this->validate([
            'examId' => ['required', 'uuid'],
            'newRoomName' => ['required', 'string', 'max:100'],
            'newRoomCapacity' => ['required', 'integer', 'min:1', 'max:500'],
        ]);

        $maxOrder = ExamSeatPlan::where('exam_id', $this->examId)->max('display_order') ?? 0;

        ExamSeatPlan::create([
            'exam_id' => $this->examId,
            'room_name' => $this->newRoomName,
            'capacity' => $this->newRoomCapacity,
            'display_order' => $maxOrder + 1,
        ]);

        $this->newRoomName = '';
        $this->newRoomCapacity = 30;
        $this->dispatch('toast', message: 'রুম যোগ করা হয়েছে');
    }

    /**
     * পরীক্ষার হলে দায়িত্বরত শিক্ষক অ্যাসাইন করা — হল-ডিউটি তালিকা প্রিন্টের
     * জন্য দরকার।
     */
    public function assignHallTeacher(string $roomId, string $teacherId): void
    {
        $room = ExamSeatPlan::where('exam_id', $this->examId)->findOrFail($roomId);
        $room->update(['assigned_teacher_id' => $teacherId ?: null]);
        $this->dispatch('toast', message: 'হলের দায়িত্বরত শিক্ষক নির্ধারণ করা হয়েছে');
    }

    public function deleteRoom(string $roomId): void
    {
        ExamSeatPlan::where('id', $roomId)->where('exam_id', $this->examId)->delete();
        $this->dispatch('toast', message: 'রুম ও সেই রুমের সিট বিন্যাস মুছে ফেলা হয়েছে');
    }

    /**
     * ⚠️ পুরনো সিট বিন্যাস মুছে নতুন করে তৈরি করে — ক্লাস অনুযায়ী গ্রুপ
     * করে, প্রতিটা ক্লাসের ছাত্রদের student_id_no অনুযায়ী সাজিয়ে, রুমগুলোর
     * ধারণক্ষমতা মেনে একটার পর একটা সিরিয়ালি বসিয়ে দেয়।
     */
    public function generateSeatPlan(): void
    {
        $this->validate(['examId' => ['required', 'uuid']]);

        $rooms = ExamSeatPlan::where('exam_id', $this->examId)->orderBy('display_order')->get();

        if ($rooms->isEmpty()) {
            $this->dispatch('toast', message: 'প্রথমে অন্তত একটা রুম যোগ করুন');
            return;
        }

        $classIds = ExamSubject::where('exam_id', $this->examId)->distinct()->pluck('class_id');

        if ($classIds->isEmpty()) {
            $this->dispatch('toast', message: 'এই পরীক্ষার সাথে কোনো ক্লাস যুক্ত নেই — আগে পরীক্ষার সময়সূচিতে বিষয়/ক্লাস যোগ করুন');
            return;
        }

        $students = Student::whereIn('class_id', $classIds)
            ->where('status', 'active')
            ->with('schoolClass')
            ->get()
            ->sortBy(fn ($s) => ($s->schoolClass->display_order ?? 0) . '-' . $s->student_id_no)
            ->values();

        if ($students->isEmpty()) {
            $this->dispatch('toast', message: 'এই ক্লাসগুলোতে কোনো সক্রিয় ছাত্র পাওয়া যায়নি');
            return;
        }

        $totalCapacity = $rooms->sum('capacity');
        if ($students->count() > $totalCapacity) {
            $this->dispatch('toast', message: "মোট ছাত্র {$students->count()} জন কিন্তু রুমগুলোর মোট ধারণক্ষমতা মাত্র {$totalCapacity} — আরও রুম/ধারণক্ষমতা বাড়ান");
            return;
        }

        DB::transaction(function () use ($rooms, $students) {
            ExamSeatAssignment::where('exam_id', $this->examId)->delete();

            $studentQueue = $students->values();
            $cursor = 0;

            foreach ($rooms as $room) {
                for ($seat = 1; $seat <= $room->capacity && $cursor < $studentQueue->count(); $seat++) {
                    ExamSeatAssignment::create([
                        'exam_id' => $this->examId,
                        'exam_seat_plan_id' => $room->id,
                        'student_id' => $studentQueue[$cursor]->id,
                        'seat_no' => $seat,
                    ]);
                    $cursor++;
                }
            }
        });

        $this->dispatch('toast', message: 'সিট বিন্যাস তৈরি হয়ে গেছে');
    }

    public function moveStudent(string $assignmentId, string $newRoomId): void
    {
        $assignment = ExamSeatAssignment::where('exam_id', $this->examId)->findOrFail($assignmentId);
        $newRoom = ExamSeatPlan::where('exam_id', $this->examId)->findOrFail($newRoomId);

        $takenSeats = ExamSeatAssignment::where('exam_seat_plan_id', $newRoom->id)->pluck('seat_no')->all();
        $nextSeat = null;
        for ($s = 1; $s <= $newRoom->capacity; $s++) {
            if (! in_array($s, $takenSeats, true)) {
                $nextSeat = $s;
                break;
            }
        }

        if ($nextSeat === null) {
            $this->dispatch('toast', message: 'এই রুমে আর খালি সিট নেই');
            return;
        }

        $assignment->update(['exam_seat_plan_id' => $newRoom->id, 'seat_no' => $nextSeat]);
        $this->dispatch('toast', message: 'ছাত্রের রুম পরিবর্তন করা হয়েছে');
    }

    public function render()
    {
        // ⚠️ examId খালি স্ট্রিং হতে পারে (কোনো পরীক্ষা তৈরি না থাকলে) —
        // exam_id একটা uuid কলাম, খালি স্ট্রিং দিয়ে where() করলে Postgres এ
        // "invalid input syntax for type uuid" এরর দিয়ে পুরো পেজ ভেঙে যায় (500)।
        // তাই আগে খালি কিনা চেক করে নেওয়া হচ্ছে।
        $rooms = $this->examId
            ? ExamSeatPlan::where('exam_id', $this->examId)
                ->orderBy('display_order')
                ->with(['assignments.student.schoolClass', 'assignments.student.section', 'assignedTeacher'])
                ->get()
            : collect();

        return view('livewire.exam-seat-plan-manager', [
            'exams' => Exam::orderByDesc('start_date')->get(),
            'rooms' => $rooms,
            'teachers' => Teacher::where('status', 'active')->orderBy('name')->get(),
            'totalAssigned' => $rooms->sum(fn ($r) => $r->assignments->count()),
        ])->layout('components.layouts.app', ['title' => 'পরীক্ষার সিট প্ল্যান']);
    }
}
