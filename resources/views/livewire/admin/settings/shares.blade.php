<?php

use App\Settings\SharesSettings;
use Livewire\Volt\Component;

new class extends Component {
    public bool $shares_enabled = true;
    public float $minimum_share_amount = 1000.00;
    public float $maximum_share_amount = 1000000.00;
    public int $share_dividend_period = 90;
    public float $default_dividend_rate = 5.5;
    public bool $auto_compound_dividends = false;

    public function mount(): void
    {
        $settings = app(SharesSettings::class);
        $this->shares_enabled = $settings->shares_enabled;
        $this->minimum_share_amount = $settings->minimum_share_amount;
        $this->maximum_share_amount = $settings->maximum_share_amount;
        $this->share_dividend_period = $settings->share_dividend_period;
        $this->default_dividend_rate = $settings->default_dividend_rate;
        $this->auto_compound_dividends = $settings->auto_compound_dividends;
    }

    public function save(): void
    {
        $settings = app(SharesSettings::class);
        $settings->shares_enabled = $this->shares_enabled;
        $settings->minimum_share_amount = $this->minimum_share_amount;
        $settings->maximum_share_amount = $this->maximum_share_amount;
        $settings->share_dividend_period = $this->share_dividend_period;
        $settings->default_dividend_rate = $this->default_dividend_rate;
        $settings->auto_compound_dividends = $this->auto_compound_dividends;
        $settings->save();

        $this->dispatch('settings-saved');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Shares Settings')" :subheading="__('Manage shares and dividend configuration')">
        <form wire:submit="save" class="space-y-6">
            <flux:toggle
                wire:model="shares_enabled"
                :label="__('Enable Shares')"
                :description="__('Allow users to purchase and manage shares')" />

            <flux:input
                wire:model="minimum_share_amount"
                :label="__('Minimum Share Amount')"
                type="number"
                step="0.01"
                required />

            <flux:input
                wire:model="maximum_share_amount"
                :label="__('Maximum Share Amount')"
                type="number"
                step="0.01"
                required />

            <flux:input
                wire:model="share_dividend_period"
                :label="__('Dividend Period (Days)')"
                type="number"
                required />

            <flux:input
                wire:model="default_dividend_rate"
                :label="__('Default Dividend Rate (%)')"
                type="number"
                step="0.1"
                required />

            <flux:toggle
                wire:model="auto_compound_dividends"
                :label="__('Auto Compound Dividends')"
                :description="__('Automatically reinvest dividends into additional shares')" />

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