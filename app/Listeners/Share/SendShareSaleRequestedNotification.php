<?php

namespace App\Listeners\Share;

use App\Events\Share\ShareSaleRequested;
use App\Notifications\Share\ShareSaleRequestedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendShareSaleRequestedNotification implements ShouldQueue
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
    public function handle(ShareSaleRequested $event): void
    {
        $event->shareTransaction->user->notify(new ShareSaleRequestedNotification($event->shareTransaction));
    }
}