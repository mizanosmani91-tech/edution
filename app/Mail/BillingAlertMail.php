<?php

namespace App\Mail;

use App\Models\Institution;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * BillingAlertMail — বিলিং বকেয়া/গ্রেস পিরিয়ড/ব্যালেন্স কম হলে
 * প্রতিষ্ঠানের রেজিস্ট্রেশন ইমেইলে পাঠানো হয়।
 */
class BillingAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Institution $institution,
        public string $alertTitle,
        public string $alertBody,
    ) {
    }

    public function build(): self
    {
        return $this->subject('EDUTION বিলিং — ' . $this->alertTitle)
            ->view('emails.billing-alert')
            ->with([
                'institution' => $this->institution,
                'alertTitle' => $this->alertTitle,
                'alertBody' => $this->alertBody,
            ]);
    }
}
