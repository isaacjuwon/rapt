<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        /*$this->migrator->add('mail.mail_mailer', 'smtp');
        // $this->migrator->add('mail.mail_host', 'smtp.mailtrap.io');
        $this->migrator->add('mail.mail_port', 2525);
        $this->migrator->addEncrypted('mail.mail_username', '');
        $this->migrator->addEncrypted('mail.mail_password', '');
        $this->migrator->addEncrypted('mail.mail_host', 'smtp.mailtrap.io');
        $this->migrator->add('mail.mail_encryption', 'tls');
        $this->migrator->add('mail.mail_from_address', 'hello@example.com');
        $this->migrator->add('mail.mail_from_name', 'My Application');
        $this->migrator->add('mail.mail_enabled', true); */
    }
};
