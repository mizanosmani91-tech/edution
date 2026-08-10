<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\FeeCollection;
use App\Models\LeaveRequest;
use Livewire\Component;

/**
 * GuardianPortal — multi-child। শুধু institution_id ফিল্টার (BelongsToTenant)
 * যথেষ্ট না এখানে — একই institution এর অন্য guardian এর সন্তানের ডেটা দেখা
 * আটকাতে হবে। তাই child selector শুধু auth()->user()->children() থেকে
 * আসে, আর selectChild() এ ম্যানুয়ালি verify করা হয় যে selected student
 * সত্যিই এই guardian এর সন্তান কিনা — URL/component state manipulate করে
 * অন্য কারো student_id বসিয়ে দিলেও এটা আটকাবে।
 */
class GuardianPortal extends Component
{
    public ?string $activeChildId = null;

    public bool $showLeaveForm = false;
    public string $leaveFrom = '';
    public string $leaveTo = '';
    public string $leaveReason = '';

    public function mount(): void
    {
        $firstChild = auth()->user()->children()->first();
        $this->activeChildId = $firstChild?->id;
    }

    public function selectChild(string $studentId): void
    {
        $isMyChild = auth()->user()->children()->where('students.id', $studentId)->exists();

        abort_unless($isMyChild, 403, 'এই ছাত্রের তথ্য দেখার অনুমতি আপনার নেই।');

        $this->activeChildId = $studentId;
    }

    public function submitLeaveRequest(): void
    {
        $this->validate([
            'leaveFrom' => 'required|date',
            'leaveTo' => 'required|date|after_or_equal:leaveFrom',
            'leaveReason' => 'required|string|max:500',
        ]);

        // ⚠️ activeChildId নিজের সন্তান কিনা আরেকবার নিশ্চিত (defense in depth,
        // যদিও mount/selectChild এ চেক হয়ে গেছে — ফর্ম সাবমিটের সময়ও যাচাই)
        $isMyChild = auth()->user()->children()->where('students.id', $this->activeChildId)->exists();
        abort_unless($isMyChild, 403);

        LeaveRequest::create([
            'student_id' => $this->activeChildId,
            'requested_by' => auth()->id(),
            'date_from' => $this->leaveFrom,
            'date_to' => $this->leaveTo,
            'reason' => $this->leaveReason,
        ]);

        $this->reset(['showLeaveForm', 'leaveFrom', 'leaveTo', 'leaveReason']);
    }

    public function render()
    {
        $children = auth()->user()->children;

        $attendanceSummary = null;
        $feeSummary = null;
        $leaveRequests = collect();

        if ($this->activeChildId) {
            $child = $children->firstWhere('id', $this->activeChildId);

            if ($child) {
                $last30Days = Attendance::where('student_id', $child->id)
                    ->where('date', '>=', now()->subDays(30))
                    ->get();

                $attendanceSummary = [
                    'present' => $last30Days->where('status', 'present')->count(),
                    'absent' => $last30Days->where('status', 'absent')->count(),
                    'total' => $last30Days->count(),
                ];

                $feeSummary = FeeCollection::where('student_id', $child->id)
                    ->whereIn('status', ['due', 'partial', 'overdue'])
                    ->get()
                    ->map(fn ($f) => ['month' => $f->due_month, 'due' => $f->due_amount]);

                $leaveRequests = LeaveRequest::where('student_id', $child->id)
                    ->latest()
                    ->limit(5)
                    ->get();
            }
        }

        return view('livewire.guardian-portal', [
            'children' => $children,
            'attendanceSummary' => $attendanceSummary,
            'feeSummary' => $feeSummary,
            'leaveRequests' => $leaveRequests,
        ])->layout('components.layouts.app');
    }
}
