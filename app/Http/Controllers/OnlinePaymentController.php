<?php

namespace App\Http\Controllers;

use App\Models\FeeCollection;
use App\Services\BkashPaymentService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * অভিভাবক এখান থেকে সরাসরি bKash এ ফি পরিশোধ করলে এডমিনের ম্যানুয়াল
 * কনফার্মেশন ছাড়াই স্বয়ংক্রিয়ভাবে amount_paid/status আপডেট হয়ে যায় —
 * bKash নিজেই টাকা যাচাই করে দেয় বলে "guardian claim" ধাপ এখানে লাগে না।
 */
class OnlinePaymentController extends Controller
{
    public function initiate(Request $request, FeeCollection $feeCollection)
    {
        $this->assertGuardianOwnsFee($feeCollection);

        if ($feeCollection->due_amount <= 0) {
            return back()->with('error', 'এই ফি ইতিমধ্যে পরিশোধিত।');
        }

        try {
            $service = new BkashPaymentService($feeCollection->institution_id);

            $callbackUrl = route('online-payment.callback');

            $result = $service->createPayment(
                (float) $feeCollection->due_amount,
                $feeCollection->id,
                $callbackUrl
            );

            $feeCollection->update([
                'online_gateway' => 'bkash',
                'online_payment_id' => $result['paymentID'] ?? null,
                'online_status' => 'pending',
                'online_initiated_at' => now(),
            ]);

            session(['pending_online_fee_id' => $feeCollection->id]);

            return redirect()->away($result['bkashURL']);
        } catch (\Throwable $e) {
            Log::error('bKash payment initiate failed: '.$e->getMessage());

            return back()->with('error', 'bKash পেমেন্ট শুরু করা যায়নি। কিছুক্ষণ পর আবার চেষ্টা করুন, অথবা ম্যানুয়ালি পেমেন্ট দাবি জমা দিন।');
        }
    }

    /**
     * bKash এখানে GET রিডাইরেক্ট করে ফেরত পাঠায়, সাথে paymentID ও status থাকে।
     * paymentID দিয়ে আবার আমাদের সার্ভার থেকে bKash কে জিজ্ঞেস করে (execute) আসল
     * অবস্থা নিশ্চিত হওয়া হয় — শুধু query string বিশ্বাস করে টাকা যোগ করা হয় না,
     * কারণ URL প্যারামিটার সহজেই জাল করা যায়।
     */
    public function callback(Request $request)
    {
        $paymentId = $request->query('paymentID');
        $status = $request->query('status');
        $feeId = session('pending_online_fee_id');

        session()->forget('pending_online_fee_id');

        if (! $paymentId || ! $feeId) {
            return redirect()->route('portal.guardian', ['tab' => 'fees'])->with('error', 'পেমেন্ট সেশন খুঁজে পাওয়া যায়নি।');
        }

        $feeCollection = FeeCollection::find($feeId);

        if (! $feeCollection || $feeCollection->online_payment_id !== $paymentId) {
            return redirect()->route('portal.guardian', ['tab' => 'fees'])->with('error', 'পেমেন্ট তথ্য মিলছে না।');
        }

        // ইতিমধ্যে completed হয়ে থাকলে দ্বিতীয়বার যোগ না করা (ব্রাউজার back/রিফ্রেশ থেকে সুরক্ষা)
        if ($feeCollection->online_status === 'completed') {
            return redirect()->route('portal.guardian', ['tab' => 'fees'])->with('success', 'পেমেন্ট আগেই সম্পন্ন হয়েছে।');
        }

        if ($status !== 'success') {
            $feeCollection->update(['online_status' => 'failed']);

            return redirect()->route('portal.guardian', ['tab' => 'fees'])->with('error', 'পেমেন্ট বাতিল হয়েছে বা ব্যর্থ হয়েছে।');
        }

        try {
            $service = new BkashPaymentService($feeCollection->institution_id);
            $result = $service->executePayment($paymentId);

            if (($result['transactionStatus'] ?? null) !== 'Completed') {
                $feeCollection->update(['online_status' => 'failed']);

                return redirect()->route('portal.guardian', ['tab' => 'fees'])->with('error', 'bKash পেমেন্ট নিশ্চিত করা যায়নি: '.($result['statusMessage'] ?? 'অজানা কারণ'));
            }

            DB::transaction(function () use ($feeCollection, $result) {
                $amount = (float) ($result['amount'] ?? $feeCollection->due_amount);
                $newPaid = $feeCollection->amount_paid + $amount;

                $feeCollection->update([
                    'amount_paid' => $newPaid,
                    'payment_method' => 'bkash',
                    'transaction_ref' => $result['trxID'] ?? null,
                    'paid_at' => now(),
                    'status' => $newPaid >= $feeCollection->amount_due ? 'paid' : 'partial',
                    'online_status' => 'completed',
                    'online_trx_id' => $result['trxID'] ?? null,
                ]);
            });

            try {
                $feeCollection->loadMissing('institution', 'student');

                app(NotificationService::class)->billingAlert(
                    $feeCollection->institution,
                    'online_payment',
                    'অনলাইন ফি পেমেন্ট সম্পন্ন',
                    ($feeCollection->student->name ?? 'ছাত্র/ছাত্রী').' এর ফি bKash এ পরিশোধ হয়েছে। ট্রানজেকশন: '.($result['trxID'] ?? '')
                );
            } catch (\Throwable $e) {
                // নোটিফিকেশন ব্যর্থ হলেও পেমেন্ট রেকর্ড আটকাবে না
            }

            return redirect()->route('portal.guardian', ['tab' => 'fees'])->with('success', 'পেমেন্ট সফল হয়েছে। ধন্যবাদ।');
        } catch (\Throwable $e) {
            Log::error('bKash payment execute failed: '.$e->getMessage());

            return redirect()->route('portal.guardian', ['tab' => 'fees'])->with('error', 'পেমেন্ট যাচাই করতে সমস্যা হয়েছে। অফিসের সাথে যোগাযোগ করুন, ট্রানজেকশন আইডি সেভ রাখুন।');
        }
    }

    private function assertGuardianOwnsFee(FeeCollection $feeCollection): void
    {
        $user = auth()->user();

        if ($user->role !== 'guardian') {
            abort(403);
        }

        abort_unless(
            $user->children()->where('students.id', $feeCollection->student_id)->exists(),
            403,
            'এই ফি পরিশোধের অনুমতি আপনার নেই।'
        );
    }
}
