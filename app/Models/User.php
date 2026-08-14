<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Concerns\UuidPrimaryKey;

/**
 * User
 *
 * ⚠️ ইচ্ছাকৃতভাবে এখানে BelongsToTenant trait ব্যবহার করা হয়নি।
 *
 * কারণ: Login-এর সময় (LoginController দেখুন) আমরা এখনো জানি না কোন
 * institution-এর জন্য tenant context সেট করব — সেটা বের করার জন্যই তো
 * User টেবিলে query করা লাগে (email + institution slug দিয়ে)। tenant
 * context সেট হওয়ার *আগে* এই query চলে, তাই global scope এখানে বসালে
 * "chicken-and-egg" সমস্যা হবে (context নেই তো query-ই throw করবে,
 * কেউ লগইনই করতে পারবে না)।
 *
 * এর বদলে LoginController ম্যানুয়ালি institution_id দিয়ে ফিল্টার করে —
 * এটা ইচ্ছাকৃত, ভুলে বাদ পড়েনি।
 *
 * Login সফল হওয়ার পরেই SetTenantContext middleware বাকি সব
 * tenant-বাউন্ড মডেলের (Student, Exam...) জন্য scope সেট করে।
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, UuidPrimaryKey;

    protected $fillable = [
        'institution_id',
        'name',
        'email',
        'phone',
        'password',
        'role',        // admin / teacher / guardian / student / superadmin
        'teacher_id',  // role='teacher' হলে সেট থাকবে (নিজের teacher রেকর্ডের লিংক)
        'student_id',  // role='student' হলে সেট থাকবে
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin' && $this->institution_id === null;
    }

    /**
     * এই ইউজার guardian হলে তার সন্তানরা — guardian portal এখান থেকেই
     * child-selector বানাবে
     */
    public function children()
    {
        return $this->belongsToMany(Student::class, 'guardian_student', 'guardian_id', 'student_id')
            ->withPivot('relationship');
    }

    public function teacherProfile()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function studentProfile()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
