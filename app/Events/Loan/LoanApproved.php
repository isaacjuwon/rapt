<?php

namespace App\Events\Loan;

use App\Models\Loan; // Import the Loan model
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoanApproved extends LoanEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(Loan $loan)
    {
        parent::__construct($loan);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('loan.' . $this->loan->id),
        ];
    }
}
