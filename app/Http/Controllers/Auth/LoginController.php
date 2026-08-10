<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * LoginController
 *
 * Login পুরোপুরি tenant-aware — একটা institution-এর user ভুলেও অন্য
 * institution-এ লগইন করতে পারবে না, এমনকি সঠিক email+password দিলেও।
 *
 * ফ্লো:
 *   1. Request host থেকে subdomain বের করে Institution রিজলভ
 *   2. সেই Institution-এর মধ্যেই email দিয়ে User খোঁজা (cross-tenant না)
 *   3. Rate limiting (brute-force protection)
 *   4. সফল হলে session-এ login, এরপর SetTenantContext middleware বাকিটা সামলাবে
 */
class LoginController extends Controller
{
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = strtolower($credentials['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => "অনেকবার ভুল চেষ্টা হয়েছে। {$seconds} সেকেন্ড পর আবার চেষ্টা করুন।",
            ]);
        }

        $institution = Institution::resolveFromSubdomain($request->getHost());

        if (!$institution) {
            RateLimiter::hit($throttleKey);
            // subdomain অচেনা — কোন institution সেটা বলাও তথ্য-ফাঁস, তাই generic error
            throw ValidationException::withMessages([
                'email' => 'ইমেইল বা পাসওয়ার্ড সঠিক না।',
            ]);
        }

        // 👇 গুরুত্বপূর্ণ লাইন: institution_id দিয়ে ম্যানুয়াল ফিল্টার (User মডেলে
        // global scope নেই বলেই এটা এখানে explicit করে লিখতে হচ্ছে — এটা ভুলে
        // বাদ দিলে email দিয়ে অন্য institution-এর user match হয়ে যাবে)
        $user = User::where('institution_id', $institution->id)
            ->where('email', $credentials['email'])
            ->first();

        if (!$user || !Auth::attempt([
            'id' => $user->id,
            'password' => $credentials['password'],
        ])) {
            RateLimiter::hit($throttleKey);
            throw ValidationException::withMessages([
                'email' => 'ইমেইল বা পাসওয়ার্ড সঠিক না।',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
