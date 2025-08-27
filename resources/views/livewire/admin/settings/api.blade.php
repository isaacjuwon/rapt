<?php

use App\Settings\ApiSettings;
use Livewire\Volt\Component;

new class extends Component {
    public array $configurations = [];
    public string $new_key = '';
    public string $new_value = '';
    public string $edit_key = '';
    public string $edit_value = '';

    public function mount(): void
    {
        $settings = app(ApiSettings::class);
        $this->configurations = $settings->configurations;
    }

    public function save(): void
    {
        $settings = app(ApiSettings::class);
        $settings->configurations = $this->configurations;
        $settings->save();

        $this->dispatch('settings-saved');
    }

    public function addConfiguration(): void
    {
        if (!empty($this->new_key) && !empty($this->new_value)) {
            $this->configurations[$this->new_key] = $this->new_value;
            $this->new_key = '';
            $this->new_value = '';
        }
    }

    public function removeConfiguration($key): void
    {
        unset($this->configurations[$key]);
    }

    public function editConfiguration($key): void
    {
        $this->edit_key = $key;
        $this->edit_value = $this->configurations[$key] ?? '';
    }

    public function updateConfiguration(): void
    {
        if (!empty($this->edit_key) && !empty($this->edit_value)) {
            $this->configurations[$this->edit_key] = $this->edit_value;
            $this->edit_key = '';
            $this->edit_value = '';
        }
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl">API Settings</flux:heading>
    <flux:subheading>Manage API configurations and credentials</flux:subheading>


    <form wire:submit="save" class="mt-6 space-y-6 max-w-2xl">
        <!-- Add New Configuration -->
        <flux:card>
            <flux:heading size="lg">Add New Configuration</flux:heading>
            <div class="grid grid-cols-2 gap-4 mt-4">
                <flux:input
                    wire:model="new_key"
                    label="Key"
                    type="text"
                    placeholder="e.g., epins_base_url" />
                <flux:input
                    wire:model="new_value"
                    label="Value"
                    type="text"
                    placeholder="e.g., https://api.epins.com" />
            </div>
            <flux:button
                type="button"
                variant="outline"
                wire:click="addConfiguration"
                class="mt-4">
                Add Configuration
            </flux:button>
        </flux:card>

        <!-- Existing Configurations -->
        <div class="space-y-4">
            <flux:heading size="lg">Existing Configurations</flux:heading>

            @foreach($configurations as $key => $value)
            <flux:card>
                @if($edit_key === $key)
                <div class="grid grid-cols-2 gap-4">
                    <flux:input
                        wire:model="edit_key"
                        label="Key"
                        type="text" />
                    <flux:input
                        wire:model="edit_value"
                        label="Value"
                        type="text" />
                </div>
                <div class="flex gap-2 mt-4">
                    <flux:button
                        type="button"
                        variant="primary"
                        wire:click="updateConfiguration">
                        Update
                    </flux:button>
                    <flux:button
                        type="button"
                        variant="outline"
                        wire:click="$set('edit_key', '')">
                        Cancel
                    </flux:button>
                </div>
                @else
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="font-medium">{{ $key }}</div>
                        <div class="text-sm text-gray-600">{{ str($value)->limit(50) }}</div>
                    </div>
                    <div class="flex gap-2">
                        <flux:button
                            type="button"
                            variant="outline"
                            size="sm"
                            wire:click="editConfiguration('{{ $key }}')">
                            Edit
                        </flux:button>
                        <flux:button
                            type="button"
                            variant="danger"
                            size="sm"
                            wire:click="removeConfiguration('{{ $key }}')">
                            Remove
                        </flux:button>
                    </div>
                </div>
                @endif
            </flux:card>
            @endforeach

            @if(empty($configurations))
            <flux:callout variant="muted">
                No API configurations found.
            </flux:callout>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <flux:button type="submit" variant="primary">
                Save Settings
            </flux:button>

            <x-action-message on="settings-saved">
                Saved.
            </x-action-message>
        </div>
    </form>
</section>