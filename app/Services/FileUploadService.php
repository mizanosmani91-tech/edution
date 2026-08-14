<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * FileUploadService
 *
 * Supabase Storage বাদ দেওয়ার পর VPS-এর লোকাল ডিস্কে (বা পরে S3-compatible
 * এ) ফাইল রাখা হবে। ⚠️ tenant isolation এখানেও জরুরি — শুধু ডেটাবেজ না,
 * ফাইল পাথও institution অনুযায়ী namespace করা, নাহলে একজনের আপলোড করা
 * ফাইলের path অনুমান করে আরেকজন অ্যাক্সেস করতে পারবে (IDOR এর মতো ঝুঁকি)।
 *
 * পাথ প্যাটার্ন: {disk}/institutions/{institution_id}/{category}/{uuid}.{ext}
 */
class FileUploadService
{
    private const MAX_SIZE_KB = 2048; // 2MB
    private const ALLOWED_MIMES = ['jpg', 'jpeg', 'png', 'webp'];

    public function store(UploadedFile $file, string $category): string
    {
        $institutionId = app('tenant.institution_id');

        if (!in_array(strtolower($file->getClientOriginalExtension()), self::ALLOWED_MIMES)) {
            throw new \InvalidArgumentException('শুধু jpg, png, webp ফাইল আপলোড করা যাবে।');
        }

        if ($file->getSize() > self::MAX_SIZE_KB * 1024) {
            throw new \InvalidArgumentException('ফাইলের সাইজ 2MB এর বেশি হতে পারবে না।');
        }

        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = "institutions/{$institutionId}/{$category}/{$filename}";

        // 'public' disk ব্যবহার করা হচ্ছে ধরে নিয়ে — production এ S3-compatible
        // (Backblaze B2 ইত্যাদি) disk এ সহজেই সুইচ করা যাবে, শুধু disk নাম বদলাতে হবে
        Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));

        return $path;
    }

    /**
     * পুরনো ফাইল ডিলিট করার আগে নিশ্চিত হওয়া যে সেটা এই institution এরই —
     * path এ institution_id embedded থাকায় এই চেক সহজ, কিন্তু defense হিসেবে
     * এখনো explicit রাখা হলো
     */
    public function delete(?string $path): void
    {
        if (!$path) {
            return;
        }

        $institutionId = (string) app('tenant.institution_id');

        if (!str_starts_with($path, "institutions/{$institutionId}/")) {
            // এই ফাইল এই institution এর না — ডিলিট করা হচ্ছে না, এটা সন্দেহজনক
            report(new \RuntimeException("Attempted cross-tenant file delete: {$path}"));
            return;
        }

        Storage::disk('public')->delete($path);
    }

    public function url(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }
}
