<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('security.two_factor_enabled', true);
        $this->migrator->addEncrypted('security.max_login_attempts', 5);
        $this->migrator->addEncrypted('security.lockout_duration', 15);
        $this->migrator->add('security.session_timeout_enabled', true);
        $this->migrator->add('security.session_timeout_minutes', 30);
        $this->migrator->add('security.password_strength_required', true);
        $this->migrator->add('security.password_min_length', 8);
        $this->migrator->add('security.require_special_characters', true);
        $this->migrator->add('security.suspicious_activity_monitoring', true);
    }
};
