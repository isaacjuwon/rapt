<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class NotificationsSettings extends Settings
{
    public bool $email_notifications_enabled = true;
    public bool $sms_notifications_enabled = false;
    public bool $push_notifications_enabled = true;
    public bool $database_notifications_enabled = true;

    public static function group(): string
    {
        return 'notifications';
    }
}
