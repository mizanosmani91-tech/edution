<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SmsOtpService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OtpController extends Controller
{
    public function __construct(protected SmsOtpService $otp)
    {
    }

    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
        ]);

        if (! $this->otp->canResend($validated['phone'])) {
            return response()->json([
                'message' => 'একটু অপেক্ষা করুন — আবার কোড পাঠানোর আগে ৬০ সেকেন্ড লাগবে।',
            ], 429);
        }

        $this->otp->send($validated['phone']);

        return response()->json([
            'message' => 'যাচাইকরণ কোড আপনার মোবাইলে পাঠানো হয়েছে।',
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        if ($this->otp->verify($validated['phone'], $validated['code'])) {
            return response()->json([
                'verified' => true,
                'message' => 'মোবাইল নম্বর সফলভাবে যাচাই হয়েছে।',
            ]);
        }

        return response()->json([
            'verified' => false,
            'message' => 'কোডটি সঠিক নয় অথবা মেয়াদ শেষ হয়ে গেছে — আবার চেষ্টা করুন।',
        ], 422);
    }
}
