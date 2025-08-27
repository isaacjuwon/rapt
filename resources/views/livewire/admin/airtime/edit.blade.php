<?php

use Flux\Flux;
use Masmerise\Toaster\Toaster;
use App\Models\Brand;
use App\Models\AirtimePlan;
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new  #[Layout('layouts.components.admin')] class extends Component {

    public string $name = '';

    public string $description = '';

    public string $status = '';

    public string $brand_id = '';

    public ?AirtimePlan $plan = null;

    public bool $modal = false;

    /**
     * Reset the form values.
     */
    public function resetValues(): void
    {
        $this->reset(['name', 'description', 'status', 'brand_id']);
        $this->resetErrorBag();
        $this->plan = null;
    }

    /**
     * Select a airtimePlan for editing.
     */
    #[On('load::plan')]
    public function editAirtimePlan(AirtimePlan $airtimePlan): void
    {
        $this->plan = $airtimePlan;
        $this->name = $airtimePlan->name;
        $this->description = $airtimePlan->description;
        $this->brand_id = $airtimePlan->brand_id;


        $this->modal = true;
    }

    /**
     * Update the selected airtimePlan.
     */
    public function updateAirtimePlan(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'lowercase', 'description', 'max:255', 'unique:' . AirtimePlan::class . ',description,' . $this->plan->id],
        ]);

        if ($this->name === $this->plan->name && $this->description === $this->plan->description) {
            Toaster::info(__('Nothing changed.'));

            return;
        }

        $this->plan->update([
            'name' => $this->name,
            'description' => $this->description,
            'brand_id' => $this->brand_id,
            'status' => $this->status,
        ]);

        $this->resetValues();
        $this->modal('edit-airtimePlan')->close();

        Toaster::success(__('AirtimePlan updated successfully.'));
    }


    public function with(): array
    {
        return [
            'brands' => Brand::all(),
        ];
    }
}; ?>

<div>
    <flux:modal
        wire:close="resetValues"
        wire:model.self="modal"
        class="w-sm max-w-[calc(100vw-3rem)]">
        <form
            wire:submit="updateAirtimePlan"
            class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Edit airtimePlan') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Update a airtimePlan account.') }}
                </flux:text>

                <div class="mt-2 flex min-h-10 items-center gap-3">
                    <flux:icon.loading
                        wire:loading.delay.long
                        wire:target="editAirtimePlan, resetValues"
                        variant="mini"
                        class="inline" />

                    <div
                        wire:loading.delay.long.hidden
                        wire:target="editAirtimePlan, resetValues">
                        <flux:heading size="sm">
                            {{ $plan?->name }}
                        </flux:heading>
                        <flux:text>
                            {{ $plan?->description }}
                        </flux:text>
                    </div>
                </div>
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
                    value="{{ $brand->id }}"
                    :selected="$brand->id === $brand_id">
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
                    {{ __('Update') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

</div>