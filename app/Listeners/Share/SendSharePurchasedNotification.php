<?php

namespace App\Listeners\Share;

use App\Events\Share\SharePurchased;
use App\Notifications\Share\SharePurchasedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSharePurchasedNotification implements ShouldQueue
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
    public function handle(SharePurchased $event): void
    {
        $event->shareTransaction->user->notify(new SharePurchasedNotification($event->shareTransaction));
    }
}