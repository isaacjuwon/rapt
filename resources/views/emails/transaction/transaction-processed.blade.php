<x-mail::message>
# Transaction Processed: {{ ucfirst($transaction->type) }} - {{ ucfirst($transaction->status) }}

Hello {{ $notifiable->name }},

Your transaction with reference {{ $transaction->reference }} has been processed.

**Type:** {{ ucfirst($transaction->type) }}
**Amount:** ${{ number_format($transaction->amount, 2) }}
**Status:** {{ ucfirst($transaction->status) }}

@if ($transaction->description)
**Description:** {{ $transaction->description }}
@endif

<x-mail::button :url="url('/transactions')">
View Transaction
</x-mail::button>

Thanks,
{{ config('app.name') }}
</x-mail::message>