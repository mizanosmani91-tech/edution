<?php

namespace App\Http\Controllers;

use App\Models\DemoAccessRequest;
use App\Models\DemoLead;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * পাবলিক ডেমো (edution.xyz) থেকে সফট রেজিস্ট্রেশন ও অভিভাবক/শিক্ষক ডেমো
 * এক্সেস রিকোয়েস্টের জন্য প্লেইন controller (guest-facing, auth লাগে না)।
 * সব রুটেই throttle middleware আছে (routes/web.php দ্রষ্টব্য)।
 */
class DemoAccessController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'institution_name' => ['nullable', 'string', 'max:150'],
        ]);

        // একই ফোন নম্বর আবার আসলে নতুন লিড না বানিয়ে পুরনোটাই আপডেট —
        // বারবার রেজিস্ট্রেশন করলে সুপার এডমিনের প্যানেলে ডুপ্লিকেট এন্ট্রি জমবে না
        $lead = DemoLead::updateOrCreate(
            ['phone' => $validated['phone']],
            [
                'token' => (string) Str::uuid(),
                'name' => $validated['name'],
                'institution_name' => $validated['institution_name'] ?? null,
            ]
        );

        // এডমিন ডেমো: রেজিস্ট্রেশন করলেই সাথে সাথে দেখা যাবে — আলাদা অনুমোদন লাগে না
        DemoAccessRequest::firstOrCreate(
            ['demo_lead_id' => $lead->id, 'role' => 'admin'],
            ['status' => 'approved', 'unlocked_until' => now()->addDays(7), 'approved_at' => now()]
        );

        return response()->json(['token' => $lead->token]);
    }

    public function status(Request $request)
    {
        $validated = $request->validate(['token' => ['required', 'uuid']]);

        $lead = DemoLead::where('token', $validated['token'])->first();

        if (! $lead) {
            return response()->json(['registered' => false]);
        }

        $requests = $lead->accessRequests()->get()->keyBy('role');

        $shape = function (?DemoAccessRequest $req, string $role) {
            return [
                'myStatus' => $req?->status,
                'unlockedUntil' => $req?->isCurrentlyUnlocked() ? $req->unlocked_until->toIso8601String() : null,
                // শিক্ষক/অভিভাবকের ক্ষেত্রে গ্লোবাল আনলক উইন্ডোই আসল কথা —
                // ফিক্সড/শেয়ার্ড ক্রেডেনশিয়াল, তাই অন্য কারো রিকোয়েস্ট থেকেও
                // আনলক হয়ে থাকলে এই ইউজারও লগইন করতে পারবে
                'globallyUnlockedUntil' => in_array($role, ['teacher', 'guardian'], true)
                    ? optional(DemoAccessRequest::where('role', $role)->where('status', 'approved')->where('unlocked_until', '>=', now())->latest('unlocked_until')->first())->unlocked_until?->toIso8601String()
                    : null,
            ];
        };

        return response()->json([
            'registered' => true,
            'name' => $lead->name,
            'admin' => $shape($requests->get('admin'), 'admin'),
            'teacher' => $shape($requests->get('teacher'), 'teacher'),
            'guardian' => $shape($requests->get('guardian'), 'guardian'),
        ]);
    }

    public function requestAccess(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'uuid'],
            'role' => ['required', 'in:teacher,guardian'],
        ]);

        $lead = DemoLead::where('token', $validated['token'])->first();
        abort_unless($lead, 404);

        $existing = DemoAccessRequest::where('demo_lead_id', $lead->id)
            ->where('role', $validated['role'])
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if (! $existing) {
            DemoAccessRequest::create([
                'demo_lead_id' => $lead->id,
                'role' => $validated['role'],
                'status' => 'pending',
            ]);
        }

        return response()->json(['ok' => true]);
    }
}
