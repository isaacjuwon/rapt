<x-mail::message>
# Share Sale Request Received

Hello {{ $notifiable->name }},

Your request to sell {{ $shareTransaction->quantity }} shares for ${{ number_format($shareTransaction->total_amount, 2) }} has been received.

<x-mail::button :url="url('/shares')">
View Share Transactions
</x-mail::button>

This request is pending admin approval. You will be notified once it has been processed.

Thanks,
{{ config('app.name') }}
</x-mail::message>