<?php

namespace App\Mail;

use App\Models\Institution;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * RegistrationRejectedMail — আবেদন বাতিল হলে আবেদনকারীকে জানানো।
 * (আগে বাতিল করলে কিছুই পাঠানো হতো না।)
 */
class RegistrationRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Institution $institution)
    {
    }

    public function build(): self
    {
        return $this->subject('EDUTION — আপনার আবেদন সম্পর্কে')
            ->view('emails.registration-rejected')
            ->with(['institution' => $this->institution]);
    }
}
