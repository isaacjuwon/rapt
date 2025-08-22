<?php

use App\Settings\SecuritySettings;
use Livewire\Volt\Component;

new class extends Component {
    public bool $two_factor_enabled = true;
    public int $max_login_attempts = 5;
    public int $lockout_duration = 15;
    public bool $session_timeout_enabled = true;
    public int $session_timeout_minutes = 30;
    public bool $password_strength_required = true;
    public int $password_min_length = 8;
    public bool $require_special_characters = true;
    public bool $suspicious_activity_monitoring = true;

    public function mount(): void
    {
        $settings = app(SecuritySettings::class);
        $this->two_factor_enabled = $settings->two_factor_enabled;
        $this->max_login_attempts = $settings->max_login_attempts;
        $this->lockout_duration = $settings->lockout_duration;
        $this->session_timeout_enabled = $settings->session_timeout_enabled;
        $this->session_timeout_minutes = $settings->session_timeout_minutes;
        $this->password_strength_required = $settings->password_strength_required;
        $this->password_min_length = $settings->password_min_length;
        $this->require_special_characters = $settings->require_special_characters;
        $this->suspicious_activity_monitoring = $settings->suspicious_activity_monitoring;
    }

    public function save(): void
    {
        $settings = app(SecuritySettings::class);
        $settings->two_factor_enabled = $this->two_factor_enabled;
        $settings->max_login_attempts = $this->max_login_attempts;
        $settings->lockout_duration = $this->lockout_duration;
        $settings->session_timeout_enabled = $this->session_timeout_enabled;
        $settings->session_timeout_minutes = $this->session_timeout_minutes;
        $settings->password_strength_required = $this->password_strength_required;
        $settings->password_min_length = $this->password_min_length;
        $settings->require_special_characters = $this->require_special_characters;
        $settings->suspicious_activity_monitoring = $this->suspicious_activity_monitoring;
        $settings->save();

        $this->dispatch('settings-saved');
    }
}; ?>

<section class="w-full">
    <flux:heading size="xl">Security Settings</flux:heading>
    <flux:subheading>Manage application security configuration</flux:subheading>

    <form wire:submit="save" class="mt-6 space-y-6 max-w-2xl">
        <flux:toggle
            wire:model="two_factor_enabled"
            label="Enable Two Factor Authentication"
            description="Allow users to enable 2FA on their accounts" />

        <flux:input
            wire:model="max_login_attempts"
            label="Maximum Login Attempts"
            type="number"
            required />

        <flux:input
            wire:model="lockout_duration"
            label="Lockout Duration (Minutes)"
            type="number"
            required />

        <flux:toggle
            wire:model="session_timeout_enabled"
            label="Enable Session Timeout"
            description="Automatically log out inactive users" />

        <flux:input
            wire:model="session_timeout_minutes"
            label="Session Timeout (Minutes)"
            type="number"
            required />

        <flux:toggle
            wire:model="password_strength_required"
            label="Require Strong Passwords"
            description="Enforce password strength requirements" />

        <flux:input
            wire:model="password_min_length"
            label="Minimum Password Length"
            type="number"
            required />

        <flux:toggle
            wire:model="require_special_characters"
            label="Require Special Characters"
            description="Passwords must contain special characters" />

        <flux:toggle
            wire:model="suspicious_activity_monitoring"
            label="Suspicious Activity Monitoring"
            description="Monitor and alert on suspicious account activity" />

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