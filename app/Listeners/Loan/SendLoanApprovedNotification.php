<?php

namespace App\Listeners\Loan;

use App\Events\Loan\LoanApproved;
use App\Notifications\Loan\LoanApprovedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendLoanApprovedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(LoanApproved $event): void
    {
        $event->loan->user->notify(new LoanApprovedNotification($event->loan));
    }
}