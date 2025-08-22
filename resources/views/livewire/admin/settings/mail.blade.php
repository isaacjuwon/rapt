<?php

use App\Settings\MailSettings;
use Livewire\Volt\Component;

new class extends Component {
    public string $mail_mailer = 'smtp';
    public string $mail_host = 'smtp.mailtrap.io';
    public int $mail_port = 2525;
    public string $mail_username = '';
    public string $mail_password = '';
    public string $mail_encryption = 'tls';
    public string $mail_from_address = 'hello@example.com';
    public string $mail_from_name = 'My Application';
    public bool $mail_enabled = true;

    public function mount(): void
    {
        $settings = app(MailSettings::class);
        $this->mail_mailer = $settings->mail_mailer;
        $this->mail_host = $settings->mail_host;
        $this->mail_port = $settings->mail_port;
        $this->mail_username = $settings->mail_username;
        $this->mail_password = $settings->mail_password;
        $this->mail_encryption = $settings->mail_encryption;
        $this->mail_from_address = $settings->mail_from_address;
        $this->mail_from_name = $settings->mail_from_name;
        $this->mail_enabled = $settings->mail_enabled;
    }

    public function save(): void
    {
        $settings = app(MailSettings::class);
        $settings->mail_mailer = $this->mail_mailer;
        $settings->mail_host = $this->mail_host;
        $settings->mail_port = $this->mail_port;
        $settings->mail_username = $this->mail_username;
        $settings->mail_password = $this->mail_password;
        $settings->mail_encryption = $this->mail_encryption;
        $settings->mail_from_address = $this->mail_from_address;
        $settings->mail_from_name = $this->mail_from_name;
        $settings->mail_enabled = $this->mail_enabled;
        $settings->save();

        $this->dispatch('settings-saved');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Mail Settings')" :subheading="__('Configure email delivery settings')">
        <form wire:submit="save" class="space-y-6">
            <flux:toggle
                wire:model="mail_enabled"
                :label="__('Enable Mail')"
                :description="__('Enable email delivery functionality')" />

            <flux:select
                wire:model="mail_mailer"
                :label="__('Mail Driver')">
                <flux:option value="smtp">SMTP</flux:option>
                <flux:option value="sendmail">Sendmail</flux:option>
                <flux:option value="mailgun">Mailgun</flux:option>
                <flux:option value="ses">Amazon SES</flux:option>
                <flux:option value="postmark">Postmark</flux:option>
            </flux:select>

            <flux:input
                wire:model="mail_host"
                :label="__('Mail Host')"
                type="text"
                required />

            <flux:input
                wire:model="mail_port"
                :label="__('Mail Port')"
                type="number"
                required />

            <flux:input
                wire:model="mail_username"
                :label="__('Mail Username')"
                type="text" />

            <flux:input
                wire:model="mail_password"
                :label="__('Mail Password')"
                type="password" />

            <flux:select
                wire:model="mail_encryption"
                :label="__('Encryption')">
                <flux:option value="tls">TLS</flux:option>
                <flux:option value="ssl">SSL</flux:option>
                <flux:option value="">None</flux:option>
            </flux:select>

            <flux:input
                wire:model="mail_from_address"
                :label="__('From Address')"
                type="email"
                required />

            <flux:input
                wire:model="mail_from_name"
                :label="__('From Name')"
                type="text"
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