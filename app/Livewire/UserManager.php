<?php

namespace App\Livewire;

use App\Models\Institution;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\TenantSmsService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * ইউজার ম্যানেজমেন্ট — শুধু অ্যাডমিন। প্রতিষ্ঠানের সবার লগইন এক জায়গায়:
 *   - আরেকজন অ্যাডমিন যোগ, শিক্ষার্থীর লগইন তৈরি
 *   - চালু/বন্ধ (নিষ্ক্রিয় ইউজার ঢুকতে পারে না)
 *   - পাসওয়ার্ড রিসেট (অস্থায়ী পাসওয়ার্ড, প্রথম লগইনে বদলাতে হয়)
 *
 * ⚠️ User মডেলে global tenant scope নেই — তাই প্রতিটা কোয়েরিতে `institution_id`
 * হাতে দেওয়া হয় (scopedUsers())। এটা ভুলে বাদ দিলে অন্য প্রতিষ্ঠানের ইউজারে হাত পড়ত।
 */
class UserManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $roleFilter = '';

    // নতুন অ্যাডমিন
    public bool $showAdminModal = false;
    public string $adminName = '';
    public string $adminEmail = '';

    // শিক্ষার্থীর লগইন
    public bool $showStudentModal = false;
    public string $studentSearch = '';
    public ?string $studentId = null;

    /** অস্থায়ী পাসওয়ার্ড একবারই দেখানো হয়; বন্ধ করলে মুছে যায়। */
    public ?array $revealed = null;
    public ?string $smsResult = null;

    public ?string $notice = null;
    public ?string $errorNotice = null;

    public function mount(): void
    {
        $this->authorizeAdmin();
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->role === 'admin', 403, 'শুধু অ্যাডমিন ইউজার ম্যানেজ করতে পারবেন।');
    }

    private function tenantId(): string
    {
        return (string) app('tenant.institution_id');
    }

    private function scopedUsers()
    {
        return User::query()->where('institution_id', $this->tenantId());
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    private function resetNotices(): void
    {
        $this->notice = null;
        $this->errorNotice = null;
    }

    private function activeAdminCount(): int
    {
        return $this->scopedUsers()->where('role', 'admin')->where('is_active', true)->count();
    }

    // ───────────────────────── চালু/বন্ধ ─────────────────────────

    public function toggleActive(string $userId): void
    {
        $this->authorizeAdmin();
        $this->resetNotices();

        $user = $this->scopedUsers()->findOrFail($userId);

        if ($user->id === auth()->id()) {
            $this->errorNotice = 'নিজের অ্যাকাউন্ট নিজে বন্ধ করা যায় না।';

            return;
        }

        $newState = ! $user->is_active;

        // শেষ সক্রিয় অ্যাডমিনকে বন্ধ করা যাবে না — নাহলে প্রতিষ্ঠান লক আউট হয়ে যাবে
        if (! $newState && $user->role === 'admin' && $this->activeAdminCount() <= 1) {
            $this->errorNotice = 'এটাই শেষ সক্রিয় অ্যাডমিন — বন্ধ করা যাবে না।';

            return;
        }

        $user->update(['is_active' => $newState]);

        // শিক্ষকের ইউজার বন্ধ/চালু করলে শিক্ষকের রেকর্ডের স্ট্যাটাসও মিলে যায়
        if ($user->role === 'teacher' && $user->teacher_id) {
            Teacher::whereKey($user->teacher_id)->update(['status' => $newState ? 'active' : 'inactive']);
        }

        $this->notice = $newState ? "{$user->name} এর লগইন চালু করা হয়েছে।" : "{$user->name} এর লগইন বন্ধ করা হয়েছে।";
    }

    // ───────────────────────── পাসওয়ার্ড রিসেট ─────────────────────────

    public function resetPassword(string $userId): void
    {
        $this->authorizeAdmin();
        $this->resetNotices();

        $user = $this->scopedUsers()->findOrFail($userId);

        if ($user->id === auth()->id()) {
            $this->errorNotice = 'নিজের পাসওয়ার্ড "আমার প্রোফাইল" থেকে বদলান।';

            return;
        }

        $temp = Str::password(10, symbols: false);

        $user->forceFill([
            'password' => $temp,
            'must_change_password' => true,
        ])->save();

        $this->reveal($user, $temp);
    }

    private function reveal(User $user, string $password): void
    {
        $this->smsResult = null;
        $this->revealed = [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'password' => $password,
            'phone' => $this->phoneFor($user),
        ];
    }

    /** SMS পাঠানোর নম্বর: ইউজারের ফোন, নাহলে অভিভাবকের বানানো ইমেইল থেকে। */
    private function phoneFor(User $user): ?string
    {
        if ($user->phone) {
            return $user->phone;
        }

        if ($user->role === 'guardian' && preg_match('/^guardian-(\d{6,15})@/', (string) $user->email, $m)) {
            return $m[1];
        }

        return null;
    }

    public function sendCredentialsSms(TenantSmsService $sms): void
    {
        $this->authorizeAdmin();

        if (! $this->revealed || empty($this->revealed['phone'])) {
            $this->smsResult = 'এই ইউজারের কোনো ফোন নম্বর নেই।';

            return;
        }

        // এই ইউজার সত্যিই এই প্রতিষ্ঠানের কিনা আবার যাচাই (ক্লায়েন্ট-সাইড ডেটা বিশ্বাস নয়)
        $user = $this->scopedUsers()->find($this->revealed['user_id'] ?? '');
        abort_unless($user, 404);

        $ok = $sms->send(
            $this->tenantId(),
            $this->revealed['phone'],
            "EDUTION লগইন: {$user->email} / পাসওয়ার্ড: {$this->revealed['password']}"
        );

        $this->smsResult = $ok ? 'SMS পাঠানো হয়েছে।' : 'SMS পাঠানো যায়নি — প্রতিষ্ঠানের SMS গেটওয়ে সেটিং দেখুন।';
    }

    public function closeReveal(): void
    {
        $this->revealed = null;
        $this->smsResult = null;
    }

    // ───────────────────────── নতুন অ্যাডমিন ─────────────────────────

    public function openAdminModal(): void
    {
        $this->authorizeAdmin();
        $this->resetValidation();
        $this->reset(['adminName', 'adminEmail']);
        $this->showAdminModal = true;
    }

    public function createAdmin(): void
    {
        $this->authorizeAdmin();
        $this->resetNotices();

        $this->validate([
            'adminName' => 'required|string|min:2|max:255',
            'adminEmail' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->where('institution_id', $this->tenantId()),
            ],
        ], [], ['adminName' => 'নাম', 'adminEmail' => 'ইমেইল']);

        $temp = Str::password(10, symbols: false);

        $user = User::create([
            'institution_id' => $this->tenantId(),
            'name' => $this->adminName,
            'email' => $this->adminEmail,
            'password' => $temp,
            'role' => 'admin',
            'must_change_password' => true,
        ]);

        $this->showAdminModal = false;
        $this->reveal($user, $temp);
    }

    // ───────────────────────── শিক্ষার্থীর লগইন ─────────────────────────

    public function openStudentModal(): void
    {
        $this->authorizeAdmin();
        $this->resetValidation();
        $this->reset(['studentSearch', 'studentId']);
        $this->showStudentModal = true;
    }

    public function createStudentLogin(): void
    {
        $this->authorizeAdmin();
        $this->resetNotices();

        $this->validate(['studentId' => 'required|uuid'], [], ['studentId' => 'শিক্ষার্থী']);

        // Student-এ global tenant scope আছে — অন্য প্রতিষ্ঠানের আইডি দিলে findOrFail ব্যর্থ হয়
        $student = Student::findOrFail($this->studentId);

        if ($this->scopedUsers()->where('student_id', $student->id)->exists()) {
            $this->addError('studentId', 'এই শিক্ষার্থীর লগইন আগেই তৈরি করা আছে।');

            return;
        }

        $institution = Institution::findOrFail($this->tenantId());
        $email = 'student-' . Str::lower(preg_replace('/[^A-Za-z0-9]/', '', (string) $student->student_id_no)) . '@' . $institution->slug . '.local';

        if ($this->scopedUsers()->where('email', $email)->exists()) {
            $this->addError('studentId', 'এই শিক্ষার্থী-আইডির ইমেইল আগেই ব্যবহৃত — সহায়তা নিন।');

            return;
        }

        $temp = Str::password(10, symbols: false);

        $user = User::create([
            'institution_id' => $this->tenantId(),
            'name' => $student->name,
            'email' => $email,
            'password' => $temp,
            'role' => 'student',
            'student_id' => $student->id,
            'must_change_password' => true,
        ]);

        $this->showStudentModal = false;
        $this->reveal($user, $temp);
    }

    public function render()
    {
        $this->authorizeAdmin();

        $users = $this->scopedUsers()
            ->with(['studentProfile'])
            ->when($this->roleFilter !== '', fn ($q) => $q->where('role', $this->roleFilter))
            ->when($this->search !== '', function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(fn ($w) => $w->where('name', 'ilike', $term)->orWhere('email', 'ilike', $term));
            })
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(20);

        $candidates = collect();
        if ($this->showStudentModal) {
            $haveLogin = $this->scopedUsers()->where('role', 'student')->whereNotNull('student_id')->pluck('student_id');

            $candidates = Student::query()
                ->whereNotIn('id', $haveLogin)
                ->when($this->studentSearch !== '', function ($q) {
                    $term = '%' . $this->studentSearch . '%';
                    $q->where(fn ($w) => $w->where('name', 'ilike', $term)->orWhere('student_id_no', 'ilike', $term));
                })
                ->orderBy('name')
                ->limit(15)
                ->get();
        }

        return view('livewire.user-manager', [
            'users' => $users,
            'candidates' => $candidates,
            'roles' => ['admin' => 'অ্যাডমিন', 'teacher' => 'শিক্ষক', 'guardian' => 'অভিভাবক', 'student' => 'শিক্ষার্থী'],
            'currentId' => auth()->id(),
        ])->layout('components.layouts.app', ['title' => 'ব্যবহারকারী']);
    }
}
