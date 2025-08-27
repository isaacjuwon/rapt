<?php

namespace App\Listeners\Loan;

use App\Events\Loan\LoanDisapproved;
use App\Notifications\Loan\LoanDisapprovedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendLoanDisapprovedNotification implements ShouldQueue
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
    public function handle(LoanDisapproved $event): void
    {
        $event->loan->user->notify(new LoanDisapprovedNotification($event->loan));
    }
}