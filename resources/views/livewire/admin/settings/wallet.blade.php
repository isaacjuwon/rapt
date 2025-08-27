<?php

use App\Settings\WalletSettings;
use Livewire\Volt\Component;

new class extends Component {
    public bool $wallet_enabled = true;
    public float $minimum_deposit = 100.00;
    public float $maximum_deposit = 1000000.00;
    public float $minimum_withdrawal = 1000.00;
    public float $maximum_withdrawal = 1000000.00;

    public function mount(): void
    {
        $settings = app(WalletSettings::class);
        $this->wallet_enabled = $settings->wallet_enabled;
        $this->minimum_deposit = $settings->minimum_deposit;
        $this->maximum_deposit = $settings->maximum_deposit;
        $this->minimum_withdrawal = $settings->minimum_withdrawal;
        $this->maximum_withdrawal = $settings->maximum_withdrawal;
    }

    public function save(): void
    {
        $settings = app(WalletSettings::class);
        $settings->wallet_enabled = $this->wallet_enabled;
        $settings->minimum_deposit = $this->minimum_deposit;
        $settings->maximum_deposit = $this->maximum_deposit;
        $settings->minimum_withdrawal = $this->minimum_withdrawal;
        $settings->maximum_withdrawal = $this->maximum_withdrawal;
        $settings->save();

        $this->dispatch('settings-saved');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Wallet Settings')" :subheading="__('Manage wallet and transaction configuration')">
        <form wire:submit="save" class="space-y-6">
            <flux:toggle
                wire:model="wallet_enabled"
                :label="__('Enable Wallet')"
                :description="__('Allow users to use the wallet feature')" />

            <flux:input
                wire:model="minimum_deposit"
                :label="__('Minimum Deposit Amount')"
                type="number"
                step="0.01"
                required />

            <flux:input
                wire:model="maximum_deposit"
                :label="__('Maximum Deposit Amount')"
                type="number"
                step="0.01"
                required />

            <flux:input
                wire:model="minimum_withdrawal"
                :label="__('Minimum Withdrawal Amount')"
                type="number"
                step="0.01"
                required />

            <flux:input
                wire:model="maximum_withdrawal"
                :label="__('Maximum Withdrawal Amount')"
                type="number"
                step="0.01"
                required />

            <div class="flex items-center gap-4">
                <flux:button type="submit" variant="primary">
                    {{ __('Save Settings') }}
                </flux:button>

                <x-action-message class="me-3" on="settings-saved">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
