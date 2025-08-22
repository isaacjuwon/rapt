<?php

use App\Models\Brand;
use App\Models\DataPlan;
use Livewire\Volt\Component;
use Livewire\Attributes\Computed;

new class extends Component {

    public bool $showConfirmationModal = false;

    public string|int $network_id = '';

    public string $data_type = '';

    public string|int $plan_id = '';

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
            ->whereHas('dataPlans', function ($query) {
                $query->where('status', true);
            })
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function dataTypes()
    {
        if (!$this->network_id) {
            return collect();
        }

        return DataPlan::where('brand_id', $this->network_id)
            ->where('status', true)
            ->distinct()
            ->pluck('type')
            ->filter()
            ->values();
    }

    #[Computed]
    public function plans()
    {
        if (!$this->network_id || !$this->data_type) {
            return collect();
        }

        return DataPlan::where('brand_id', $this->network_id)
            ->where('type', $this->data_type)
            ->where('status', true)
            ->orderBy('size')
            ->get();
    }

    public function updated($property, $value): void
    {
        if ($property === 'network_id') {
            $this->data_type = '';
            $this->plan_id = '';
            $this->plan = null;
        }

        if ($property === 'data_type') {
            $this->plan_id = '';
            $this->plan = null;
        }

        if ($property === 'plan_id') {
            $this->plan = DataPlan::find($value);
        }
    }
}; ?>

<section class="w-full">
    <x-page-header :title="__('Purchase Data')" :description="__('Purchase Data below')" />

    <form method="POST" wire:submit="purchase" class="flex flex-col gap-6">

        <x-radio.group class="flex flex-wrap gap-4" label="Networks" required>
            @foreach ($this->networks as $network)
            <x-radio-icon :label="$network->name" :value="$network->id" wire:model.live="network_id" :avatar="$network->image_url" />
            @endforeach
        </x-radio.group>

        <flux:select
            wire:model.live="data_type"
            :label="__('Data Type')"
            :placeholder="__('Select data type')"
            required
            :disabled="!$network_id">
            <option value="">{{ __('Select data type') }}</option>
            @foreach ($this->dataTypes as $type)
            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
            @endforeach
        </flux:select>

        <flux:select
            wire:model.live="plan_id"
            :label="__('Data Plan')"
            :placeholder="__('Select data plan')"
            required
            :disabled="!$data_type">
            <option value="">{{ __('Select data plan') }}</option>
            @foreach ($this->plans as $plan)
            <option value="{{ $plan->id }}">{{ $plan->name }} - {{ $plan->size }} ({{ number_format($plan->price, 2) }})</option>
            @endforeach
        </flux:select>

        <flux:input
            wire:model="phone"
            :label="__('Phone Number')"
            type="tel"
            required
            autofocus
            autocomplete="phone"
            placeholder="09012121212" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" wire:click="$set('showConfirmationModal', true)" class="w-full" :disabled="!$plan_id">{{ __( 'Purchase') }}</flux:button>
        </div>
    </form>

    <flux:modal wire:model.self="showConfirmationModal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Confirmation purchase') }}</flux:heading>

                <flux:subheading>
                    {{ __('Please review your data purchase details before confirming.') }}
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
                            <span class="text-gray-600 dark:text-gray-400">Data Type:</span>
                            <span class="font-semibold">{{ ucfirst($data_type) }}</span>
                        </div>
                        @isset($plan)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Plan:</span>
                            <span class="font-semibold">{{ $plan->name }} - {{ $plan->size }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Amount:</span>
                            <span class="font-semibold">{{ number_format($plan->price, 2) }}</span>
                        </div>
                        @endisset
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Phone:</span>
                            <span class="font-semibold">{{ $phone }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <p>{{ __('Are you sure you want to purchase this data plan?') }}</p>
            <flux:separator />

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="$set('showConfirmationModal', false)">{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" wire:click="purchase">{{ __('Confirm') }}</flux:button>
            </div>
        </div>
    </flux:modal>

</section>