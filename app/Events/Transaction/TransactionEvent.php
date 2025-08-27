<?php

namespace App\Events\Transaction;

use App\Models\Transaction; // Assuming a Transaction model
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Transaction $transaction)
    {
        //
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('transaction.' . $this->transaction->id),
        ];
    }
}