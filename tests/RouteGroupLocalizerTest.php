<?php

namespace Tests\Feature\Localizer;

use VSamovarov\LaravelLocalizer\Facades\Localizer as LocalizerFacade;
use VSamovarov\LaravelLocalizer\LocalizerServiceProvider;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Routing\Router;
use Mockery as m;
use VSamovarov\LaravelLocalizer\Localizer;
use VSamovarov\LaravelLocalizer\RouteGroupLocalizer;
use Illuminate\Contracts\Translation\Translator as TranslatorContract;

class RouteGroupLocalizerTest extends \Orchestra\Testbench\TestCase
{

    protected $config = [
        'supportedLocales' => [
            'uk'          => ['name' => 'Ukrainian', 'script' => 'Cyrl', 'native' => 'українська', 'regional' => 'uk_UA'],
            'en'          => ['name' => 'English', 'script' => 'Latn', 'native' => 'English', 'regional' => 'en_GB'],
            'ru'          => ['name' => 'Russian', 'script' => 'Cyrl', 'native' => 'русский', 'regional' => 'ru_RU'],
        ],
        'hideDefaultLocaleInURL' => false,
        'translationFileName' => 'routes',
        'localizerNamePrefix' => 'localizer-',
    ];

    protected $routesTranslate = [
        'ru' => [
            "about" => "о-нас",
            "article" => "статья",
            "home" => "главная-страница",
        ],
        'uk' => [
            "about" => "про-нас",
            "article" => "стаття",
            "home" => "головна-сторінка",
        ],
        'en' => [
            "about" => "about",
            "article" => "article",
        ],
    ];
    protected $router;
    protected function getPackageProviders($app)
    {
        return [LocalizerServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'localizer' => LocalizerFacade::class
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        app('config')['laravel-localizer'] = $this->config;
    }

    public function testRouteGroupLocalizerHideDefaultLocaleInURL()
    {
        $config = app('config')['laravel-localizer'];
        $config['hideDefaultLocaleInURL'] = true;
        $localizer = new Localizer($config);
        $router = new Router(m::mock(Dispatcher::class), app());
        $translator = new Translator($this->routesTranslate);
        $groupLocalizer = new RouteGroupLocalizer($localizer, $translator, $router, app('request'));


        $groupLocalizer->routeGroupLocalizer([], function () use ($router) {
            $router->get('/')->name('home');
            $router->get('lorem/boo')->name('to.too');
            $router->get('about/home/article')->name('article');
        });

        $names = collect($router->getRoutes())->map(function ($route) {
            return $route->getName();
        })->toArray();


        $namesEquals = [
            "localizer-en.article",
            "localizer-en.home",
            "localizer-en.to.too",
            "localizer-ru.article",
            "localizer-ru.home",
            "localizer-ru.to.too",
            "localizer-uk.article",
            "localizer-uk.home",
            "localizer-uk.to.too",
        ];
        sort($namesEquals);
        sort($names);
        $this->assertEquals($namesEquals, $names);

        $urls = collect($router->getRoutes())->map(function ($route) {
            return $route->uri();
        })->toArray();
        $urlsEquals = [
            "",
            "en",
            "en/about/home/article",
            "en/lorem/boo",
            "lorem/boo",
            "ru",
            "ru/lorem/boo",
            "ru/о-нас/главная-страница/статья",
            "про-нас/головна-сторінка/стаття",
        ];
        sort($urlsEquals);
        sort($urls);
        $this->assertEquals($urlsEquals, $urls);
    }
    public function testRouteGroupLocalizerShowDefaultLocaleInURL()
    {
        $config = app('config')['laravel-localizer'];
        $config['hideDefaultLocaleInURL'] = false;
        $localizer = new Localizer($config);
        $router = new Router(m::mock(Dispatcher::class), app());
        $translator = new Translator($this->routesTranslate);
        $groupLocalizer = new RouteGroupLocalizer($localizer, $translator, $router, app('request'));


        $groupLocalizer->routeGroupLocalizer([], function () use ($router) {
            $router->get('/')->name('home');
            $router->get('lorem/boo')->name('to.too');
            $router->get('about/home/article')->name('article');
        });

        $names = collect($router->getRoutes())->map(function ($route) {
            return $route->getName();
        })->toArray();

        $namesEquals = [
            "localizer-uk.home",
            "localizer-uk.to.too",
            "localizer-uk.article",
            "localizer-en.home",
            "localizer-en.to.too",
            "localizer-en.article",
            "localizer-ru.home",
            "localizer-ru.to.too",
            "localizer-ru.article",
            ""
        ];
        sort($namesEquals);
        sort($names);
        $this->assertEquals($namesEquals, $names);

        $urls = collect($router->getRoutes())->map(function ($route) {
            return $route->uri();
        })->toArray();
        $urlsEquals = [
            "uk",
            "uk/lorem/boo",
            "uk/про-нас/головна-сторінка/стаття",
            "en",
            "en/lorem/boo",
            "en/about/home/article",
            "ru",
            "ru/lorem/boo",
            "ru/о-нас/главная-страница/статья",
            "",
        ];
        sort($urlsEquals);
        sort($urls);
        $this->assertEquals($urlsEquals, $urls);
    }
    public function testRouteTranslateUri()
    {
        $translator = new Translator($this->routesTranslate);
        $groupLocalizer = new RouteGroupLocalizer(app('localizer'), $translator, app('router'), app('request'));

        $this->assertEquals(
            $groupLocalizer->translateUri('about/home/article', 'ru', 'route', $translator),
            'о-нас/главная-страница/статья'
        );

        $this->assertEquals(
            $groupLocalizer->translateUri('/about/home/article', 'uk', 'route', $translator),
            '/про-нас/головна-сторінка/стаття'
        );

        $this->assertEquals(
            $groupLocalizer->translateUri('article', 'uk', 'route', $translator),
            'стаття'
        );
        $this->assertEquals(
            $groupLocalizer->translateUri('article/bla-bla/{id}', 'uk', 'route', $translator),
            'стаття/bla-bla/{id}'
        );
    }
}

class Translator implements TranslatorContract
{

    private $loader;

    public function __construct(array $loader = [])
    {
        $this->loader = $loader;
    }

    public function get($key, array $replace = [], $locale = null, $fallback = true)
    {

        $segment = $this->parseKey($key);

        return $this->loader[$locale][$segment] ?? $key;
    }


    public function choice($key, $number, array $replace = [], $locale = null)
    {
    }

    public function getLocale()
    {
    }

    public function setLocale($locale)
    {
    }

    private function parseKey($key)
    {
        $segments = explode('.', $key);
        return $segments[1];
    }
}