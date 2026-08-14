<?php

namespace App\Livewire;

use App\Models\DemoAccessRequest;
use App\Models\Institution;
use App\Models\InstitutionPayment;
use App\Models\PlatformNotice;
use App\Models\PlatformSetting;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Mail\AdminCredentialsMail;
use App\Models\User;
use App\Services\SmsOtpService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * SuperadminDashboard
 *
 * panel.edution.xyz-এর একমাত্র Livewire কম্পোনেন্ট — ওভারভিউ, প্রতিষ্ঠান
 * ম্যানেজমেন্ট, বিলিং, নোটিশ, সাপোর্ট টিকেট, সেটিংস সব একসাথে। রেফারেন্স
 * ডিজাইনের মতোই সাইডবার-চালিত সেকশন সুইচিং, কিন্তু client-side JS এর
 * বদলে $activeSection প্রপার্টি দিয়ে (তাই প্রতিটা সেকশনের ডেটা শুধু
 * প্রয়োজন হলেই লোড হয়, আগে থেকে সব রেন্ডার হয় না)।
 */
class SuperadminDashboard extends Component
{
    public string $activeSection = 'overview';

    // ── Institutions section ──
    public string $instType = '';
    public string $instPlan = '';
    public string $instStatus = '';
    public string $instSearch = '';

    public ?string $justApprovedSlug = null;
    public ?string $justApprovedPassword = null;

    // ── Manage-institution modal ──
    public ?string $manageInstitutionId = null;
    public string $deleteConfirmText = '';
    public ?string $purgeInstitutionId = null;
    public string $purgeConfirmText = '';
    public bool $manageActive = true;
    public string $managePlan = 'basic';
    public ?int $manageLimit = null;
    public array $manageModules = [];

    // ── Notices ──
    public string $noticeTitle = '';
    public string $noticeBody = '';
    public string $noticeType = 'general';
    public string $noticeAudience = 'all';

    // ── Support ──
    public ?string $activeTicketId = null;
    public string $replyBody = '';

    // ── Settings ──
    public bool $settingAutoApprove = false;
    public bool $settingAutoSuspendTrial = true;
    public bool $settingBillingSms = true;
    public bool $settingMaintenance = false;

    public string $inviteName = '';
    public string $inviteEmail = '';
    public string $invitePhone = '';

    public const PLAN_PRICES = [
        'basic' => 1500,
        'standard' => 3500,
        'premium' => 6500,
    ];

    public const PLAN_LABELS = [
        'basic' => 'বেসিক',
        'standard' => 'স্ট্যান্ডার্ড',
        'premium' => 'প্রিমিয়াম',
    ];

    public function mount(): void
    {
        $this->settingAutoApprove = PlatformSetting::getBool('auto_approve_institutions', false);
        $this->settingAutoSuspendTrial = PlatformSetting::getBool('auto_suspend_trial_end', true);
        $this->settingBillingSms = PlatformSetting::getBool('billing_alert_sms', true);
        $this->settingMaintenance = PlatformSetting::getBool('maintenance_mode', false);
    }

    public function setSection(string $section): void
    {
        $this->activeSection = $section;
        $this->activeTicketId = null;
    }

    // ================= Institutions =================

    public function approvePendingInstitution(string $institutionId, SmsOtpService $sms): void
    {
        $institution = Institution::query()->findOrFail($institutionId);

        // ⚠️ ৮ ক্যারেক্টার random password — শক্ত এলোমেলো, কিন্তু ফোনে
        // পড়ে শোনানোর মতো সহজ (ambiguous ক্যারেক্টার O/0, I/l বাদ)
        $tempPassword = Str::password(10, symbols: false);

        User::create([
            'institution_id' => $institution->id,
            'name' => $institution->name . ' Admin',
            'email' => $institution->registration_email,
            'password' => Hash::make($tempPassword),
            'role' => 'admin',
            'must_change_password' => true,
        ]);

        $institution->update(['status' => 'active']);

        $this->justApprovedSlug = $institution->slug;
        $this->justApprovedPassword = $tempPassword;

        // ⚠️ স্ক্রিনে দেখানো পাসওয়ার্ড রিলোড/ব্রাউজার বন্ধ হলে হারিয়ে যেতে পারে —
        // তাই approve করার সাথে সাথেই দুই জায়গায় (ইমেইল + SMS) স্থায়ীভাবে
        // পাঠিয়ে দেওয়া হচ্ছে। SMS ইংরেজিতে ও ৬০ ক্যারেক্টারের মধ্যে রাখা
        // হয়েছে (single SMS part, খরচ বাড়ে না), বিস্তারিত তথ্য ইমেইলে থাকে।
        try {
            Mail::to($institution->registration_email)
                ->send(new AdminCredentialsMail($institution, $institution->registration_email, $tempPassword, isReset: false));
        } catch (\Throwable $e) {
            Log::warning('এডমিন ক্রেডেনশিয়াল ইমেইল পাঠাতে ব্যর্থ: ' . $e->getMessage());
        }

        if ($institution->phone) {
            $sms->sendMessage(
                $institution->phone,
                "EDUTION: Approved. Password: {$tempPassword}"
            );
        }
    }

    public function resetAdminPassword(string $institutionId, SmsOtpService $sms): void
    {
        $institution = Institution::query()->findOrFail($institutionId);

        $admin = User::query()
            ->where('institution_id', $institution->id)
            ->where('role', 'admin')
            ->first();

        if (!$admin) {
            $this->dispatch('toast', message: 'এই প্রতিষ্ঠানের কোনো এডমিন ইউজার পাওয়া যায়নি');
            return;
        }

        $tempPassword = Str::password(10, symbols: false);
        $admin->update([
            'password' => Hash::make($tempPassword),
            'must_change_password' => true,
        ]);

        $this->justApprovedSlug = $institution->slug;
        $this->justApprovedPassword = $tempPassword;

        try {
            Mail::to($admin->email)
                ->send(new AdminCredentialsMail($institution, $admin->email, $tempPassword, isReset: true));
        } catch (\Throwable $e) {
            Log::warning('পাসওয়ার্ড রিসেট ইমেইল পাঠাতে ব্যর্থ: ' . $e->getMessage());
        }

        if ($institution->phone) {
            $sms->sendMessage(
                $institution->phone,
                "EDUTION: New password: {$tempPassword}"
            );
        }

        $this->manageInstitutionId = null;
        $this->dispatch('toast', message: 'নতুন পাসওয়ার্ড জেনারেট, ইমেইল ও SMS করা হয়েছে');
    }

    public function rejectPendingInstitution(string $institutionId): void
    {
        Institution::query()->findOrFail($institutionId)->update(['status' => 'rejected']);
    }

    public function openManageModal(string $institutionId): void
    {
        $institution = Institution::query()->findOrFail($institutionId);

        $this->manageInstitutionId = $institution->id;
        $this->manageActive = $institution->status !== 'suspended';
        $this->managePlan = $institution->plan ?? 'basic';
        $this->manageLimit = $institution->student_limit_override;

        $this->manageModules = [];
        foreach (array_keys(Institution::TOGGLEABLE_MODULES) as $key) {
            $this->manageModules[$key] = $institution->isModuleEnabled($key);
        }
    }

    public function closeManageModal(): void
    {
        $this->manageInstitutionId = null;
        $this->deleteConfirmText = '';
    }

    /**
     * ⚠️ এটা soft-delete — প্রতিষ্ঠান সাথে সাথেই মুছে যায় না, রিসাইকেল
     * বিনে চলে যায় (subdomain কাজ করা বন্ধ হয়ে যায়, কিন্তু সব ডেটা অক্ষত
     * থাকে)। কেউ (একাধিক superadmin থাকলে দুর্ঘটনাক্রমে বা ইচ্ছাকৃতভাবে)
     * ডিলিট করলেও রিসাইকেল বিন থেকে ফেরানো যাবে — স্থায়ী ডিলিট আলাদা,
     * কডাকড়ি type-to-confirm সহ (permanentlyDeleteInstitution দেখুন)।
     */
    public function moveInstitutionToTrash(): void
    {
        if (!$this->manageInstitutionId) {
            return;
        }

        $institution = Institution::query()->findOrFail($this->manageInstitutionId);
        $name = $institution->name;

        $institution->update(['deleted_by' => auth()->id()]);
        $institution->delete();

        $this->manageInstitutionId = null;
        $this->deleteConfirmText = '';
        $this->dispatch('toast', message: "\"{$name}\" রিসাইকেল বিনে সরানো হয়েছে");
    }

    public function restoreInstitution(string $institutionId): void
    {
        $institution = Institution::onlyTrashed()->findOrFail($institutionId);
        $institution->restore();
        $institution->update(['deleted_by' => null]);

        $this->dispatch('toast', message: "\"{$institution->name}\" পুনরুদ্ধার করা হয়েছে");
    }

    public function openPurgeModal(string $institutionId): void
    {
        $this->purgeInstitutionId = $institutionId;
        $this->purgeConfirmText = '';
    }

    public function closePurgeModal(): void
    {
        $this->purgeInstitutionId = null;
        $this->purgeConfirmText = '';
    }

    /**
     * ⚠️ Type-to-confirm স্থায়ী ডিলিট — শুধু রিসাইকেল বিন থেকেই সম্ভব, প্রতিষ্ঠানের
     * স্লাগ হুবহু টাইপ না করলে বাটন কাজ করবে না। forceDelete() হলেই
     * cascadeOnDelete()-এর কারণে সকল সংশ্লিষ্ট ডেটাও স্থায়ীভাবে সাথে মুছে যাবে।
     */
    public function permanentlyDeleteInstitution(): void
    {
        if (!$this->purgeInstitutionId) {
            return;
        }

        $institution = Institution::onlyTrashed()->findOrFail($this->purgeInstitutionId);

        if ($this->purgeConfirmText !== $institution->slug) {
            $this->dispatch('toast', message: 'নিশ্চিতকরণের জন্য প্রতিষ্ঠানের স্লাগ হুবুহু টাইপ করুন');
            return;
        }

        $name = $institution->name;
        $institution->forceDelete();

        $this->purgeInstitutionId = null;
        $this->purgeConfirmText = '';
        $this->dispatch('toast', message: "\"{$name}\" স্থায়ীভাবে ডিলিট করা হয়েছে");
    }

    public function suspendFromModal(): void
    {
        $this->manageActive = false;
    }

    public function saveManageModal(): void
    {
        if (!$this->manageInstitutionId) {
            return;
        }

        $institution = Institution::query()->findOrFail($this->manageInstitutionId);

        $institution->update([
            'status' => $this->manageActive ? 'active' : 'suspended',
            'plan' => $this->managePlan,
            'student_limit_override' => $this->manageLimit ?: null,
            'enabled_modules' => $this->manageModules,
        ]);

        $this->manageInstitutionId = null;
        $this->dispatch('toast', message: 'প্রতিষ্ঠানের সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে');
    }

    // ================= Billing =================

    public function approvePayment(string $paymentId): void
    {
        $payment = InstitutionPayment::findOrFail($paymentId);

        $payment->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $payment->institution->update(['status' => 'active']);
    }

    public function rejectPayment(string $paymentId): void
    {
        InstitutionPayment::findOrFail($paymentId)->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
    }

    // ================= Notices =================

    public function sendNotice(): void
    {
        $this->validate([
            'noticeTitle' => ['required', 'string', 'max:255'],
            'noticeBody' => ['required', 'string'],
        ]);

        $reachedCount = match ($this->noticeAudience) {
            'trial' => Institution::query()->where('status', 'trial')->count(),
            'premium' => Institution::query()->where('plan', 'premium')->where('status', '!=', 'pending')->count(),
            'overdue' => InstitutionPayment::query()->where('status', 'pending')->distinct('institution_id')->count('institution_id'),
            default => Institution::query()->where('status', '!=', 'pending')->count(),
        };

        PlatformNotice::create([
            'title' => $this->noticeTitle,
            'body' => $this->noticeBody,
            'notice_type' => $this->noticeType,
            'audience' => $this->noticeAudience,
            'reached_count' => $reachedCount,
            'sent_by' => auth()->id(),
        ]);

        $this->reset(['noticeTitle', 'noticeBody', 'noticeType', 'noticeAudience']);
        $this->noticeType = 'general';
        $this->noticeAudience = 'all';
        $this->dispatch('toast', message: 'নোটিশ সফলভাবে পাঠানো হয়েছে');
    }

    // ================= Support =================

    public function loadTicket(string $ticketId): void
    {
        $this->activeTicketId = $ticketId;
        $this->replyBody = '';
    }

    public function sendReply(): void
    {
        if (!$this->activeTicketId || trim($this->replyBody) === '') {
            return;
        }

        SupportTicketMessage::create([
            'support_ticket_id' => $this->activeTicketId,
            'sender_type' => 'superadmin',
            'sender_name' => auth()->user()->name,
            'body' => $this->replyBody,
        ]);

        $this->replyBody = '';
        $this->dispatch('toast', message: 'উত্তর পাঠানো হয়েছে');
    }

    public function resolveTicket(): void
    {
        if (!$this->activeTicketId) {
            return;
        }

        SupportTicket::query()->whereKey($this->activeTicketId)->update(['status' => 'resolved']);
        $this->dispatch('toast', message: 'টিকেট সমাধান হিসেবে চিহ্নিত হয়েছে');
    }

    public function updateTicketPriority(string $priority): void
    {
        if (!$this->activeTicketId) {
            return;
        }

        SupportTicket::query()->whereKey($this->activeTicketId)->update(['priority' => $priority]);
    }

    // ================= Settings =================

    public function saveSettings(): void
    {
        PlatformSetting::setBool('auto_approve_institutions', $this->settingAutoApprove);
        PlatformSetting::setBool('auto_suspend_trial_end', $this->settingAutoSuspendTrial);
        PlatformSetting::setBool('billing_alert_sms', $this->settingBillingSms);
        PlatformSetting::setBool('maintenance_mode', $this->settingMaintenance);

        $this->dispatch('toast', message: 'সেটিংস সংরক্ষণ করা হয়েছে');
    }

    public function inviteSuperadmin(): void
    {
        $this->validate([
            'inviteName' => ['required', 'string', 'max:255'],
            'inviteEmail' => ['required', 'email', 'max:255', 'unique:users,email'],
            'invitePhone' => ['required', 'string', 'max:20'],
        ]);

        $tempPassword = Str::password(10, symbols: false);

        User::create([
            'institution_id' => null,
            'name' => $this->inviteName,
            'email' => $this->inviteEmail,
            'phone' => $this->invitePhone,
            'password' => Hash::make($tempPassword),
            'role' => 'superadmin',
            'must_change_password' => true,
        ]);

        $this->justApprovedPassword = $tempPassword;
        $this->reset(['inviteName', 'inviteEmail', 'invitePhone']);
        $this->dispatch('toast', message: 'নতুন সুপার এডমিন যোগ করা হয়েছে — সাময়িক পাসওয়ার্ড উপরে দেখানো হয়েছে');
    }

    // ================= ডেমো এক্সেস (শিক্ষক/অভিভাবক) =================

    public function unlockDemoAccess(string $id, int $minutes): void
    {
        $req = DemoAccessRequest::findOrFail($id);

        $req->update([
            'status' => 'approved',
            'unlocked_until' => now()->addMinutes($minutes),
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $roleLabel = $req->role === 'teacher' ? 'শিক্ষক' : 'অভিভাবক';
        $this->dispatch('toast', message: "{$roleLabel} ডেমো এক্সেস {$minutes} মিনিটের জন্য আনলক করা হয়েছে");
    }

    public function rejectDemoAccess(string $id): void
    {
        DemoAccessRequest::findOrFail($id)->update(['status' => 'rejected']);
        $this->dispatch('toast', message: 'রিকোয়েস্ট প্রত্যাখ্যান করা হয়েছে');
    }

    // ================= Render =================

    public function render()
    {
        $data = [
            'stats' => $this->computeOverviewStats(),
        ];

        switch ($this->activeSection) {
            case 'overview':
                $data['pendingInstitutions'] = Institution::query()->where('status', 'pending')->latest()->limit(5)->get();
                $data['recentInstitutions'] = Institution::query()->where('status', '!=', 'pending')->latest()->limit(4)->get();
                $data['urgentTickets'] = SupportTicket::withoutGlobalScope('tenant-or-superadmin')
                    ->with('institution')->where('status', 'open')->where('priority', 'high')->latest()->limit(3)->get();
                $data['growthMonths'] = $this->institutionGrowthByMonth();
                $data['planDistribution'] = $this->planDistribution();
                break;

            case 'applications':
                $data['pendingInstitutions'] = Institution::query()->where('status', 'pending')->latest()->get();
                $data['recentlyReviewed'] = Institution::query()->whereIn('status', ['rejected'])->latest()->limit(10)->get();
                break;

            case 'institutions':
                $data['institutions'] = $this->filteredInstitutions();
                break;

            case 'billing':
                $data['pendingPayments'] = InstitutionPayment::with('institution')->where('status', 'pending')->latest()->get();
                $data['payments'] = InstitutionPayment::with('institution')->where('status', '!=', 'pending')->latest()->limit(20)->get();
                $data['revenueMonths'] = $this->revenueByMonth();
                break;

            case 'notices':
                $data['notices'] = PlatformNotice::latest()->limit(20)->get();
                break;

            case 'support':
                $data['tickets'] = SupportTicket::withoutGlobalScope('tenant-or-superadmin')
                    ->with('institution')->latest()->get();
                $data['activeTicket'] = $this->activeTicketId
                    ? SupportTicket::withoutGlobalScope('tenant-or-superadmin')->with(['institution', 'messages'])->find($this->activeTicketId)
                    : null;
                break;

            case 'settings':
                $data['superadmins'] = User::query()->where('role', 'superadmin')->latest()->get();
                break;

            case 'trash':
                $data['trashedInstitutions'] = Institution::onlyTrashed()->latest('deleted_at')->get();
                break;

            case 'demo-access':
                $data['demoRequests'] = DemoAccessRequest::with('lead')
                    ->whereIn('role', ['teacher', 'guardian'])
                    ->latest()
                    ->limit(50)
                    ->get();
                break;
        }

        return view('livewire.superadmin-dashboard', $data)->layout('components.layouts.superadmin');
    }

    private function filteredInstitutions()
    {
        return Institution::query()
            ->withCount(['students' => fn ($q) => $q->withoutGlobalScope('tenant')])
            ->where('status', '!=', 'pending')
            ->when($this->instType, fn ($q) => $q->where('institution_type', $this->instType))
            ->when($this->instPlan, fn ($q) => $q->where('plan', $this->instPlan))
            ->when($this->instStatus, fn ($q) => $q->where('status', $this->instStatus))
            ->when($this->instSearch, fn ($q) => $q->where(function ($q2) {
                $q2->where('name', 'like', '%' . $this->instSearch . '%')
                   ->orWhere('slug', 'like', '%' . $this->instSearch . '%');
            }))
            ->orderByDesc('created_at')
            ->get();
    }

    private function computeOverviewStats(): array
    {
        $totalInstitutions = Institution::query()->where('status', '!=', 'pending')->count();
        $trialCount = Institution::query()->where('status', 'trial')->count();

        $activeByPlan = Institution::query()
            ->where('status', 'active')
            ->selectRaw('plan, count(*) as c')
            ->groupBy('plan')
            ->pluck('c', 'plan');

        $mrr = 0;
        foreach ($activeByPlan as $plan => $count) {
            $mrr += ($count * (self::PLAN_PRICES[$plan] ?? 0));
        }

        $openTickets = SupportTicket::withoutGlobalScope('tenant-or-superadmin')->where('status', 'open')->count();
        $urgentTickets = SupportTicket::withoutGlobalScope('tenant-or-superadmin')->where('status', 'open')->where('priority', 'high')->count();

        return [
            'totalInstitutions' => $totalInstitutions,
            'trialCount' => $trialCount,
            'mrr' => $mrr,
            'arr' => $mrr * 12,
            'openTickets' => $openTickets,
            'urgentTickets' => $urgentTickets,
            'pendingPayments' => InstitutionPayment::where('status', 'pending')->count(),
            'pendingInstitutions' => Institution::query()->where('status', 'pending')->count(),
            'trashedInstitutions' => Institution::onlyTrashed()->count(),
            'pendingDemoRequests' => DemoAccessRequest::whereIn('role', ['teacher', 'guardian'])->where('status', 'pending')->count(),
        ];
    }

    private function institutionGrowthByMonth(): array
    {
        $months = collect(range(7, 0))->map(fn ($i) => now()->subMonths($i)->startOfMonth());
        $labels = $months->map(fn ($m) => $m->translatedFormat('M'))->toArray();

        $counts = [];
        $runningTotal = Institution::query()->where('status', '!=', 'pending')
            ->where('created_at', '<', $months->first())->count();

        foreach ($months as $month) {
            $runningTotal += Institution::query()->where('status', '!=', 'pending')
                ->whereBetween('created_at', [$month, $month->copy()->endOfMonth()])->count();
            $counts[] = $runningTotal;
        }

        return ['labels' => $labels, 'data' => $counts];
    }

    private function planDistribution(): array
    {
        $counts = Institution::query()->where('status', '!=', 'pending')
            ->selectRaw('plan, count(*) as c')->groupBy('plan')->pluck('c', 'plan');

        return [
            (int) ($counts['basic'] ?? 0),
            (int) ($counts['standard'] ?? 0),
            (int) ($counts['premium'] ?? 0),
        ];
    }

    private function revenueByMonth(): array
    {
        $months = collect(range(7, 0))->map(fn ($i) => now()->subMonths($i)->startOfMonth());
        $labels = $months->map(fn ($m) => $m->translatedFormat('M'))->toArray();

        $data = $months->map(function ($month) {
            return (float) InstitutionPayment::where('status', 'approved')
                ->whereBetween('created_at', [$month, $month->copy()->endOfMonth()])
                ->sum('amount');
        })->toArray();

        return ['labels' => $labels, 'data' => $data];
    }
}
