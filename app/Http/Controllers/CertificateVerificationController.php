<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\View\View;

/**
 * CertificateVerificationController
 *
 * সার্টিফিকেটের QR কোড স্ক্যান করলে এই পাবলিক (লগইন ছাড়া দেখা যায়)
 * পেজে আসল রেকর্ড দেখায় — নাম, সার্টিফিকেট নম্বর, ইস্যুর তারিখ,
 * প্রতিষ্ঠানের নাম। জাল সার্টিফিকেট ঠেকাতে সাহায্য করে, কারণ কেউ
 * কাগজে লেখা তথ্য বদলে দিলেও QR স্ক্যান করলে আসল ডাটাবেজ রেকর্ড দেখা
 * যাবে। tenant middleware ছাড়া রুট (superadmin-এর মতো), তাই সব
 * institution-এর সার্টিফিকেট এখান থেকে যাচাই করা যায় — id UUID
 * অনুমান করা কার্যত অসম্ভব বলে এটা নিরাপদ।
 */
class CertificateVerificationController extends Controller
{
    public function show(string $id): View
    {
        $certificate = Certificate::allTenants()->with(['student.schoolClass', 'student.section', 'institution'])->find($id);

        return view('certificate-verify', ['certificate' => $certificate]);
    }
}
