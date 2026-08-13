<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * SuperadminLoginController — panel.edution.xyz এর জন্য আলাদা login।
 * ⚠️ এখানে institution_id দিয়ে ফিল্টার করা হয় না (superadmin এর
 * institution_id সবসময় null) — শুধু role='superadmin' নিশ্চিত করা হয়।
 */
class SuperadminLoginController extends Controller
{
    public function create()
    {
        return view('auth.superadmin-login');
    }

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

        $user = User::where('email', $credentials['email'])
            ->where('role', 'superadmin')
            ->whereNull('institution_id')
            ->first();

        if (!$user || !Auth::attempt(['id' => $user->id, 'password' => $credentials['password']])) {
            RateLimiter::hit($throttleKey);
            throw ValidationException::withMessages([
                'email' => 'ইমেইল বা পাসওয়ার্ড সঠিক না।',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect()->route('superadmin.institutions');
    }
}
