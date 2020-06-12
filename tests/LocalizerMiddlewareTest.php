<?php

namespace Tests\Feature\Localization;

use Illuminate\Http\Request;
use VSamovarov\LaravelLocalizer\Localizer;
use VSamovarov\LaravelLocalizer\LocalizerServiceProvider;
use VSamovarov\LaravelLocalizer\Middleware\LocalizationWeb;
use VSamovarov\LaravelLocalizer\Middleware\LocalizerMiddleware;

class LocalizerMiddlewareTest extends \Orchestra\Testbench\TestCase
{

    protected $config = [
        'supportedLocales' => [
            'uk'          => ['name' => 'Ukrainian', 'script' => 'Cyrl', 'native' => 'українська', 'regional' => 'uk_UA'],
            'en'          => ['name' => 'English', 'script' => 'Latn', 'native' => 'English', 'regional' => 'en_GB'],
            'ru'          => ['name' => 'Russian', 'script' => 'Cyrl', 'native' => 'русский', 'regional' => 'ru_RU'],
        ],
        'hideDefaultLocaleInURL' => true,
        'translationFileName' => 'routes',
        'localizerNamePrefix' => 'localizer-',

    ];
    protected function getPackageProviders($app)
    {
        return [LocalizerServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();
        app('config')['laravel-localizer'] = $this->config;
    }

    public function testSetLocale()
    {

        $localizer = new Localizer(app('config')['laravel-localizer']);
        $middleware = new LocalizerMiddleware($localizer);

        $request = Request::create("http://example.dev/bla/bla", 'GET');

        $middleware->handle($request, function () {
        });
        $this->assertEquals('uk', $localizer->getLocale());

        $request = Request::create("http://example.dev/ru/bla/bla", 'GET');

        $middleware->handle($request, function () {
        });
        $this->assertEquals('ru', $localizer->getLocale());

        $request = Request::create("http://example.dev/uk/bla/bla", 'GET');
        $middleware->handle($request, function () {
        });
        $this->assertEquals('uk', $localizer->getLocale());
    }
}
