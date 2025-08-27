<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Number::useCurrency('NGN');

        $this->configureLoggingChannel(); // Keep this

        // Configure asset URLs for subdirectory deployment
        $this->configureAssetUrls();

        // Implicitly grant "Super Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user()->can() and @can()
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        // --- Subdomain resolver setup ---
        /* Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/livewire/update', $handle)
                ->middleware(['web', 'sprout.tenanted']); // key part
        });

        // --- OR Path-based resolver setup ---
        // If you're using Sprout's path resolver, you need to include the path param.
        // Adjust {tenants_path} to match your Sprout config.
        /**
        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/{tenants_path}/livewire/update', $handle)
                ->middleware(['web', 'sprout.tenanted']);
        });
        */
    }

    protected function configureLoggingChannel(): void
    {
        $config = $this->app->get('config');
        $logifyChannelConfig = $config->get('logify.channel');

        if ($logifyChannelConfig) {
            $config->set('logging.channels.' . $logifyChannelConfig['name'], $logifyChannelConfig);
        }
    }

    protected function configureAssetUrls(): void
    {
        // Force asset URLs to use HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Set asset root for subdirectory deployment
        $appUrl = config('app.url');
        if ($appUrl && $appUrl !== 'http://localhost') {
            $parsedUrl = parse_url($appUrl);
            
            // If there's a path component in APP_URL, use it as asset root
            if (isset($parsedUrl['path']) && $parsedUrl['path'] !== '/') {
                $assetRoot = rtrim($parsedUrl['path'], '/');
                $this->app['url']->setAssetRoot($assetRoot);
            }
        }
    }
}