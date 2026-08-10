<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * ApiLoginController
 *
 * ভবিষ্যতে মোবাইল অ্যাপের জন্য — LoginController (session-based, ওয়েবের জন্য)
 * এর সমান্তরাল, একই security প্যাটার্ন (subdomain-scoped, rate limited),
 * শুধু response এ session এর বদলে Sanctum token রিটার্ন করে।
 *
 * মোবাইল অ্যাপ যেহেতু subdomain দিয়ে চলে না, request body তে institution
 * এর slug পাঠাতে হবে (host header এর বদলে)।
 */
class ApiLoginController extends Controller
{
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'institution_slug' => ['required', 'string'],
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

        $institution = Institution::where('slug', $credentials['institution_slug'])->first();

        if (!$institution) {
            RateLimiter::hit($throttleKey);
            throw ValidationException::withMessages(['email' => 'তথ্য সঠিক না।']);
        }

        $user = User::where('institution_id', $institution->id)
            ->where('email', $credentials['email'])
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($throttleKey);
            throw ValidationException::withMessages(['email' => 'তথ্য সঠিক না।']);
        }

        RateLimiter::clear($throttleKey);

        // token abilities এ role/institution বেঁধে দেওয়া হলো — ভবিষ্যতে
        // token-level restriction লাগলে এখান থেকে শুরু করা যাবে
        $token = $user->createToken('mobile-app', ["institution:{$institution->id}"]);

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'institution' => $institution->only(['id', 'name', 'slug']),
            ],
        ]);
    }

    public function destroy(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
