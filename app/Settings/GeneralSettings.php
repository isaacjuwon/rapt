<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name = 'My Application';
    public string $site_description = '';
    public string $contact_email = '';
    public string $contact_phone = '';
    public string $default_currency = 'NGN';
    public string $timezone = 'Africa/Lagos';
    public bool $maintenance_mode = false;
    public bool $registration_enabled = true;
    public ?string $site_logo;
    public ?string $favicon;

    public static function group(): string
    {
        return 'general';
    }
}
