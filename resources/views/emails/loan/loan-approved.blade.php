<x-mail::message>
# Loan Application Approved!

Hello {{ $notifiable->name }},

Good news! Your loan application for ${{ number_format($loan->amount, 2) }} has been approved.

<x-mail::button :url="url('/loan/' . $loan->id)">
View Approved Loan
</x-mail::button>

The funds will be disbursed to your wallet shortly.

Thanks,
{{ config('app.name') }}
</x-mail::message>