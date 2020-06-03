<?php

namespace VSamovarov\LaravelLocalizer;

use VSamovarov\LaravelLocalizer\Localizer;
use VSamovarov\LaravelLocalizer\Middleware\LocalizationWeb;
use VSamovarov\LaravelLocalizer\Controllers\Dummy;
use Illuminate\Support\Facades\Route;



class LocalizerRouter
{
    protected $localizer;
    protected $defaultLocale;
    protected $supportedLocales;
    protected $hideDefaultLocaleInURL;

    public function __construct(Localizer $localizer)
    {
        $this->supportedLocales = array_keys($localizer->getSupportedLocales());
        $this->defaultLocale = $localizer->getDefaultLocale();
        $this->hideDefaultLocaleInURL = $localizer->isHideDefaultLocaleInURL();
    }

    /**
     * Создаем группу роутеров для каждой локали
     *
     * @param \Closure $routes
     * @return void
     */
    public function group(\Closure $routes)
    {
        foreach ($this->supportedLocales as $locale) {
            $attributes = [
                'as' => "{$locale}.",
                'middleware' => 'VSamovarov\LaravelLocalizer\Middleware\LocalizationWeb::class'
            ];
            if ($this->defaultLocale === $locale && $this->hideDefaultLocaleInURL) {
                $this->getRoutDefaultLocalRedirect();
            } else {
                $attributes['prefix'] = $locale;
            }
            Route::group($attributes, $routes);
        }
    }

    /**
     * Формируем роутеры с редиректом  для локали по умолчанию
     *
     * @return void
     */
    protected function getRoutDefaultLocalRedirect()
    {
        if ($this->hideDefaultLocaleInURL) {
            Route::group(
                [
                    'prefix' => $this->defaultLocale,
                    'middleware' => 'VSamovarov\LaravelLocalizer\Middleware\DefaultLocaleRedirect::class'
                ],
                function () {
                    Route::any("/", [Dummy::class, 'abort']);
                    /**
                     * если будет путь по типу ru/ru/
                     * то его не редиректим, чтобы не заспамили -  /ru/ru/ru/ru...
                     */
                    Route::any("{any}", [Dummy::class, 'abort'])->where('any', "^(?!{$this->defaultLocale}/).*");
                }
            );
        }
    }
}