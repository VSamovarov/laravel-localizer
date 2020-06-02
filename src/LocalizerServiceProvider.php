<?php

namespace VSamovarov\LaravelLocalizer;

use Illuminate\Routing\Router;
use VSamovarov\LaravelLocalizer\Localizer;
use VSamovarov\LaravelLocalizer\LocalizerRouter;
use Illuminate\Support\ServiceProvider;
use VSamovarov\LaravelLocalizer\Middleware\DefaultLocaleRedirect;
use VSamovarov\LaravelLocalizer\Middleware\LocalizationApi;
use VSamovarov\LaravelLocalizer\Middleware\LocalizationWeb;

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
        }
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('localizationWeb', LocalizationWeb::class);
        $router->aliasMiddleware('defaultLocaleRedirect', DefaultLocaleRedirect::class);
        $router->aliasMiddleware('LocalizationApi', LocalizationApi::class);

        require_once('helpers.php');
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