<?php

use App\Models\Brand;
use App\Models\EducationPlan;
use Livewire\Volt\Component;
use Livewire\Attributes\Computed;

new class extends Component {

    public bool $showConfirmationModal = false;

    public string|int $operator_id = '';

    public string|int $plan_id = '';

    public $email;

    public $plan;

    public function purchase() {}

    #[Computed]
    public function selectedOperator()
    {
        return $this->operators->firstWhere('id', $this->operator_id);
    }

    #[Computed]
    public function operators()
    {
        return Brand::active()
            ->whereHas('educationPlans', function ($query) {
                $query->where('status', true);
            })
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function plans()
    {
        if (!$this->operator_id) {
            return collect();
        }

        return EducationPlan::where('brand_id', $this->operator_id)
            ->where('status', true)
            ->orderBy('name')
            ->get();
    }

    public function updated($property, $value): void
    {
        if ($property === 'operator_id') {
            $this->plan_id = '';
            $this->plan = null;
        }

        if ($property === 'plan_id') {
            $this->plan = EducationPlan::find($value);
        }
    }
}; ?>

<section class="w-full">
    <x-page-header :title="__('Purchase Education Pin')" :description="__('Purchase education pins below')" />

    <form method="POST" wire:submit="purchase" class="flex flex-col gap-6">

        <x-radio.group class="flex flex-wrap gap-4" label="Operators" required>
            @foreach ($this->operators as $operator)
            <x-radio-icon :label="$operator->name" :value="$operator->id" wire:model.live="operator_id" :avatar="$operator->image_url" />
            @endforeach
        </x-radio.group>

        <flux:select
            wire:model.live="plan_id"
            :label="__('Education Plan')"
            :placeholder="__('Select education plan')"
            required
            :disabled="!$operator_id">
            <option value="">{{ __('Select education plan') }}</option>
            @foreach ($this->plans as $plan)
            <option value="{{ $plan->id }}">{{ $plan->name }} ({{ \Illuminate\Support\Number::currency($plan->price) }})</option>
            @endforeach
        </flux:select>

        <flux:input
            wire:model="email"
            :label="__('Email Address')"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="user@example.com" />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" wire:click="$set('showConfirmationModal', true)" class="w-full" :disabled="!$plan_id">{{ __( 'Purchase') }}</flux:button>
        </div>
    </form>

    <flux:modal wire:model.self="showConfirmationModal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Confirmation purchase') }}</flux:heading>

                <flux:subheading>
                    {{ __('Please review your education pin purchase details before confirming.') }}
                </flux:subheading>
            </div>

            <div class="flex w-full items-start gap-6">
                <div class="flex-shrink-0">
                    @if($this->selectedOperator && $this->selectedOperator->image_url)
                    <img src="{{ $this->selectedOperator->image_url }}" alt="{{ $this->selectedOperator->name }}" class="w-16 h-16 rounded-lg object-cover">
                    @endif
                </div>
                <div class="flex-1">
                    <div class="grid grid-cols-1 gap-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Operator:</span>
                            <span class="font-semibold">{{ $this->selectedOperator?->name }}</span>
                        </div>
                        @isset($plan)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Plan:</span>
                            <span class="font-semibold">{{ $plan->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Amount:</span>
                            <span class="font-semibold">{{ \Illuminate\Support\Number::currency($plan->price) }}</span>
                        </div>
                        @endisset
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Email:</span>
                            <span class="font-semibold">{{ $email }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <p>{{ __('Are you sure you want to purchase this education pin?') }}</p>
            <flux:separator />

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="$set('showConfirmationModal', false)">{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" wire:click="purchase">{{ __('Confirm') }}</flux:button>
            </div>
        </div>
    </flux:modal>

</section>