<?php

use Flux\Flux;
use Masmerise\Toaster\Toaster;
use App\Models\Brand;
use App\Models\AirtimePlan;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.components.admin')] class extends Component {

    public string $name = '';

    public string $description = '';

    public string $status = '';

    public string $brand_id = '';

    /**
     * Reset the form values.
     */
    public function resetValues(): void
    {
        $this->reset(['name', 'description', 'status', 'brand_id']);
        $this->resetErrorBag();
    }

    public function with(): array
    {
        return [
            'brands' => Brand::all(),
        ];
    }

    /**
     * Handle an incoming create airtimePlan request.
     */
    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'status' => ['required', 'boolean'],
        ]);

        AirtimePlan::create([
            'name' => $this->name,
            'description' => $this->description,
            'brand_id' => $this->brand_id,
            'status' => $this->status,
        ]);

        $this->resetValues();
        $this->resetPage();
        $this->modal('create-airtimePlan')->close();

        Toaster::success(__('Airtime Plan created successfully.'));
    }
}; ?>

<div>
    <flux:modal.trigger name="create-airtimePlan">
        <flux:button
            variant="primary"
            icon="user-plus">
            {{ __('Create airtime Plan') }}
        </flux:button>
    </flux:modal.trigger>
    <flux:modal
        wire:close="resetValues"
        name="create-airtimePlan"
        class="w-sm max-w-[calc(100vw-3rem)]">
        <form
            wire:submit="createAirtimePlan"
            class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Create airtimePlan') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Create a new airtimePlan account.') }}
                </flux:text>
            </div>

            <flux:input
                wire:model="name"
                type="text"
                label="{{ __('Name') }}"
                placeholder="{{ __('Your name') }}"
                autocomplete="name"
                autofocus />



            <flux:select
                wire:model="brand_id"
                label="{{ __('Brand') }}"
                placeholder="{{ __('Select a brand') }}"
                autocomplete="brand">

                @foreach ($brands as $brand)
                <flux:select.option
                    value="{{ $brand->id }}">
                    {{ $brand->name }}
                </flux:select.option>
                @endforeach

            </flux:select>

            <flux:textarea
                wire:model="description"
                type="description"
                label="{{ __('Description') }}"
                placeholder="{{ __('Your description') }}"
                rows="2" />

            <flux:field variant="outline">
                <flux:label for="status">{{ __('Status') }}</flux:label>
                <flux:switch
                    id="status"
                    wire:model="status"
                    :checked="$status" />
                <flux:error name="status" />
            </flux:field>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost">
                        {{ __('Cancel') }}
                    </flux:button>
                </flux:modal.close>

                <flux:button
                    type="submit"
                    variant="primary">
                    {{ __('Create') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>



</div>