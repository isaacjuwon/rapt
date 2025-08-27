<x-mail::message>
# Shares Purchased Successfully!

Hello {{ $notifiable->name }},

You have successfully purchased {{ $shareTransaction->quantity }} shares for ${{ number_format($shareTransaction->total_amount, 2) }}.

<x-mail::button :url="url('/shares')">
View Shares
</x-mail::button>

Thank you for your investment!

Thanks,
{{ config('app.name') }}
</x-mail::message>