<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\Mail\BulkMail;

class BulkMailService
{
    public function sendBulkMail(array $recipients, string $subject, string $body): void
    {
        foreach ($recipients as $recipient) {
            Mail::to($recipient)->send(new BulkMail($subject, $body));
        }
    }
}
