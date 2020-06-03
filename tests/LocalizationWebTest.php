<?php

namespace Tests\Feature\Localization;

use Tests\TestCase;
use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Http\Request;
use VSamovarov\LaravelLocalizer\Localizer;
use VSamovarov\LaravelLocalizer\Middleware\LocalizationWeb;


class LocalizationWebTest extends TestCase
{
    protected $supportedLocales = [
        'uk' => ['name' => 'Ukrainian', 'script' => 'Cyrl', 'native' => 'українська', 'regional' => 'uk_UA'],
        'en' => ['name' => 'English', 'script' => 'Latn', 'native' => 'English', 'regional' => 'en_GB'],
    ];
    protected $defaultLocale = 'uk';
    protected $validLocale = 'en';

    public function testGetUrlWithoutLocal()
    {
        $localizer = $this->getLocalization();
        $middleware = new LocalizationWeb($localizer);

        $request = Request::create("http://example.dev/{$this->validLocale}/bla/bla", 'GET');
        $middleware->handle($request, function () {
        });
        $this->assertEquals($this->validLocale, $localizer->getLocale());

        $request = Request::create("http://example.dev/{$this->validLocale}", 'GET');
        $middleware->handle($request, function () {
        });
        $this->assertEquals($this->validLocale, $localizer->getLocale());

        $request = Request::create("http://example.dev/{$this->defaultLocale}/bla/bla", 'GET');
        $middleware->handle($request, function () {
        });
        $this->assertEquals($this->defaultLocale, $localizer->getLocale());

        $request = Request::create("http://example.dev/", 'GET');
        $middleware->handle($request, function () {
        });
        $this->assertEquals($this->defaultLocale, $localizer->getLocale());

        $request = Request::create("http://example.dev/blabla-bla/bla", 'GET');
        $middleware->handle($request, function () {
        });
        $this->assertEquals($this->defaultLocale, $localizer->getLocale());
    }

    protected function getLocalization()
    {
        return new Localizer(
            new Config([
                'app' => [
                    'locale' => $this->defaultLocale,
                ],
                'laravel-localizer' => [
                    'supportedLocales' => $this->supportedLocales,
                ]
            ]),
            $this->createMock(Translator::class)
        );
    }
}