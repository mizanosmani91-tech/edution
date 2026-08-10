<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use Livewire\Component;

class LeaveRequestsList extends Component
{
    public function approve(string $id): void
    {
        $leave = LeaveRequest::findOrFail($id);

        $leave->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        // অনুমোদিত হলে ওই তারিখগুলোর জন্য attendance status='leave' অটোমেটিক বসিয়ে দেওয়া
        $period = \Carbon\CarbonPeriod::create($leave->date_from, $leave->date_to);
        foreach ($period as $date) {
            Attendance::updateOrCreate(
                ['student_id' => $leave->student_id, 'date' => $date->toDateString()],
                [
                    'class_id' => $leave->student->class_id,
                    'section_id' => $leave->student->section_id,
                    'status' => 'leave',
                    'marked_by' => auth()->id(),
                ]
            );
        }
    }

    public function reject(string $id): void
    {
        LeaveRequest::findOrFail($id)->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
    }

    public function render()
    {
        return view('livewire.leave-requests-list', [
            'pending' => LeaveRequest::with('student')->where('status', 'pending')->latest()->get(),
        ])->layout('components.layouts.app');
    }
}
