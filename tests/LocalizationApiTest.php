<?php

namespace Tests\Feature\Localization;

use Tests\TestCase;
use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Http\Request;
use VSamovarov\LaravelLocalizer\Localizer;
use VSamovarov\LaravelLocalizer\Middleware\LocalizationApi;


class LocalizationApiTest extends TestCase
{
    protected $supportedLocales = [
        'uk' => ['name' => 'Ukrainian', 'script' => 'Cyrl', 'native' => 'українська', 'regional' => 'uk_UA'],
        'en' => ['name' => 'English', 'script' => 'Latn', 'native' => 'English', 'regional' => 'en_GB'],
    ];
    protected $defaultLocale = 'uk';
    protected $validLocale = 'en';

    public function testMiddlewareApi()
    {
        $localizer = new Localizer(
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

        $middleware = new LocalizationApi($localizer);

        // Нет Get параметра lang
        $request = Request::create('/', 'GET');
        $middleware->handle($request, function ($request) use ($localizer) {
            $this->assertEquals($localizer->getLocale(), $request->lang);
        });
        $this->assertEquals($localizer->getLocale(), $this->defaultLocale);

        // Валидный Get параметр lang
        $request->merge([
            'lang' => $this->validLocale
        ]);
        $middleware->handle($request, function ($request) {
            $this->assertEquals($this->validLocale, $request->lang);
        });
        $this->assertEquals($localizer->getLocale(), $this->validLocale);

        // Если в 'lang' не корректное значение
        $request->merge([
            'lang' => 'invalid-locale'
        ]);
        $response = $middleware->handle($request, function () {
        });
        $this->assertEquals($response->status(), 404);
    }
}