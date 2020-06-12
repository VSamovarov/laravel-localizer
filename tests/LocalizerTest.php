<?php

namespace Tests\Feature\Localizer;

use VSamovarov\LaravelLocalizer\Exceptions\ConfigLocalizerNotDefined;
use VSamovarov\LaravelLocalizer\Facades\Localizer as LocalizerFacade;
use VSamovarov\LaravelLocalizer\LocalizerServiceProvider;

use VSamovarov\LaravelLocalizer\Localizer;

class LocalizationTest extends \Orchestra\Testbench\TestCase
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
    public function testExceptionLocalizerConstructor()
    {
        $this->expectException(ConfigLocalizerNotDefined::class);
        new Localizer([]);
    }
    public function testLocalizerConstructor()
    {
        $localizer = new Localizer($this->config);
        //Язык по умолчанию - первый в массиве 'supportedLocales'
        $this->assertEquals($localizer->getDefaultLocale(), 'uk');
    }
    public function testSetSupportedLocales()
    {
        $config = $this->config;

        //Показываем дефолтную локаль в урле
        $config['hideDefaultLocaleInURL'] = false;
        $localizer = new Localizer($config);
        $localizer->setSupportedLocales($config['supportedLocales']);
        $supportedLocales = $localizer->getSupportedLocales();
        $this->assertEquals(array_column($supportedLocales, 'slug'), array_keys($config['supportedLocales']));
        $this->assertEquals(array_column($supportedLocales, 'prefix'), array_keys($config['supportedLocales']));

        //Скрываем дефолтную локаль в урле
        $config['hideDefaultLocaleInURL'] = true;
        $localizer = new Localizer($config);
        $supportedLocales = $localizer->getSupportedLocales();
        $this->assertEquals(array_column($supportedLocales, 'slug'), array_keys($config['supportedLocales']));
        $prefixes = array_keys($config['supportedLocales']);
        $prefixes[0] = '';
        $this->assertEquals(array_column($supportedLocales, 'prefix'), $prefixes);
    }
    public function testIsSupportedLocale()
    {
        $localizer = new Localizer($this->config);

        $this->assertTrue($localizer->isSupportedLocale('en'));
        $this->assertFalse($localizer->isSupportedLocale('fail'));
    }

    public function testSetLocale()
    {
        $localizer = new Localizer($this->config);
        $localizer->setLocale('fail');
        $this->assertEquals(app()->getLocale(), $localizer->getDefaultLocale());

        $localizer->setLocale('en');
        $this->assertEquals(app()->getLocale(), 'en');
    }
}
