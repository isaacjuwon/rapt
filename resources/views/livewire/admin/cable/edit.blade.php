<?php

use Flux\Flux;
use Masmerise\Toaster\Toaster;
use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Locked;
use App\Models\CablePlan; // Changed model
use App\Models\Brand; // Assuming Brand model for brand_id
use Illuminate\Validation\Rule;

new #[Title('Edit Cable Plan')] class extends Component { // Changed title
    #[Locked]
    public CablePlan $cablePlan; // Changed property name

    public string $name = '';
    public ?string $description = null;
    public bool $status = false;
    public ?string $api_code = null;
    public ?string $service_id = null;
    public ?string $reference = null;
    public ?string $type = null;
    public ?string $duration = null;
    public ?float $price = null;
    public ?float $discounted_price = null;
    public ?int $brand_id = null;

    public function mount(CablePlan $cablePlan): void // Changed parameter
    {
        $this->cablePlan = $cablePlan; // Changed property
        $this->name = $cablePlan->name;
        $this->description = $cablePlan->description;
        $this->status = $cablePlan->status;
        $this->api_code = $cablePlan->api_code;
        $this->service_id = $cablePlan->service_id;
        $this->reference = $cablePlan->reference;
        $this->type = $cablePlan->type;
        $this->duration = $cablePlan->duration;
        $this->price = $cablePlan->price;
        $this->discounted_price = $cablePlan->discounted_price;
        $this->brand_id = $cablePlan->brand_id;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:160'],
            'status' => ['required', 'boolean'],
            'api_code' => ['nullable', 'string', 'max:255'],
            'service_id' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'duration' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'discounted_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
        ];
    }

    public function update(): void
    {
        $validated = $this->validate();

        $this->cablePlan->update($validated); // Changed property

        Toaster::success(__('Cable Plan updated successfully.')); // Changed text

        $this->redirect(route('admin.cable.index'), navigate: true); // Changed route
    }

    public function brands()
    {
        return Brand::all();
    }
}; ?>

<div class="space-y-6">
    <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center">
        <div>
            <flux:heading size="xl">{{ __('Edit Cable Plan') }}</flux:heading> // Changed text
            <flux:subheading>
                {{ __('Update the details for the cable plan: :cablePlanName.', ['cablePlanName' => $this->cablePlan->name]) }} // Changed text and property
            </flux:subheading>
        </div>

        <flux:spacer />
    </div>

    <flux:card>
        <form wire:submit="update" class="space-y-4">
            <flux:input
                label="{{ __('Plan Name') }}"
                wire:model="name"
                type="text"
                placeholder="{{ __('Enter plan name') }}"
                required
                autofocus />

            <flux:input
                label="{{ __('Description') }}"
                wire:model="description"
                type="text"
                placeholder="{{ __('Enter description (optional)') }}" />

            <flux:checkbox
                label="{{ __('Status (Active)') }}"
                wire:model="status" />

            <flux:input
                label="{{ __('API Code') }}"
                wire:model="api_code"
                type="text"
                placeholder="{{ __('Enter API code (optional)') }}" />

            <flux:input
                label="{{ __('Service ID') }}"
                wire:model="service_id"
                type="text"
                placeholder="{{ __('Enter service ID (optional)') }}" />

            <flux:input
                label="{{ __('Reference') }}"
                wire:model="reference"
                type="text"
                placeholder="{{ __('Enter reference (optional)') }}" />

            <flux:input
                label="{{ __('Type') }}"
                wire:model="type"
                type="text"
                placeholder="{{ __('Enter type (e.g., daily, weekly, monthly) (optional)') }}" />

            <flux:input
                label="{{ __('Duration') }}"
                wire:model="duration"
                type="text"
                placeholder="{{ __('Enter duration (e.g., 30 days) (optional)') }}" />

            <flux:input
                label="{{ __('Price') }}"
                wire:model="price"
                type="number"
                step="0.01"
                placeholder="{{ __('Enter price (optional)') }}" />

            <flux:input
                label="{{ __('Discounted Price') }}"
                wire:model="discounted_price"
                type="number"
                step="0.01"
                placeholder="{{ __('Enter discounted price (optional)') }}" />

            <flux:select
                label="{{ __('Brand') }}"
                wire:model="brand_id"
                placeholder="{{ __('Select a brand') }}"
                required>
                @foreach ($this->brands() as $brand)
                <flux:select.option value="{{ $brand->id }}">{{ $brand->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex justify-end gap-2">
                <flux:button :href="route('admin.cable.index')" wire:navigate variant="ghost"> // Changed route
                    {{ __('Cancel') }}
                </flux:button>

                <flux:button type="submit">
                    {{ __('Update Cable Plan') }} // Changed text
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>