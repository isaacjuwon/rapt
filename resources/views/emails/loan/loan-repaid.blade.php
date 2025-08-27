<x-mail::message>
# Loan Repayment Confirmation

Hello {{ $notifiable->name }},

Your loan of ${{ number_format($loan->amount, 2) }} has been successfully repaid.

<x-mail::button :url="url('/loan/' . $loan->id)">
View Loan Details
</x-mail::button>

Thank you for your prompt repayment!

Thanks,
{{ config('app.name') }}
</x-mail::message>