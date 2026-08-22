<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\FeeCollection;
use App\Models\InstitutionSetting;
use App\Models\SchoolClass;
use App\Models\StaffAttendance;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\MonthlyHonorsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DashboardStats extends Component
{
    public array $widgets = [];

    private array $defaultWidgets = [
        'kpi' => true,
        'attendance_donut' => true,
        'class_attendance' => true,
        'attendance_trend' => true,
        'fee_chart' => true,
        'exam_chart' => true,
        'honors' => true,
        'defaulters' => true,
    ];

    public function mount(): void
    {
        $institution = Auth::user()->institution;
        $saved = $institution?->settings?->dashboard_widgets;
        $this->widgets = array_merge($this->defaultWidgets, is_array($saved) ? $saved : []);
    }

    public function toggleWidget(string $key): void
    {
        if (!array_key_exists($key, $this->widgets)) {
            return;
        }
        $this->widgets[$key] = !$this->widgets[$key];

        $institutionId = Auth::user()->institution_id;
        InstitutionSetting::updateOrCreate(
            ['institution_id' => $institutionId],
            ['dashboard_widgets' => $this->widgets]
        );
    }

    public function render()
    {
        $institutionId = Auth::user()->institution_id;

        $totalStudents = Student::where('status', 'active')->count();
        $totalTeachers = Teacher::count();

        $todayAttendance = Attendance::where('date', now()->toDateString())->get();
        $presentToday = $todayAttendance->where('status', 'present')->count();
        $absentToday = $todayAttendance->where('status', 'absent')->count();
        $lateToday = $todayAttendance->where('status', 'late')->count();
        $leaveToday = $todayAttendance->where('status', 'leave')->count();
        $attendanceRate = $todayAttendance->count() > 0
            ? round($presentToday / $todayAttendance->count() * 100)
            : null;

        $monthCollection = FeeCollection::where('due_month', now()->format('Y-m'))->sum('amount_paid');
        $totalDue = FeeCollection::whereIn('status', ['due', 'partial', 'overdue'])
            ->selectRaw('SUM(amount_due - amount_paid) as total')
            ->value('total') ?? 0;

        $classDistribution = SchoolClass::withCount(['students' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('display_order')
            ->get()
            ->map(fn ($c) => ['label' => $c->name, 'count' => $c->students_count]);

        $topDefaulters = FeeCollection::with('student')
            ->whereIn('status', ['due', 'partial', 'overdue'])
            ->get()
            ->groupBy('student_id')
            ->map(fn ($group) => [
                'student' => $group->first()->student,
                'total_due' => $group->sum(fn ($f) => $f->amount_due - $f->amount_paid),
            ])
            ->filter(fn ($row) => $row['student'] !== null)
            ->sortByDesc('total_due')
            ->take(5)
            ->values();

        // ===== ডোনাট: আজকের হাজিরা =====
        $attendanceDonut = [
            'labels' => ['উপস্থিত', 'অনুপস্থিত', 'দেরিতে', 'ছুটি'],
            'data' => [$presentToday, $absentToday, $lateToday, $leaveToday],
            'colors' => ['#10B981', '#EF4444', '#F59E0B', '#3B82F6'],
        ];

        // ===== ক্লাস-ভিত্তিক আজকের হাজিরা % =====
        $classAttendance = SchoolClass::orderBy('display_order')->get()->map(function ($c) use ($todayAttendance) {
            $rows = $todayAttendance->where('class_id', $c->id);
            $pct = $rows->count() > 0 ? round($rows->where('status', 'present')->count() / $rows->count() * 100) : 0;
            return ['label' => $c->name, 'value' => $pct];
        })->filter(fn ($r) => $r['value'] > 0 || true)->values();

        // ===== গত ১৪ দিনের হাজিরা ট্রেন্ড =====
        $trendLabels = [];
        $trendData = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $rows = Attendance::where('date', $date->toDateString())->get();
            $trendLabels[] = $date->translatedFormat('d M');
            $trendData[] = $rows->count() > 0 ? round($rows->where('status', 'present')->count() / $rows->count() * 100) : null;
        }

        // ===== ফি: এই মাসে আদায় বনাম বকেয়া =====
        $feeChart = [
            'labels' => ['আদায়কৃত', 'বকেয়া'],
            'data' => [round((float) $monthCollection), round((float) $totalDue)],
            'colors' => ['#10B981', '#EF4444'],
        ];

        // ===== পরীক্ষা: সাম্প্রতিক প্রকাশিত পরীক্ষার গ্রেড বিভাজন =====
        $examChart = null;
        $latestExam = Exam::where('is_published', true)->orderByDesc('end_date')->first();
        if ($latestExam) {
            $marks = ExamMark::whereHas('examSubject', fn ($q) => $q->where('exam_id', $latestExam->id))
                ->whereNotNull('marks_obtained')->get();
            $bands = ['A+ (৮০+)' => 0, 'A (৭০-৭৯)' => 0, 'B (৬০-৬৯)' => 0, 'C (৫০-৫৯)' => 0, 'অকৃতকার্য' => 0];
            foreach ($marks as $m) {
                $v = (float) $m->marks_obtained;
                if ($v >= 80) $bands['A+ (৮০+)']++;
                elseif ($v >= 70) $bands['A (৭০-৭৯)']++;
                elseif ($v >= 60) $bands['B (৬০-৬৯)']++;
                elseif ($v >= 33) $bands['C (৫০-৫৯)']++;
                else $bands['অকৃতকার্য']++;
            }
            $examChart = [
                'exam_name' => $latestExam->name,
                'labels' => array_keys($bands),
                'data' => array_values($bands),
                'colors' => ['#10B981', '#3B82F6', '#F59E0B', '#EC4899', '#EF4444'],
            ];
        }

        // ===== আজকের শিক্ষক/স্টাফ হাজিরা =====
        $staffToday = StaffAttendance::where('date', now()->toDateString())->get();
        $staffDonut = [
            'labels' => ['উপস্থিত', 'অনুপস্থিত', 'দেরিতে'],
            'data' => [$staffToday->where('status', 'present')->count(), $staffToday->where('status', 'absent')->count(), $staffToday->where('status', 'late')->count()],
            'colors' => ['#10B981', '#EF4444', '#F59E0B'],
        ];

        // ===== মাসের সেরা =====
        (new MonthlyHonorsService())->ensureComputedForCurrentMonth($institutionId);
        $honors = \App\Models\MonthlyHonor::with(['student', 'teacher'])
            ->where('institution_id', $institutionId)
            ->where('month', now()->format('Y-m'))
            ->get()
            ->keyBy('category');

        return view('livewire.dashboard-stats', [
            'totalStudents' => $totalStudents,
            'totalTeachers' => $totalTeachers,
            'attendanceRate' => $attendanceRate,
            'monthCollection' => $monthCollection,
            'totalDue' => $totalDue,
            'classDistribution' => $classDistribution,
            'topDefaulters' => $topDefaulters,
            'attendanceDonut' => $attendanceDonut,
            'classAttendance' => $classAttendance,
            'trendLabels' => $trendLabels,
            'trendData' => $trendData,
            'feeChart' => $feeChart,
            'examChart' => $examChart,
            'staffDonut' => $staffDonut,
            'honorStudent' => $honors->get('student'),
            'honorTeacher' => $honors->get('teacher'),
        ]);
    }
}
