<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use App\Models\Media;

new class extends Component {

    use WithFileUploads;

    public string $collection = 'default';

    public $file;

    public function uploadMedia(): void
    {
        $this->validate([
            'file' => 'required|image|max:' . config('media-library.max_size_kb'),
        ]);

        $disk = config('media-library.disk');
        $dir = trim(config('media-library.directory'), '/');

        $path = $this->file->store($dir, $disk);

        $media = new Media();
        $media->collection = $this->collection;
        $media->disk = $disk;
        $media->path = $path;
        $media->filename = basename($path);
        $media->mime_type = $this->file->getMimeType();
        $media->size_kb = (int) ceil($this->file->getSize() / 1024);
        $media->meta = [];

        if (class_exists(\Intervention\Image\ImageManager::class)) {
            $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
            $conversions = [];
            foreach (config('media-library.conversions', []) as $name => $settings) {
                $image = $manager->read($this->file->getRealPath());
                $fit = $settings['fit'] ?? 'contain';
                if ($fit === 'cover') {
                    $image->cover($settings['w'], $settings['h']);
                } else {
                    $image->contain($settings['w'], $settings['h']);
                }
                $extension = pathinfo($media->filename, PATHINFO_EXTENSION);
                $basename = pathinfo($media->filename, PATHINFO_FILENAME);
                $conversionPath = $dir . '/' . $basename . '-' . $name . '.' . $extension;
                $encoded = method_exists($image, 'encode') ? $image->encode() : $image->toJpeg(90);
                Storage::disk($disk)->put($conversionPath, (string) $encoded);
                $conversions[$name] = $conversionPath;
            }
            $media->meta = ['conversions' => $conversions];
        }

        $media->save();

        $this->dispatch('media:uploaded', id: $media->id);

        $this->reset('file');
    }
}; ?>

<div>
    <flux:input type="file" wire:model="file" accept="{{ implode(',', config('media-library.allowed_mimes')) }}" />
    @error('file')<div class="text-red-600">{{ $message }}</div>@enderror
    <flux:button wire:click="uploadMedia" wire:loading.attr="disabled">Upload</flux:button>
    </d <flux:input type="file" wire:model="file" accept="{{ implode(',', config('media-library.allowed_mimes')) }}" />
    iv>