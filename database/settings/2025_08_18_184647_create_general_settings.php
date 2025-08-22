<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.site_name', 'My Application');
        $this->migrator->add('general.site_description', '');
        $this->migrator->add('general.contact_email', '');
        $this->migrator->add('general.contact_phone', '');
        $this->migrator->add('general.default_currency', 'NGN');
        $this->migrator->add('general.timezone', 'Africa/Lagos');
        $this->migrator->add('general.maintenance_mode', false);
        $this->migrator->add('general.registration_enabled', true);
    }
};
