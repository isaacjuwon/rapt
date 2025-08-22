@props([
    'title',
    'description',
])

 <div class="space-y-2 text-center">
    <h1 class="text-xl font-medium">{{ $title }}</h1>
    <p class="text-center text-sm text-muted-foreground">{{ $description }}</p>
</div>

