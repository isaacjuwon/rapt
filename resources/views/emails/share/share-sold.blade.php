<x-mail::message>
# Shares Sold Successfully!

Hello {{ $notifiable->name }},

Your request to sell {{ $shareTransaction->quantity }} shares has been approved and ${{ number_format($shareTransaction->total_amount, 2) }} has been credited to your wallet.

<x-mail::button :url="url('/shares')">
View Share Transactions
</x-mail::button>

Thank you for using our service!

Thanks,
{{ config('app.name') }}
</x-mail::message>