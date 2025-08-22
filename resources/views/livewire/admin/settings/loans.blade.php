<?php

use App\Settings\LoanSettings;
use Livewire\Volt\Component;

new class extends Component {
    // General Settings
    public bool $loans_enabled = true;

    // Share Requirement Settings
    public float $shares_requirement_percentage = 30.0;
    public bool $shares_requirement_enabled = true;

    // Loan Amount Settings
    public float $minimum_loan_amount = 1000.0;
    public float $maximum_loan_amount = 100000.0;

    // Interest Rate Settings
    public float $default_interest_rate = 5.0;
    public float $minimum_interest_rate = 1.0;
    public float $maximum_interest_rate = 25.0;

    // Loan Term Settings
    public int $minimum_loan_term_months = 6;
    public int $maximum_loan_term_months = 60;

    // Payment Settings
    public int $grace_period_days = 7;
    public float $late_payment_fee = 500.00;

    // Validation Settings
    public bool $allow_multiple_active_loans = true;
    public bool $require_no_defaulted_loans = true;

    // Legacy Settings (keeping for compatibility)
    public bool $require_collateral = true;
    public float $collateral_percentage = 50.0;
    public int $credit_score_threshold = 600;
    public bool $auto_approval_enabled = false;

    public function mount(): void
    {
        $settings = app(LoanSettings::class);

        // General Settings
        $this->loans_enabled = $settings->loans_enabled;

        // Share Requirement Settings
        $this->shares_requirement_percentage = $settings->shares_requirement_percentage;
        $this->shares_requirement_enabled = $settings->shares_requirement_enabled;

        // Loan Amount Settings
        $this->minimum_loan_amount = $settings->minimum_loan_amount;
        $this->maximum_loan_amount = $settings->maximum_loan_amount;

        // Interest Rate Settings
        $this->default_interest_rate = $settings->default_interest_rate;
        $this->minimum_interest_rate = $settings->minimum_interest_rate;
        $this->maximum_interest_rate = $settings->maximum_interest_rate;

        // Loan Term Settings
        $this->minimum_loan_term_months = $settings->minimum_loan_term_months;
        $this->maximum_loan_term_months = $settings->maximum_loan_term_months;

        // Payment Settings
        $this->grace_period_days = $settings->grace_period_days;
        $this->late_payment_fee = $settings->late_payment_fee;

        // Validation Settings
        $this->allow_multiple_active_loans = $settings->allow_multiple_active_loans;
        $this->require_no_defaulted_loans = $settings->require_no_defaulted_loans;

        // Legacy Settings
        $this->require_collateral = $settings->require_collateral;
        $this->collateral_percentage = $settings->collateral_percentage;
        $this->credit_score_threshold = $settings->credit_score_threshold;
        $this->auto_approval_enabled = $settings->auto_approval_enabled;
    }

    public function save(): void
    {
        $settings = app(LoanSettings::class);

        // General Settings
        $settings->loans_enabled = $this->loans_enabled;

        // Share Requirement Settings
        $settings->shares_requirement_percentage = $this->shares_requirement_percentage;
        $settings->shares_requirement_enabled = $this->shares_requirement_enabled;

        // Loan Amount Settings
        $settings->minimum_loan_amount = $this->minimum_loan_amount;
        $settings->maximum_loan_amount = $this->maximum_loan_amount;

        // Interest Rate Settings
        $settings->default_interest_rate = $this->default_interest_rate;
        $settings->minimum_interest_rate = $this->minimum_interest_rate;
        $settings->maximum_interest_rate = $this->maximum_interest_rate;

        // Loan Term Settings
        $settings->minimum_loan_term_months = $this->minimum_loan_term_months;
        $settings->maximum_loan_term_months = $this->maximum_loan_term_months;

        // Payment Settings
        $settings->grace_period_days = $this->grace_period_days;
        $settings->late_payment_fee = $this->late_payment_fee;

        // Validation Settings
        $settings->allow_multiple_active_loans = $this->allow_multiple_active_loans;
        $settings->require_no_defaulted_loans = $this->require_no_defaulted_loans;

        // Legacy Settings
        $settings->require_collateral = $this->require_collateral;
        $settings->collateral_percentage = $this->collateral_percentage;
        $settings->credit_score_threshold = $this->credit_score_threshold;
        $settings->auto_approval_enabled = $this->auto_approval_enabled;

        $settings->save();

        $this->dispatch('settings-saved');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Loan Settings')" :subheading="__('Manage loan configuration and policies')">
        <form wire:submit="save" class="space-y-8">
            <!-- General Settings -->
            <div class="space-y-4">
                <flux:heading size="lg">General Settings</flux:heading>

                <flux:toggle
                    wire:model="loans_enabled"
                    :label="__('Enable Loans')"
                    :description="__('Allow users to apply for loans')" />
            </div>

            <!-- Share Requirement Settings -->
            <div class="space-y-4">
                <flux:heading size="lg">Share Requirement Settings</flux:heading>

                <flux:toggle
                    wire:model="shares_requirement_enabled"
                    :label="__('Enable Share Requirement')"
                    :description="__('Require users to own shares to apply for loans')" />

                <flux:input
                    wire:model="shares_requirement_percentage"
                    :label="__('Share Requirement Percentage (%)')"
                    type="number"
                    step="0.1"
                    min="0"
                    max="100"
                    :disabled="!$shares_requirement_enabled"
                    required />
            </div>

            <!-- Loan Amount Settings -->
            <div class="space-y-4">
                <flux:heading size="lg">Loan Amount Settings</flux:heading>

                <flux:input
                    wire:model="minimum_loan_amount"
                    :label="__('Minimum Loan Amount')"
                    type="number"
                    step="0.01"
                    min="0"
                    required />

                <flux:input
                    wire:model="maximum_loan_amount"
                    :label="__('Maximum Loan Amount')"
                    type="number"
                    step="0.01"
                    min="0"
                    required />
            </div>

            <!-- Interest Rate Settings -->
            <div class="space-y-4">
                <flux:heading size="lg">Interest Rate Settings</flux:heading>

                <flux:input
                    wire:model="default_interest_rate"
                    :label="__('Default Interest Rate (%)')"
                    type="number"
                    step="0.1"
                    min="0"
                    required />

                <flux:input
                    wire:model="minimum_interest_rate"
                    :label="__('Minimum Interest Rate (%)')"
                    type="number"
                    step="0.1"
                    min="0"
                    required />

                <flux:input
                    wire:model="maximum_interest_rate"
                    :label="__('Maximum Interest Rate (%)')"
                    type="number"
                    step="0.1"
                    min="0"
                    required />
            </div>

            <!-- Loan Term Settings -->
            <div class="space-y-4">
                <flux:heading size="lg">Loan Term Settings</flux:heading>

                <flux:input
                    wire:model="minimum_loan_term_months"
                    :label="__('Minimum Loan Term (Months)')"
                    type="number"
                    min="1"
                    required />

                <flux:input
                    wire:model="maximum_loan_term_months"
                    :label="__('Maximum Loan Term (Months)')"
                    type="number"
                    min="1"
                    required />
            </div>

            <!-- Payment Settings -->
            <div class="space-y-4">
                <flux:heading size="lg">Payment Settings</flux:heading>

                <flux:input
                    wire:model="grace_period_days"
                    :label="__('Grace Period (Days)')"
                    type="number"
                    min="0"
                    required />

                <flux:input
                    wire:model="late_payment_fee"
                    :label="__('Late Payment Fee')"
                    type="number"
                    step="0.01"
                    min="0"
                    required />
            </div>

            <!-- Validation Settings -->
            <div class="space-y-4">
                <flux:heading size="lg">Validation Settings</flux:heading>

                <flux:toggle
                    wire:model="allow_multiple_active_loans"
                    :label="__('Allow Multiple Active Loans')"
                    :description="__('Allow users to have more than one active loan at a time')" />

                <flux:toggle
                    wire:model="require_no_defaulted_loans"
                    :label="__('Require No Defaulted Loans')"
                    :description="__('Users with defaulted loans cannot apply for new loans')" />
            </div>

            <!-- Legacy Settings -->
            <div class="space-y-4">
                <flux:heading size="lg">Legacy Settings</flux:heading>

                <flux:toggle
                    wire:model="require_collateral"
                    :label="__('Require Collateral')"
                    :description="__('Require collateral for loan approval')" />

                <flux:input
                    wire:model="collateral_percentage"
                    :label="__('Collateral Percentage (%)')"
                    type="number"
                    step="0.1"
                    min="0"
                    max="100"
                    required />

                <flux:input
                    wire:model="credit_score_threshold"
                    :label="__('Credit Score Threshold')"
                    type="number"
                    required />

                <flux:toggle
                    wire:model="auto_approval_enabled"
                    :label="__('Auto Approval')"
                    :description="__('Automatically approve loans that meet criteria')" />
            </div>

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