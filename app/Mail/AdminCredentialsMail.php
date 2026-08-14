<?php

namespace App\Mail;

use App\Models\Institution;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * AdminCredentialsMail
 *
 * প্রতিষ্ঠান অনুমোদনের পর (বা এডমিন পাসওয়ার্ড রিসেট করলে) লগইন তথ্য
 * ইমেইলে সুন্দরভাবে পাঠানোর জন্য — SMS-এর পাশাপাশি ব্যাকআপ/স্থায়ী রেকর্ড
 * হিসেবে থাকে (SMS দ্রুত পড়া যায়, ইমেইলে পুরো লিংকসহ বিস্তারিত থাকে)।
 */
class AdminCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Institution $institution,
        public string $loginEmail,
        public string $password,
        public bool $isReset = false,
    ) {
    }

    public function build(): self
    {
        $subject = $this->isReset
            ? 'EDUTION — আপনার পাসওয়ার্ড রিসেট হয়েছে'
            : 'EDUTION — আপনার প্রতিষ্ঠান অনুমোদিত হয়েছে';

        return $this->subject($subject)
            ->view('emails.admin-credentials')
            ->with([
                'institution' => $this->institution,
                'loginEmail' => $this->loginEmail,
                'password' => $this->password,
                'isReset' => $this->isReset,
            ]);
    }
}
