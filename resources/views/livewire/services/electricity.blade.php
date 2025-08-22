<?php

use App\Models\Brand;
use App\Models\ElectricityPlan;
use Livewire\Volt\Component;
use Livewire\Attributes\Computed;

new class extends Component {

    public bool $showConfirmationModal = false;

    public float|int $amount = 0;

    public string|int $network_id = '';

    public string $meter_type = '';

    public $phone;

    public $plan;

    public function purchase() {}

    #[Computed]
    public function selectedNetwork()
    {
        return $this->networks->firstWhere('id', $this->network_id);
    }

    #[Computed]
    public function networks()
    {
        return Brand::active()
            ->whereHas('electricityPlans', function ($query) {
                $query->where('status', true);
            })
            ->orderBy('name')
            ->get();
    }

    public function updated($property, $value): void
    {
        if ($property === 'network_id') {
            $this->plan = ElectricityPlan::where('brand_id', $value)->first();
        }
    }
}; ?>

<section class="w-full">
    <x-page-header :title="__('Purchase Electricity')" :description="__('Purchase electricity below')" />

    <form method="POST" wire:submit="purchase" class="flex flex-col gap-6">

        <x-radio.group class="flex flex-wrap gap-4" label="Networks" required>
            @foreach ($this->networks as $network)
            <x-radio-icon :label="$network->name" :value="$network->id" wire:model.live="network_id" :avatar="$network->image_url" />
            @endforeach
        </x-radio.group>

        <flux:select
            wire:model="meter_type"
            :label="__('Meter Type')"
            :placeholder="__('Select meter type')"
            required>
            <option value="">{{ __('Select meter type') }}</option>
            <option value="prepaid">{{ __('Prepaid') }}</option>
            <option value="postpaid">{{ __('Postpaid') }}</option>
        </flux:select>

        <flux:input
            wire:model="phone"
            :label="__('Phone Number')"
            type="tel"
            required
            autofocus
            autocomplete="phone"
            placeholder="09012121212" />

        <flux:input
            wire:model="amount"
            :label="__('Amount')"
            type="number"
            required
            autofocus
            autocomplete="amount"
            placeholder="100" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" wire:click="$set('showConfirmationModal', true)" class="w-full">{{ __( 'Purchase') }}</flux:button>
        </div>
    </form>

    <flux:modal wire:model.self="showConfirmationModal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Confirmation purchase') }}</flux:heading>

                <flux:subheading>
                    {{ __('Please review your electricity purchase details before confirming.') }}
                </flux:subheading>
            </div>

            <div class="flex w-full items-start gap-6">
                <div class="flex-shrink-0">
                    @if($this->selectedNetwork && $this->selectedNetwork->image_url)
                    <img src="{{ $this->selectedNetwork->image_url }}" alt="{{ $this->selectedNetwork->name }}" class="w-16 h-16 rounded-lg object-cover">
                    @endif
                </div>
                <div class="flex-1">
                    <div class="grid grid-cols-1 gap-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Network:</span>
                            <span class="font-semibold">{{ $this->selectedNetwork?->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Meter Type:</span>
                            <span class="font-semibold">{{ ucfirst($meter_type) }}</span>
                        </div>
                        @isset($plan)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Product:</span>
                            <span class="font-semibold">{{ $plan->name }}</span>
                        </div>
                        @endisset
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Amount:</span>
                            <span class="font-semibold">{{ $amount }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Phone:</span>
                            <span class="font-semibold">{{ $phone }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <p>{{ __('Are you sure you want to purchase this electricity?') }}</p>
            <flux:separator />

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="$set('showConfirmationModal', false)">{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" wire:click="purchase">{{ __('Confirm') }}</flux:button>
            </div>
        </div>
    </flux:modal>

</section>