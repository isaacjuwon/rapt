<?php

namespace App\Events\Share;

use App\Models\ShareTransaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShareSold extends ShareEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(ShareTransaction $shareTransaction)
    {
        parent::__construct($shareTransaction);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('share-transaction.' . $this->shareTransaction->id),
        ];
    }
}