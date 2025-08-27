<x-mail::message>
# Loan Application Received

Hello {{ $notifiable->name }},

Your loan application for the amount of ${{ number_format($loan->amount, 2) }} has been successfully received.

<x-mail::button :url="url('/loan/' . $loan->id)">
View Loan Application
</x-mail::button>

We will review your application and get back to you shortly.

Thanks,
{{ config('app.name') }}
</x-mail::message>