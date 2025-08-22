<?php

use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    
    public string $collection = 'default';

    public bool $open = false;

    public function browse(): void
    {
        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
    }

    #[On('lw-media:selected')]
    public function closeOnSelect(): void
    {
        $this->open = false;
    }

   
}; ?>


<div>
    <flux:button wire:click="browse">Browse</flux:button>

    <flux:modal wire:model="open">
        <div class="space-y-4 p-4">
            <livewire:lw-media-uploader :collection="$collection" />
            <livewire:lw-media-gallery :collection="$collection" />
        </div>
    </flux:modal>
</div>

