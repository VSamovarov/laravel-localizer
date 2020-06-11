<?php

namespace Tests\Feature\Localization;

use Tests\TestCase;
use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Translation\Translator as TranslatorContract;
use VSamovarov\LaravelLocalizer\Localizer;
use VSamovarov\LaravelLocalizer\Exceptions\ConfigLocalizerNotDefined;
use VSamovarov\LaravelLocalizer\Exceptions\TranslateRouteException;
use VSamovarov\LaravelLocalizer\Exceptions\UnsupportedLocaleException;

class LocalizationTest extends TestCase
{

    protected $supportedLocales = [
        'uk' => ['name' => 'Ukrainian', 'script' => 'Cyrl', 'native' => 'українська', 'regional' => 'uk_UA'],
        'ru' => ['name' => 'Russian', 'script' => 'Cyrl', 'native' => 'русский', 'regional' => 'ru_RU'],
        'en' => ['name' => 'English', 'script' => 'Latn', 'native' => 'English', 'regional' => 'en_GB'],
    ];
    protected $routesTranslate = [
        'ru' => [
            "about" => "о-нас",
            "about/article/{id}" => "о-нас/статья/{id}",
            "invalid-structure" => "/{id}/плохая-структура",
        ],
        'uk' => [
            "about" => "про-нас",
            "about/article/{id}" => "про-нас/стаття/{id}",
            "invalid-structure" => "/{id}/погана-структура",
        ],
        'en' => [
            "about" => "about",
            "about/article/{id}" => "about/article/{id}",
            "invalid-structure" => "/invalid-structure",
        ],
    ];
    protected $validLocale;
    protected $invalidLocale = 'invalid-locale';
    protected $defaultLocale;
    protected $testUrl = 'https://test.com';


    protected function setUp(): void
    {
        parent::setUp();
        $this->defaultLocale = array_keys($this->supportedLocales)[0];
        $this->validLocale = array_keys($this->supportedLocales)[1];
    }

    public function testSetSupportedLocales()
    {

        $config = new Config([
            'app' => [
                'locale' => $this->defaultLocale,
            ],
            'laravel-localizer' => []
        ]);
        $this->expectException(ConfigLocalizerNotDefined::class);
        $localizer = new Localizer($config, new Translator);
    }

    public function testSetDefaultLocale()
    {
        $config = new Config([
            'app' => [
                'locale' => $this->invalidLocale,
            ],
            'laravel-localizer' => [
                'supportedLocales' => $this->supportedLocales
            ]
        ]);

        $this->expectException(UnsupportedLocaleException::class);
        new Localizer($config, new Translator);
    }

    public function testIsSupportedLocale()
    {
        $localizer = new Localizer($this->createConfig(), new Translator);
        $this->assertTrue($localizer->isSupportedLocale($this->validLocale));
        $this->assertFalse($localizer->isSupportedLocale($this->invalidLocale));
    }

    public function testCompareStrings()
    {
        $localizer = new Localizer($this->createConfig(), new Translator);

        $this->assertTrue($localizer->compareRouteParameterStructure('/eee/hhh/', '/ddd/ccc/'));
        $this->assertTrue($localizer->compareRouteParameterStructure('eee/hhh', 'ddd/ccc'));
        $this->assertTrue($localizer->compareRouteParameterStructure('eee/{$id}/hhh', 'ddd/{$id}/ccc'));
        $this->assertTrue($localizer->compareRouteParameterStructure('eee/{uid}/{id?}', 'ddd/{uid}/{id?}'));
        $this->assertTrue($localizer->compareRouteParameterStructure('eee/{post:slug}', 'ddd/{post:slug}'));

        $this->assertFalse($localizer->compareRouteParameterStructure('/', '/ddd'));
        $this->assertFalse($localizer->compareRouteParameterStructure('/eee', '/'));
        $this->assertFalse($localizer->compareRouteParameterStructure('/eee', 'ddd/ccc'));
        $this->assertFalse($localizer->compareRouteParameterStructure('/eee/hhh', 'ddd/ccc'));
        $this->assertFalse($localizer->compareRouteParameterStructure('eee/{article}/hhh/{id}', 'eee/hhh/{id}'));
        $this->assertFalse($localizer->compareRouteParameterStructure('eee/{uid}/{id}', 'ddd/{bbb}/{id}'));
        $this->assertFalse($localizer->compareRouteParameterStructure('eee/hhh/{id}', 'ddd/ccc/{id?}'));
        $this->assertFalse($localizer->compareRouteParameterStructure('eee/{post:slug}', 'ddd/{post-ddd:slug}'));
    }

    public function testTransRoute()
    {
        $localizer = new Localizer(
            $this->createConfig(),
            new Translator($this->routesTranslate)
        );

        $lang = 'ru';
        $localizer->setLocale($lang);

        $this->assertEquals($localizer->transRoute('about/article/{id}'), $this->routesTranslate[$lang]['about/article/{id}']);
        $this->assertEquals($localizer->transRoute('bla-bla'), 'bla-bla');

        $lang = 'uk';
        $localizer->setLocale($lang);
        $this->assertEquals($localizer->transRoute('about'), $this->routesTranslate[$lang]['about']);
        $this->assertEquals($localizer->transRoute('bla-bla'), 'bla-bla');

        $this->expectException(TranslateRouteException::class);
        $localizer->transRoute('invalid-structure');
    }

    /**
     * Create a new config repository instance.
     *
     * @return \Illuminate\Config\Repository
     */
    protected function createConfig()
    {
        return new Config([
            'app' => [
                'locale' => $this->defaultLocale,
            ],
            'laravel-localizer' => [
                'supportedLocales' => $this->supportedLocales
            ]
        ]);
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
        $key = $this->parseKey($key);
        return $this->loader[$locale][$key] ?? null;
    }

    public function has($key, $locale = null, $fallback = true)
    {
        return !empty($this->get($key, [], $locale, $fallback));
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
