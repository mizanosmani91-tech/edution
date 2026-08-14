<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\RegistrationReceivedMail;
use App\Models\Institution;
use App\Services\SmsOtpService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    /**
     * ⚠️ এই নামগুলো subdomain হিসেবে ব্যবহার করা যাবে না — প্ল্যাটফর্মের
     * নিজস্ব সাবডোমেইন (panel, www) বা সাধারণ বিভ্রান্তিকর নাম।
     */
    protected const RESERVED_SLUGS = [
        'www', 'panel', 'app', 'api', 'admin', 'edution', 'mail', 'ftp',
        'smtp', 'staging', 'test', 'assets', 'static', 'cdn', 'support',
        'help', 'blog', 'docs', 'status', 'demo',
    ];

    public function checkSlug(Request $request)
    {
        $validated = $request->validate(['slug' => ['required', 'string', 'max:63']]);

        $slug = Str::slug($validated['slug']);

        if ($slug === '') {
            return response()->json(['available' => false, 'message' => 'অবৈধ সাবডোমেইন।']);
        }

        if (in_array($slug, self::RESERVED_SLUGS, true)) {
            return response()->json(['available' => false, 'message' => 'এই নামটি ব্যবহার করা যাবে না, অন্য নাম চেষ্টা করুন।']);
        }

        $taken = Institution::where('slug', $slug)->exists();

        return response()->json([
            'available' => ! $taken,
            'slug' => $slug,
            'message' => $taken ? 'এই সাবডোমেইনটি ইতিমধ্যে ব্যবহৃত হচ্ছে।' : 'এই সাবডোমেইনটি খালি আছে।',
        ]);
    }

    public function create(Request $request)
    {
        return view('auth.register', [
            'selectedBillingType' => $request->query('billing_type', 'postpaid'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'institution_type' => ['required', 'in:school,madrasa,kindergarten'],
            'eiin' => ['nullable', 'string', 'max:50'],
            'division' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'founding_year' => ['nullable', 'string', 'max:10'],
            'address' => ['required', 'string', 'max:500'],
            'student_count_estimate' => ['nullable', 'string', 'max:50'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_designation' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'billing_type' => ['required', 'in:postpaid,prepaid'],
            'preferred_subdomain' => ['nullable', 'string', 'max:63', 'regex:/^[a-z0-9-]+$/'],
            'terms' => ['accepted'],
        ]);

        // ⚠️ ফর্ম সাবমিট করার আগে OTP দিয়ে মোবাইল নম্বর যাচাই বাধ্যতামূলক —
        // /register/send-otp ও /register/verify-otp দিয়ে ফ্রন্টএন্ডে যাচাই হয়,
        // এখানে সার্ভার-সাইডে সেটা আবার নিশ্চিত করা হচ্ছে (ফর্ম বাইপাস ঠেকাতে)
        $otpService = app(SmsOtpService::class);
        if (! $otpService->isVerified($validated['phone'])) {
            return back()->withErrors(['phone' => 'অনুগ্রহ করে আগে মোবাইল নম্বর OTP দিয়ে যাচাই করুন।'])->withInput();
        }

        $baseSlug = $validated['preferred_subdomain'] ?? Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;
        while (Institution::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $institution = Institution::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'preferred_subdomain' => $validated['preferred_subdomain'] ?? null,
            'institution_type' => $validated['institution_type'],
            'eiin' => $validated['eiin'] ?? null,
            'division' => $validated['division'] ?? null,
            'district' => $validated['district'],
            'founding_year' => $validated['founding_year'] ?? null,
            'status' => 'pending',
            'billing_type' => $validated['billing_type'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'student_count_estimate' => $validated['student_count_estimate'] ?? null,
            'admin_name' => $validated['admin_name'],
            'admin_designation' => $validated['admin_designation'],
            'registration_email' => $validated['email'],
        ]);

        $otpService->clearVerified($validated['phone']);

        try {
            Mail::to($institution->registration_email)->send(new RegistrationReceivedMail($institution));
        } catch (\Throwable $e) {
            // ⚠️ ইমেইল পাঠাতে ব্যর্থ হলেও রেজিস্ট্রেশন ভেঙে না পড়ে — শুধু লগ করা হলো
            Log::warning('রেজিস্ট্রেশন কনফার্মেশন ইমেইল পাঠাতে ব্যর্থ: ' . $e->getMessage());
        }

        return redirect()->route('register')->with('success', true)
            ->with('successSlug', $slug)
            ->with('successEmail', $validated['email'])
            ->with('successBillingType', $validated['billing_type']);
    }
}
