<?php

namespace App\Events\Share;

use App\Models\ShareTransaction; // Assuming a ShareTransaction model
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShareEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ShareTransaction $shareTransaction)
    {
        //
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('share-transaction.' . $this->shareTransaction->id),
        ];
    }
}