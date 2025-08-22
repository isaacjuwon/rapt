@props([
'title',
'description',
])

<div class="relative mb-6 w-full">
    <flux:heading size="xl" level="1">{{ $title }}</flux:heading>
    <flux:subheading size="lg" class="mb-6">{{ $description }}</flux:subheading>
    <flux:separator variant="subtle" />
</div>