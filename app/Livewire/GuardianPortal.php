<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\FeeCollection;
use App\Models\HifzProgress;
use App\Models\Homework;
use App\Models\IntegrationSetting;
use App\Models\HomeworkCompletion;
use App\Models\LeaveRequest;
use App\Models\Notice;
use App\Models\RoutinePeriod;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * GuardianPortal — অভিভাবকের পূর্ণাঙ্গ পোর্টাল। multi-child সাপোর্ট করে।
 *
 * ⚠️ শুধু institution_id ফিল্টার (BelongsToTenant) যথেষ্ট না এখানে —
 * একই institution এর অন্য guardian এর সন্তানের ডেটা দেখা আটকাতে হবে।
 * তাই child selector শুধু auth()->user()->children() থেকে আসে, আর
 * প্রতিটা write action এ ম্যানুয়ালি ভেরিফাই করা হয় যে activeChildId
 * সত্যিই এই guardian এর সন্তান কিনা — URL/component state manipulate
 * করে অন্য কারো student_id বসিয়ে দিলেও এটা আটকাবে।
 */
class GuardianPortal extends Component
{
    public string $activeTab = 'overview'; // overview / fees / messages / notices / leave / profile

    public ?string $activeChildId = null;

    // ছুটির আবেদন
    public bool $showLeaveModal = false;
    public string $leaveType = 'casual';
    public string $leaveFrom = '';
    public string $leaveTo = '';
    public string $leaveReason = '';

    // ফি পেমেন্ট দাবি (guardian claim — এডমিন ভেরিফাই করলেই আসল হিসেবে যোগ হবে)
    public ?string $payingFeeId = null;
    public string $claimAmount = '';
    public string $claimMethod = 'bkash';
    public string $claimRef = '';
    public string $claimNote = '';

    // প্রোফাইল
    public string $profilePhone = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->role === 'guardian', 403, 'এই পেজ শুধু অভিভাবকদের জন্য।');

        $firstChild = auth()->user()->children()->first();
        $this->activeChildId = $firstChild?->id;
        $this->profilePhone = auth()->user()->phone ?? '';

        // bKash পেমেন্ট শেষে callback থেকে ?tab=fees দিয়ে ফেরত পাঠানো হয়,
        // সেটা এখানে ধরে সরাসরি ফি ট্যাবেই খুলে দেওয়া হয়
        if (request()->query('tab')) {
            $this->activeTab = request()->query('tab');
        }
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function selectChild(string $studentId): void
    {
        $isMyChild = auth()->user()->children()->where('students.id', $studentId)->exists();

        abort_unless($isMyChild, 403, 'এই ছাত্রের তথ্য দেখার অনুমতি আপনার নেই।');

        $this->activeChildId = $studentId;
    }

    private function assertOwnsActiveChild(): void
    {
        abort_unless(
            $this->activeChildId && auth()->user()->children()->where('students.id', $this->activeChildId)->exists(),
            403
        );
    }

    // ================= ছুটির আবেদন =================

    public function openLeaveModal(): void
    {
        $this->reset(['leaveType', 'leaveFrom', 'leaveTo', 'leaveReason']);
        $this->leaveType = 'casual';
        $this->showLeaveModal = true;
    }

    public function submitLeaveRequest(): void
    {
        $this->assertOwnsActiveChild();

        $this->validate([
            'leaveType' => ['required', 'string'],
            'leaveFrom' => ['required', 'date'],
            'leaveTo' => ['required', 'date', 'after_or_equal:leaveFrom'],
            'leaveReason' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        LeaveRequest::create([
            'applicant_type' => 'student',
            'student_id' => $this->activeChildId,
            'leave_type' => $this->leaveType,
            'requested_by' => auth()->id(),
            'date_from' => $this->leaveFrom,
            'date_to' => $this->leaveTo,
            'reason' => $this->leaveReason,
            'status' => 'pending',
        ]);

        $this->showLeaveModal = false;
        $this->dispatch('toast', message: 'ছুটির আবেদন জমা হয়েছে, এডমিনের অনুমোদনের অপেক্ষায় আছে।');
    }

    // ================= ফি পেমেন্ট দাবি =================

    public function openPayModal(string $feeId): void
    {
        $this->assertOwnsActiveChild();

        $fee = FeeCollection::where('student_id', $this->activeChildId)->findOrFail($feeId);

        $this->payingFeeId = $fee->id;
        $this->claimAmount = (string) $fee->due_amount;
        $this->claimMethod = 'bkash';
        $this->claimRef = '';
        $this->claimNote = '';
    }

    public function closePayModal(): void
    {
        $this->payingFeeId = null;
    }

    public function submitPaymentClaim(): void
    {
        $this->assertOwnsActiveChild();

        $this->validate([
            'claimAmount' => ['required', 'numeric', 'min:1'],
            'claimMethod' => ['required', 'in:bkash,nagad,bank_transfer,cash'],
            'claimRef' => ['nullable', 'string', 'max:100'],
            'claimNote' => ['nullable', 'string', 'max:300'],
        ]);

        // ⚠️ এখানে সরাসরি amount_paid/status বদলানো হচ্ছে না — শুধু
        // guardian_claimed_* কলামে "আমি পেমেন্ট করেছি" বলে দাবি জমা রাখা
        // হচ্ছে। এডমিন যাচাই করে নিশ্চিত করলে তখনই আসল হিসেবে যোগ হবে।
        // এটা ছাড়া যে কোনো অভিভাবক নিজের ফি নিজেই "পরিশোধ" বলে দাবি করে
        // দিতে পারতো — জালিয়াতি ঠেকানোর জন্যই এই দুই-ধাপ ব্যবস্থা।
        $fee = FeeCollection::where('student_id', $this->activeChildId)->findOrFail($this->payingFeeId);

        $fee->update([
            'guardian_claimed_amount' => $this->claimAmount,
            'guardian_claimed_method' => $this->claimMethod,
            'guardian_claimed_ref' => $this->claimRef ?: null,
            'guardian_claim_note' => $this->claimNote ?: null,
            'guardian_claimed_at' => now(),
            'guardian_claim_status' => 'pending',
        ]);

        $this->payingFeeId = null;
        $this->dispatch('toast', message: 'পেমেন্টের তথ্য জমা হয়েছে — অফিস যাচাই করে নিশ্চিত করলে হিসেবে যোগ হবে।');
    }

    // ================= বার্তা/মেসেজ =================

    /**
     * ক্লাস শিক্ষক/বিষয় শিক্ষক/প্রধান শিক্ষকের সাথে চ্যাট শুরু করে
     * চ্যাট পেজে পাঠিয়ে দেয়। অন্য কোনো প্রতিষ্ঠানের বা অসংযুক্ত user_id
     * দিলে ব্যর্থ হবে (tenant-scoped exists চেক)।
     */
    public function startChat(string $userId): void
    {
        $myId = auth()->id();

        if ($userId === $myId) {
            return;
        }

        $exists = User::where('id', $userId)->where('institution_id', app('tenant.institution_id'))->exists();
        abort_unless($exists, 404);

        $conversation = Conversation::where('type', 'direct')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $myId))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userId))
            ->first();

        if (! $conversation) {
            $conversation = DB::transaction(function () use ($myId, $userId) {
                $conv = Conversation::create(['type' => 'direct']);
                ConversationParticipant::create(['conversation_id' => $conv->id, 'user_id' => $myId]);
                ConversationParticipant::create(['conversation_id' => $conv->id, 'user_id' => $userId]);

                return $conv;
            });
        }

        $this->redirect(route('chat.index', ['open' => $conversation->id]));
    }

    // ================= প্রোফাইল =================

    public function saveProfile(): void
    {
        $this->validate(['profilePhone' => ['nullable', 'string', 'max:20']]);

        auth()->user()->update(['phone' => $this->profilePhone ?: null]);

        $this->dispatch('toast', message: 'প্রোফাইল আপডেট হয়েছে।');
    }

    public function render()
    {
        $children = auth()->user()->children()->with(['schoolClass', 'section'])->get();
        $child = $this->activeChildId ? $children->firstWhere('id', $this->activeChildId) : null;

        $attendanceSummary = null;
        $feeCollections = collect();
        $totalDue = 0;
        $leaveRequests = collect();
        $classTeacherUser = null;
        $classTeacherName = null;
        $subjectContacts = collect();
        $headmasterUser = null;
        $headmasterName = null;
        $notices = collect();
        $homeworks = collect();

        if ($child) {
            $last30Days = Attendance::where('student_id', $child->id)
                ->where('date', '>=', now()->subDays(30))
                ->get();

            $attendanceSummary = [
                'present' => $last30Days->where('status', 'present')->count(),
                'absent' => $last30Days->where('status', 'absent')->count(),
                'leave' => $last30Days->where('status', 'leave')->count(),
                'total' => $last30Days->count(),
            ];

            $feeCollections = FeeCollection::where('student_id', $child->id)->latest('due_month')->get();
            $totalDue = $feeCollections->whereIn('status', ['due', 'partial', 'overdue'])->sum(fn ($f) => $f->due_amount);

            $leaveRequests = LeaveRequest::where('applicant_type', 'student')
                ->where('student_id', $child->id)
                ->latest('date_from')
                ->limit(15)
                ->get();

            // ক্লাস শিক্ষক (সেকশনের homeroom teacher)
            $classTeacher = $child->section?->classTeacher;
            if ($classTeacher) {
                $classTeacherName = $classTeacher->name;
                $classTeacherUser = User::where('teacher_id', $classTeacher->id)->first();
            }

            // বিষয় শিক্ষকরা (রুটিন থেকে — এই ক্লাস/সেকশনে যারা পড়ান)
            $periods = RoutinePeriod::with(['teacher', 'subject'])
                ->where('class_id', $child->class_id)
                ->when($child->section_id, fn ($q) => $q->where('section_id', $child->section_id))
                ->get();

            $subjectContacts = $periods->groupBy('teacher_id')
                ->map(function ($group) {
                    $teacher = $group->first()->teacher;
                    $subjects = $group->pluck('subject.name')->filter()->unique()->implode(', ');

                    return [
                        'teacher' => $teacher,
                        'subjects' => $subjects,
                        'user' => $teacher ? User::where('teacher_id', $teacher->id)->first() : null,
                    ];
                })
                ->filter(fn ($row) => $row['teacher'])
                ->values();

            // প্রধান শিক্ষক
            $headTeacher = Teacher::where('designation', 'প্রধান শিক্ষক')->first();
            if ($headTeacher) {
                $headmasterName = $headTeacher->name;
                $headmasterUser = User::where('teacher_id', $headTeacher->id)->first();
            }

            $notices = Notice::published()
                ->where(function ($q) {
                    $q->whereNull('audience')
                        ->orWhereJsonLength('audience', 0)
                        ->orWhereJsonContains('audience', 'guardian');
                })
                ->orderByDesc('is_pinned')
                ->orderByDesc('is_urgent')
                ->latest('publish_at')
                ->limit(20)
                ->get();

            // এই সন্তানের ক্লাস/সেকশনের হোমওয়ার্ক + এই সন্তানের জন্য
            // চেক করা হয়েছে কিনা (থাকলে) — একসাথে জোড়া লাগিয়ে পাঠানো হচ্ছে
            $homeworkRows = Homework::with(['subject', 'teacher'])
                ->where('class_id', $child->class_id)
                ->where(function ($q) use ($child) {
                    $q->whereNull('section_id')->orWhere('section_id', $child->section_id);
                })
                ->latest('assigned_date')
                ->limit(20)
                ->get();

            $completions = HomeworkCompletion::where('student_id', $child->id)
                ->whereIn('homework_id', $homeworkRows->pluck('id'))
                ->get()
                ->keyBy('homework_id');

            $homeworks = $homeworkRows->map(fn ($h) => [
                'homework' => $h,
                'status' => $completions->get($h->id)?->status,
            ]);
        }

        $recentHifz = collect();
        if ($child) {
            $recentHifz = HifzProgress::where('student_id', $child->id)->latest('date')->limit(5)->get();
        }

        $bkashEnabled = (bool) (IntegrationSetting::find(app('tenant.institution_id'))?->bkash_enabled);

        // অপঠিত মেসেজের সংখ্যা (overview badge এর জন্য)
        $unreadCount = ConversationParticipant::where('user_id', auth()->id())
            ->with('conversation')
            ->get()
            ->filter(fn ($p) => $p->conversation
                && $p->conversation->last_message_at
                && (! $p->last_read_at || $p->conversation->last_message_at->gt($p->last_read_at)))
            ->count();

        return view('livewire.guardian-portal', [
            'children' => $children,
            'child' => $child,
            'attendanceSummary' => $attendanceSummary,
            'feeCollections' => $feeCollections,
            'totalDue' => $totalDue,
            'leaveRequests' => $leaveRequests,
            'classTeacherName' => $classTeacherName,
            'classTeacherUser' => $classTeacherUser,
            'subjectContacts' => $subjectContacts,
            'headmasterName' => $headmasterName,
            'headmasterUser' => $headmasterUser,
            'notices' => $notices,
            'homeworks' => $homeworks,
            'unreadCount' => $unreadCount,
            'bkashEnabled' => $bkashEnabled,
            'recentHifz' => $recentHifz,
        ])->layout('components.layouts.app', ['title' => 'অভিভাবক পোর্টাল']);
    }
}
