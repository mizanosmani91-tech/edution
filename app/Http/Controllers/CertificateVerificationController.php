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
 *
 * ⚠️ গুরুত্বপূর্ণ: শুধু Certificate::allTenants() করলেই যথেষ্ট না —
 * eager-load করা student/schoolClass/section মডেলগুলোও BelongsToTenant
 * ব্যবহার করে, আর এই রুটে কোনো SetTenantContext middleware চলে না
 * (subdomain-বিহীন পাবলিক রুট), তাই ওই নেস্টেড রিলেশনগুলোর নিজস্ব
 * global scope 'tenant.institution_id' না পেয়ে RuntimeException ছুঁড়ে
 * 500 এরর দিচ্ছিল। তাই প্রতিটা নেস্টেড রিলেশনেও withoutGlobalScope('tenant')
 * এক্সপ্লিসিটলি বসাতে হলো।
 */
class CertificateVerificationController extends Controller
{
    public function show(string $id): View
    {
        $certificate = Certificate::allTenants()
            ->with([
                'student' => function ($q) {
                    $q->withoutGlobalScope('tenant')->with([
                        'schoolClass' => fn ($q2) => $q2->withoutGlobalScope('tenant'),
                        'section' => fn ($q2) => $q2->withoutGlobalScope('tenant'),
                    ]);
                },
                'institution',
            ])
            ->find($id);

        return view('certificate-verify', ['certificate' => $certificate]);
    }
}
