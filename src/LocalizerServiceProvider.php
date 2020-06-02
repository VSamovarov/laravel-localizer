<?php

namespace VSamovarov\LaravelLocalizer;

use VSamovarov\LaravelLocalizer\Localizer;
use VSamovarov\LaravelLocalizer\LocalizerRouter;
use Illuminate\Support\ServiceProvider;

class LocalizerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('laravel-localizer.php'),
            ], 'config');

            require_once(dirname(__FILE__, 2) . '/helpers.php');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {

        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'laravel-localizer');

        $this->app->singleton('localizer', function ($app) {
            return new Localizer($app['config'], $app['translator']);
        });

        $this->app->singleton('localizerRouter', function ($app) {
            return new LocalizerRouter($app['localizer']);
        });
    }
}