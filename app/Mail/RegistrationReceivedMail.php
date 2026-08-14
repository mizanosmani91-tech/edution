<?php

namespace App\Mail;

use App\Models\Institution;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * RegistrationReceivedMail
 *
 * নতুন প্রতিষ্ঠান রেজিস্ট্রেশন ফর্ম সাবমিট করার সাথে সাথেই (অনুমোদনের আগেই)
 * পাঠানো হয় — যাতে আবেদনকারী নিশ্চিত হন যে আবেদনটা ঠিকভাবে জমা হয়েছে এবং
 * পরবর্তী ধাপ কী হবে সেটা জানেন।
 */
class RegistrationReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Institution $institution)
    {
    }

    public function build(): self
    {
        return $this->subject('EDUTION — আপনার আবেদন গ্রহণ করা হয়েছে')
            ->view('emails.registration-received')
            ->with(['institution' => $this->institution]);
    }
}
