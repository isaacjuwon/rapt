<?php

namespace App\Livewire\Admin\BulkMail;

use Livewire\Volt\Component;
use App\Services\BulkMailService;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    public string $recipients = '';
    public string $subject = '';
    public string $body = '';

    protected $rules = [
        'recipients' => 'required|string',
        'subject' => 'required|string|max:255',
        'body' => 'required|string',
    ];

    public function sendMail(BulkMailService $bulkMailService)
    {
        $this->validate();

        $recipientsArray = array_map('trim', explode(',', $this->recipients));
        $recipientsArray = array_filter($recipientsArray, 'filter_var'); // Filter out invalid emails

        if (empty($recipientsArray)) {
            Session::flash('error', 'No valid recipients found.');
            return;
        }

        $bulkMailService->sendBulkMail($recipientsArray, $this->subject, $this->body);

        $this->reset(['recipients', 'subject', 'body']);
        Session::flash('success', 'Bulk mail sent successfully!');
    }
}
