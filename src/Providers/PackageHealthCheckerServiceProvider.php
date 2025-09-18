<?php

namespace Itzdevsatvik\PackageHealthChecker\Providers;

use Illuminate\Support\ServiceProvider;

class PackageHealthCheckerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge package configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/packagehealthchecker.php',
            'packagehealthchecker'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/packagehealthchecker.php' => config_path('packagehealthchecker.php'),
        ], 'packagehealthchecker-config');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/packagehealthchecker'),
        ], 'packagehealthchecker-views');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'packagehealthchecker');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Itzdevsatvik\PackageHealthChecker\Console\Commands\CheckPackageHealth::class,
            ]);
        }
    }
}