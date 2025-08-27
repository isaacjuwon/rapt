<?php

namespace App\Notifications\Share;

use App\Models\ShareTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SharePurchasedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public ShareTransaction $shareTransaction)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Shares Purchased Successfully!')
                    ->markdown('emails.share.share-purchased', ['shareTransaction' => $this->shareTransaction, 'notifiable' => $notifiable]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'share_transaction_id' => $this->shareTransaction->id,
            'quantity' => $this->shareTransaction->quantity,
            'amount' => $this->shareTransaction->total_amount,
            'type' => $this->shareTransaction->type,
        ];
    }
}