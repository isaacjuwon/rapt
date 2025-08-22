<?php

namespace App\Providers;

use App\Settings\GeneralSettings;
use App\Settings\ApiSettings;
use App\Settings\SharesSettings;
use App\Settings\LoanSettings;
use App\Settings\SecuritySettings;
use App\Settings\MailSettings;
use App\Settings\NotificationsSettings;
use Illuminate\Support\ServiceProvider;

class SettingsViewServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share all settings with every view
        $this->shareSettingsWithViews();
    }

    /**
     * Share settings variables with all Blade templates.
     */
    protected function shareSettingsWithViews(): void
    {
        // Share all settings classes as view variables
        view()->share('general', app(GeneralSettings::class));
        view()->share('api', app(ApiSettings::class));
        view()->share('shares', app(SharesSettings::class));
        view()->share('loans', app(LoanSettings::class));
        view()->share('security', app(SecuritySettings::class));
        view()->share('mail', app(MailSettings::class));
        view()->share('notifications', app(NotificationsSettings::class));
    }
}
