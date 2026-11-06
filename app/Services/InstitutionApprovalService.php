<?php

namespace App\Services;

use App\Mail\AdminCredentialsMail;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * প্রতিষ্ঠান অনুমোদন — সুপারঅ্যাডমিন বাটন ও "অটো-অনুমোদন" সুইচ দুটোই এটা ব্যবহার করে,
 * যাতে নিয়ম একই থাকে: শুধু pending/rejected অনুমোদন হয়, একবারই অ্যাডমিন তৈরি হয়।
 */
class InstitutionApprovalService
{
    /**
     * @return array{institution: Institution, password: string}|null  অনুমোদনযোগ্য না হলে null
     */
    public function approve(string $institutionId): ?array
    {
        $tempPassword = Str::password(10, symbols: false);

        $institution = DB::transaction(function () use ($institutionId, $tempPassword) {
            // অতিথি (লগইনহীন) অনুরোধ থেকে এলে RLS-এর জন্য এই লেনদেনের ভেতরেই সুপারঅ্যাডমিন ফ্ল্যাগ (local, শেষে মুছে যায়)
            DB::statement("SELECT set_config('app.is_superadmin', 'true', true)");

            $institution = Institution::query()->lockForUpdate()->findOrFail($institutionId);

            if (! in_array($institution->status, ['pending', 'rejected'], true)) {
                return null;
            }

            User::updateOrCreate(
                ['institution_id' => $institution->id, 'email' => $institution->registration_email],
                [
                    'name' => $institution->name . ' Admin',
                    'password' => Hash::make($tempPassword),
                    'role' => 'admin',
                    'must_change_password' => true,
                ]
            );

            $institution->update(['status' => 'active']);

            return $institution;
        });

        if (! $institution) {
            return null;
        }

        // পাসওয়ার্ড স্ক্রিনে হারিয়ে গেলেও যেন পাওয়া যায় — ইমেইল + SMS দুটোতেই স্থায়ীভাবে পাঠানো
        try {
            Mail::to($institution->registration_email)
                ->send(new AdminCredentialsMail($institution, $institution->registration_email, $tempPassword, isReset: false));
        } catch (\Throwable $e) {
            Log::warning('এডমিন ক্রেডেনশিয়াল ইমেইল পাঠাতে ব্যর্থ: ' . $e->getMessage());
        }

        if ($institution->phone) {
            try {
                app(SmsOtpService::class)->sendMessage($institution->phone, "EDUTION: Approved. Password: {$tempPassword}");
            } catch (\Throwable $e) {
                Log::warning('অনুমোদন SMS পাঠাতে ব্যর্থ: ' . $e->getMessage());
            }
        }

        return ['institution' => $institution, 'password' => $tempPassword];
    }
}
