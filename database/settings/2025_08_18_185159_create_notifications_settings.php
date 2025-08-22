<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('notifications.email_notifications_enabled', true);
        $this->migrator->add('notifications.sms_notifications_enabled', false);
        $this->migrator->add('notifications.push_notifications_enabled', true);
        $this->migrator->add('notifications.database_notifications_enabled', true);
    }
};
