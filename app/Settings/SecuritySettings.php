<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SecuritySettings extends Settings
{
    public bool $two_factor_enabled = true;
    public int $max_login_attempts = 5;
    public int $lockout_duration = 15; // minutes
    public bool $session_timeout_enabled = true;
    public int $session_timeout_minutes = 30;
    public bool $password_strength_required = true;
    public int $password_min_length = 8;
    public bool $require_special_characters = true;
    public bool $suspicious_activity_monitoring = true;

    public static function group(): string
    {
        return 'security';
    }

    public static function encrypted(): array
    {
        return ['max_login_attempts', 'lockout_duration'];
    }
}
