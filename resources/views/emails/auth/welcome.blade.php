<x-mail::message>
# Welcome to {{ config('app.name') }}!

Hello {{ $notifiable->name }},

Thank you for registering with {{ config('app.name') }}! We are excited to have you on board.

You can now explore all the features and services we offer.

<x-mail::button :url="url('/dashboard')">
Go to Dashboard
</x-mail::button>

If you have any questions, feel free to contact our support team.

Thanks,
The {{ config('app.name') }} Team
</x-mail::message>