<?php

use Livewire\Volt\Component;
use Masmerise\Toaster\Toaster;
use Livewire\Attributes\Layout;
use App\Settings\GeneralSettings;

new #[Layout('components.layouts.admin')] class extends Component {
    public string $site_name = '';
    public string $site_description = '';
    public string $contact_email = '';
    public string $contact_phone = '';
    public string $default_currency = 'NGN';
    public string $timezone = 'Africa/Lagos';
    public bool $maintenance_mode = false;
    public bool $registration_enabled = true;

    public function mount(): void
    {
        $settings = app(GeneralSettings::class);
        $this->site_name = $settings->site_name;
        $this->site_description = $settings->site_description;
        $this->contact_email = $settings->contact_email;
        $this->contact_phone = $settings->contact_phone;
        $this->default_currency = $settings->default_currency;
        $this->timezone = $settings->timezone;
        $this->maintenance_mode = $settings->maintenance_mode;
        $this->registration_enabled = $settings->registration_enabled;
    }

    public function save(): void
    {
        $settings = app(GeneralSettings::class);
        $settings->site_name = $this->site_name;
        $settings->site_description = $this->site_description;
        $settings->contact_email = $this->contact_email;
        $settings->contact_phone = $this->contact_phone;
        $settings->default_currency = $this->default_currency;
        $settings->timezone = $this->timezone;
        $settings->maintenance_mode = $this->maintenance_mode;
        $settings->registration_enabled = $this->registration_enabled;
        $settings->save();

        Toaster::success(__('Settings saved successfully.'));
        $this->dispatch('settings-saved');
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl">General Settings</flux:heading>
    <flux:subheading>Manage your application general settings</flux:subheading>

    <form wire:submit="save" class="mt-6 space-y-6 max-w-2xl">
        <flux:input
            wire:model="site_name"
            label="Site Name"
            type="text"
            required
            autofocus />

        <flux:input
            wire:model="site_description"
            label="Site Description"
            type="text" />

        <flux:input
            wire:model="contact_email"
            label="Contact Email"
            type="email" />

        <flux:input
            wire:model="contact_phone"
            label="Contact Phone"
            type="tel" />

        <flux:select
            wire:model="default_currency"
            label="Default Currency">
            <select.option value="NGN">Naira (NGN)</select.option>
            <select.option value="USD">US Dollar (USD)</select.option>
            <select.option value="EUR">Euro (EUR)</select.option>
            <select.option value="GBP">British Pound (GBP)</select.option>
        </flux:select>

        <flux:select
            wire:model="timezone"
            label="Timezone">
            <select.option value="Africa/Lagos">Africa/Lagos</select.option>
            <select.option value="UTC">UTC</select.option>
            <select.option value="Europe/London">Europe/London</select.option>
            <select.option value="America/New_York">America/New_York</select.option>
        </flux:select>

        <flux:switch
            wire:model="maintenance_mode"
            label="Maintenance Mode"
            description="Enable to put the site in maintenance mode" />

        <flux:switch
            wire:model="registration_enabled"
            label="User Registration"
            description="Allow new user registration" />

        <div class="flex items-center gap-4">
            <flux:button type="submit" variant="primary">
                Save Settings
            </flux:button>

            <x-action-message on="settings-saved">
                Saved.
            </x-action-message>
        </div>
    </form>
</section>