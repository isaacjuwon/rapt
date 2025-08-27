<?php

namespace App\Events\Transaction;

use App\Models\Transaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionProcessed extends TransactionEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(Transaction $transaction)
    {
        parent::__construct($transaction);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('transaction.' . $this->transaction->id),
        ];
    }
}