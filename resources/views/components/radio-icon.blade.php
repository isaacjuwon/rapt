@props([
    'id' => null,
    'name' => null,
    'type' => 'radio',
    'label' => null,
    'badge' => null,
    'badgeColor' => 'zinc',
    'description' => null,
    'avatar' => null,
])

@aware(['name' => $name])

@php
    $id = $id ?? ($label ?? ($attributes->whereStartsWith('wire:model')->first() ?? ($attributes->get('name') ?? Str::random(8))));
    $error = $attributes->whereStartsWith('wire:model')->first() ?? ($attributes->get('name') ?? null);
    $badge ??= $attributes->has('required') ? 'Required' : null;
    $name ??= $attributes->whereStartsWith('wire:model')->first();

@endphp

@php
    $classes = Flux::classes()
        ->add('flex items-center justify-center')
        ->add('mt-px size-[3.125rem] rounded-full outline-offset-2')
        ->add('bg-white dark:bg-white/10 peer-checked:bg-zinc-800 dark:peer-checked:bg-white')
        ->add('border border-zinc-300 dark:border-white/10 peer-checked:border-transparent')
        ->add('shadow-sm disabled:shadow-none peer-checked:shadow-none')
        ->add('peer-checked:[&_[data-indicator]]:block peer-disabled:opacity-50');
@endphp

<flux:with-field variant="default" :$id :$error :$label :$description :$badge :$badgeColor>
    <input {{ $attributes->class('peer sr-only hidden')->merge(['id' => $id, 'type' => $type, 'name' => $name]) }} data-radio data-control>
   <div {{ $attributes->class($classes) }}
        x-on:click.prevent="$el.previousElementSibling.click()"
        x-on:keydown.enter.prevent="$el.previousElementSibling.click()"
        x-on:keydown.space.prevent="$el.previousElementSibling.click()"
        x-bind:tabindex="$el.previousElementSibling.disabled ? '-1' : '0'"
        x-bind:aria-disabled="$el.previousElementSibling.disabled ? 'true' : 'false'"
        x-bind:class="{ 'cursor-pointer': !$el.previousElementSibling.disabled, 'cursor-default': $el.previousElementSibling.disabled }"
        data-radio-indicator>
        <flux:avatar :src="$avatar" size="sm" data-indicator />
   </div>
</flux:with-field>
