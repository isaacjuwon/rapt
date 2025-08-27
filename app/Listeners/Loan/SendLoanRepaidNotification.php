<?php

namespace App\Listeners\Loan;

use App\Events\Loan\LoanRepaid;
use App\Notifications\Loan\LoanRepaidNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendLoanRepaidNotification implements ShouldQueue
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
    public function handle(LoanRepaid $event): void
    {
        $event->loan->user->notify(new LoanRepaidNotification($event->loan));
    }
}