<?php

use App\Settings\NotificationsSettings;
use Livewire\Volt\Component;

new class extends Component {
    public bool $email_notifications_enabled = true;
    public bool $sms_notifications_enabled = false;
    public bool $push_notifications_enabled = true;
    public bool $database_notifications_enabled = true;

    public function mount(): void
    {
        $settings = app(NotificationsSettings::class);
        $this->email_notifications_enabled = $settings->email_notifications_enabled;
        $this->sms_notifications_enabled = $settings->sms_notifications_enabled;
        $this->push_notifications_enabled = $settings->push_notifications_enabled;
        $this->database_notifications_enabled = $settings->database_notifications_enabled;
    }

    public function save(): void
    {
        $settings = app(NotificationsSettings::class);
        $settings->email_notifications_enabled = $this->email_notifications_enabled;
        $settings->sms_notifications_enabled = $this->sms_notifications_enabled;
        $settings->push_notifications_enabled = $this->push_notifications_enabled;
        $settings->database_notifications_enabled = $this->database_notifications_enabled;
        $settings->save();

        $this->dispatch('settings-saved');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Notifications Settings')" :subheading="__('Manage notification preferences and channels')">
        <form wire:submit="save" class="space-y-6">
            <flux:toggle
                wire:model="email_notifications_enabled"
                :label="__('Email Notifications')"
                :description="__('Send notifications via email')" />

            <flux:toggle
                wire:model="sms_notifications_enabled"
                :label="__('SMS Notifications')"
                :description="__('Send notifications via SMS')" />

            <flux:toggle
                wire:model="push_notifications_enabled"
                :label="__('Push Notifications')"
                :description="__('Send push notifications to user devices')" />

            <flux:toggle
                wire:model="database_notifications_enabled"
                :label="__('Database Notifications')"
                :description="__('Store notifications in the database for in-app display')" />

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