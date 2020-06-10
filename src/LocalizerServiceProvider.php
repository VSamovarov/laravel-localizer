<?php

namespace VSamovarov\LaravelLocalizer;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use VSamovarov\LaravelLocalizer\Localizer;
use VSamovarov\LaravelLocalizer\Macros\RouteMacros;
use VSamovarov\LaravelLocalizer\Middleware\LocalizerMiddleware;

class LocalizerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $router = $this->app->make(Router::class);
        $router->pushMiddlewareToGroup('web', LocalizerMiddleware::class);
        require_once('helpers.php');
    }
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $packageConfigFile = __DIR__ . '/../config/config.php';
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $packageConfigFile => config_path('laravel-localizer.php'),
            ], 'config');
        }

        $this->mergeConfigFrom(
            $packageConfigFile,
            'laravel-localizer'
        );

        $this->app->singleton(Localizer::class, function ($app) {
            return new Localizer($app['config']['laravel-localizer']);
        });
        $this->app->alias(Localizer::class, 'localizer');

        $this->app->singleton(LocalizedRoute::class, function ($app) {
            return new LocalizedRoute($app['localizer'], $app['translator']);
        });
        $this->app->alias(LocalizedRoute::class, 'localizedRoute');

        Route::mixin(new RouteMacros);
    }
}