<?php

namespace App\Providers;

use Livewire\Livewire;
use Illuminate\Support\Facades\Route;
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

        $this->configureLoggingChannel();
        
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
}
