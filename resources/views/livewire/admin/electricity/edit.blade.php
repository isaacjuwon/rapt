<?php

use Flux\Flux;
use Masmerise\Toaster\Toaster;
use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Locked;
use App\Models\ElectricityPlan; // Changed model
use App\Models\Brand; // Assuming Brand model for brand_id
use Illuminate\Validation\Rule;

new #[Title('Edit Electricity Plan')] class extends Component { // Changed title
    #[Locked]
    public ElectricityPlan $electricityPlan; // Changed property name

    public string $name = '';
    public ?string $description = null;
    public bool $status = false;
    public ?string $api_code = null;
    public ?string $service_id = null;
    public ?int $brand_id = null;

    public function mount(ElectricityPlan $electricityPlan): void // Changed parameter
    {
        $this->electricityPlan = $electricityPlan; // Changed property
        $this->name = $electricityPlan->name;
        $this->description = $electricityPlan->description;
        $this->status = $electricityPlan->status;
        $this->api_code = $electricityPlan->api_code;
        $this->service_id = $electricityPlan->service_id;
        $this->brand_id = $electricityPlan->brand_id;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:160'],
            'status' => ['required', 'boolean'],
            'api_code' => ['nullable', 'string', 'max:255'],
            'service_id' => ['nullable', 'string', 'max:255'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
        ];
    }

    public function update(): void
    {
        $validated = $this->validate();

        $this->electricityPlan->update($validated); // Changed property

        Toaster::success(__('Electricity Plan updated successfully.')); // Changed text

        $this->redirect(route('admin.electricity.index'), navigate: true); // Changed route
    }

    public function brands()
    {
        return Brand::all();
    }
}; ?>

<div class="space-y-6">
    <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center">
        <div>
            <flux:heading size="xl">{{ __('Edit Electricity Plan') }}</flux:heading> // Changed text
            <flux:subheading>
                {{ __('Update the details for the electricity plan: :electricityPlanName.', ['electricityPlanName' => $this->electricityPlan->name]) }} // Changed text and property
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
                <flux:button :href="route('admin.electricity.index')" wire:navigate variant="ghost"> // Changed route
                    {{ __('Cancel') }}
                </flux:button>

                <flux:button type="submit">
                    {{ __('Update Electricity Plan') }} // Changed text
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>