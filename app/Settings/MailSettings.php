<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class MailSettings extends Settings
{
    public string $mail_mailer = 'smtp';
    public string $mail_host = 'smtp.mailtrap.io';
    public int $mail_port = 2525;
    public string $mail_username = '';
    public string $mail_password = '';
    public string $mail_encryption = 'tls';
    public string $mail_from_address = 'hello@example.com';
    public string $mail_from_name = 'My Application';
    public bool $mail_enabled = true;

    public static function group(): string
    {
        return 'mail';
    }

    public static function encrypted(): array
    {
        return [
            'mail_password',
            'mail_username',
            'mail_host'
        ];
    }
}
