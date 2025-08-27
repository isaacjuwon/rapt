<?php

namespace App\Listeners\Share;

use App\Events\Share\ShareSold;
use App\Notifications\Share\ShareSoldNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendShareSoldNotification implements ShouldQueue
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
    public function handle(ShareSold $event): void
    {
        $event->shareTransaction->user->notify(new ShareSoldNotification($event->shareTransaction));
    }
}