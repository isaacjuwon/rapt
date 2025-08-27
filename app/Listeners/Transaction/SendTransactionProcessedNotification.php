<?php

namespace App\Listeners\Transaction;

use App\Events\Transaction\TransactionProcessed;
use App\Notifications\Transaction\TransactionProcessedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTransactionProcessedNotification implements ShouldQueue
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
    public function handle(TransactionProcessed $event): void
    {
        $event->transaction->user->notify(new TransactionProcessedNotification($event->transaction));
    }
}