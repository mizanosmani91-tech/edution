<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\RoutinePeriod;
use App\Models\Teacher;
use Livewire\Component;
use Livewire\WithPagination;

class TeacherList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $designationFilter = '';
    public string $statusFilter = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingDesignationFilter(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->reset(['search', 'designationFilter', 'statusFilter']);
    }

    public function render()
    {
        $teachers = Teacher::when($this->search, fn ($q) => $q
                ->where('name', 'ilike', "%{$this->search}%")
                ->orWhere('teacher_id_no', 'ilike', "%{$this->search}%"))
            ->when($this->designationFilter, fn ($q) => $q->where('designation', $this->designationFilter))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderBy('name')
            ->paginate(15);

        // প্রতিটা teacher-এর জন্য assigned subjects/classes RoutinePeriod থেকে বের করি
        $teacherIds = $teachers->pluck('id');
        $routineData = RoutinePeriod::with(['subject', 'schoolClass'])
            ->whereIn('teacher_id', $teacherIds)
            ->get()
            ->groupBy('teacher_id');

        $todayAttendance = Attendance::where('date', now()->toDateString())
            ->whereIn('marked_by', $teacherIds) // শুধু approximate, teacher নিজে present কিনা track করার আলাদা সিস্টেম এখনো নেই
            ->get();

        return view('livewire.teacher-list', [
            'teachers' => $teachers,
            'routineData' => $routineData,
            'totalTeachers' => Teacher::count(),
            'activeTeachers' => Teacher::where('status', 'active')->count(),
            'onLeaveTeachers' => Teacher::where('status', 'leave')->count(),
            'designations' => Teacher::whereNotNull('designation')->distinct()->pluck('designation'),
        ])->layout('components.layouts.app', ['title' => 'শিক্ষক ও স্টাফ তালিকা']);
    }
}
