<?php

use Livewire\Volt\Component;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use App\Models\Media;

new class extends Component {

    public string $collection = 'default';

    #[On('media:uploaded')]
    public function refreshGallery(): void
    {
        // Re-render component when media is uploaded
    }

    public function getItemsProperty(): Collection
    {
        return Media::where('collection', $this->collection)
            ->latest()
            ->get();
    }

    public function delete(int $id): void
    {
        $media = Media::where('collection', $this->collection)
            ->where('id', $id)
            ->firstOrFail();

        Storage::disk($media->disk)->delete($media->path);
        if (!empty($media->meta['conversions'])) {
            foreach ($media->meta['conversions'] as $path) {
                Storage::disk($media->disk)->delete($path);
            }
        }

        $media->delete();

        $this->dispatch('lw-media:deleted', id: $id);
    }

    public function select(int $id): void
    {
        $media = Media::where('collection', $this->collection)
            ->where('id', $id)
            ->firstOrFail();

        $this->dispatch('media:selected', id: $media->id, path: $media->path);
    }

    public function with(): array
    {
        return ['items' => $this->items];
    }

}; ?>

<div class="grid grid-cols-3 gap-4">
    @foreach($items as $media)
        <div class="relative cursor-pointer" wire:click="select({{ $media->id }})">
            <img src="{{ $media->url('thumb') }}" alt="" class="w-full h-auto">
            <flux:button size="xs" wire:click.stop="delete({{ $media->id }})" class="absolute top-1 right-1" wire:loading.attr="disabled">Delete</flux:button>
        </div>
    @endforeach
</div>
