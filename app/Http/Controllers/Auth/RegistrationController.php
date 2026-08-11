<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        // slug institution নাম থেকে অটো-জেনারেট, uniqueness নিশ্চিত করে
        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;
        while (Institution::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        // ⚠️ এখানে কোনো admin user তৈরি হচ্ছে না — শুধু institution
        // 'pending' স্ট্যাটাসে সেভ হচ্ছে। Super admin approve করলে user
        // তৈরি হবে (SuperadminInstitutionsList::approve() এ)।
        Institution::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'status' => 'pending',
            'phone' => $validated['phone'],
            'address' => $validated['address'] ?? null,
        ]);

        // registration email টা সাময়িকভাবে কোথাও সেভ করা দরকার approve
        // করার সময় admin user বানাতে — institutions টেবিলে email কলাম যোগ করছি
        Institution::where('slug', $slug)->update(['registration_email' => $validated['email']]);

        return redirect()->route('register')->with('success',
            'ধন্যবাদ! আপনার আবেদন জমা হয়েছে। যাচাই করে আমরা শীঘ্রই আপনার ইমেইলে লগইন তথ্য পাঠাব।');
    }
}
