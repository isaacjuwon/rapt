<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->addEncrypted('api.configurations', [
            'epins_base_url' => 'https://api.epins.com',
            'epins_api_key' => '',
            'paystack_public_key' => '',
            'paystack_secret_key' => '',
            'webhook_url' => '',
        ]);
    }
};
