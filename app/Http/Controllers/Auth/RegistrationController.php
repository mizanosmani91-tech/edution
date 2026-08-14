<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    public function create(Request $request)
    {
        return view('auth.register', [
            'selectedPlan' => $request->query('plan', 'standard'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'institution_type' => ['required', 'in:school,madrasa,kindergarten'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'student_count_estimate' => ['nullable', 'string', 'max:50'],
            'plan' => ['required', 'in:basic,standard,premium'],
        ]);

        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;
        while (Institution::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        Institution::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'institution_type' => $validated['institution_type'],
            'status' => 'pending',
            'plan' => $validated['plan'],
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? null,
            'student_count_estimate' => $validated['student_count_estimate'] ?? null,
            'registration_email' => $validated['email'],
        ]);

        return redirect()->route('register')->with('success',
            "ধন্যবাদ! আপনার আবেদন জমা হয়েছে। যাচাইয়ের পর আমরা আপনাকে ফোনে/ইমেইলে একটি সিক্রেট কোড পাঠাব — সেটি ও রেজিস্ট্রেশনের ইমেইল দিয়ে আপনি \"{$slug}\".edution.xyz থেকে লগইন করতে পারবেন।");
    }
}
