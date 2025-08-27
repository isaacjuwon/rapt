<x-mail::message>
# Loan Application Disapproved

Hello {{ $notifiable->name }},

We regret to inform you that your loan application for ${{ number_format($loan->amount, 2) }} has been disapproved.

<x-mail::button :url="url('/loan/' . $loan->id)">
View Loan Details
</x-mail::button>

Please review the details on your loan page or contact support for more information.

Thanks,
{{ config('app.name') }}
</x-mail::message>