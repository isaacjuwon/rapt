<?php

namespace App\Listeners\Loan;

use App\Events\Loan\LoanCreated;
use App\Notifications\Loan\LoanCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendLoanCreatedNotification implements ShouldQueue
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
    public function handle(LoanCreated $event): void
    {
        $event->loan->user->notify(new LoanCreatedNotification($event->loan));
    }
}