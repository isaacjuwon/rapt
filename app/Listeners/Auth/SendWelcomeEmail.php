<?php

namespace App\Listeners\Auth;

use Illuminate\Auth\Events\Registered;
use App\Notifications\Auth\WelcomeEmailNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendWelcomeEmail implements ShouldQueue
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
    public function handle(Registered $event): void
    {
        $event->user->notify(new WelcomeEmailNotification($event->user));
    }
}