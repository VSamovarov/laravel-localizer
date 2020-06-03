<?php

namespace VSamovarov\LaravelLocalizer;

use VSamovarov\LaravelLocalizer\Exceptions\ConfigLocalizerNotDefined;
use VSamovarov\LaravelLocalizer\Exceptions\TranslateRouteException;
use VSamovarov\LaravelLocalizer\Exceptions\UnsupportedLocaleException;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Translation\Translator;



class Localizer
{
    /**
     * Имя файла для переводов роутеров
     */
    const NAME_LANGUAGE_FILE = 'routes';

    /**
     * Illuminate request class.
     *
     * @var Illuminate\Foundation\Application
     */
    private $app;

    /**
     * Illuminate translator class.
     *
     * @var \Illuminate\Translation\Translator
     */
    protected $translator;

    /**
     * Supported Locales
     *
     * @var array
     */
    protected $supportedLocales;

    /**
     * default Locales
     *
     * @var string
     */

    protected $defaultLocale;

    /**
     * If `hideDefaultLocaleInURL` is true, then a url without locale
     * is identical with the same url with default locale.
     *
     * @var bool
     */
    protected $hideDefaultLocaleInURL = true;

    public function __construct(Repository $config, Translator $translator)
    {
        $this->translator = $translator;

        $this->setSupportedLocales($config['laravel-localizer.supportedLocales']);
        $this->setDefaultLocale($config['app.locale']);
        if ($config['laravel-localizer.hideDefaultLocaleInURL'] === false || $config['laravel-localizer.hideDefaultLocaleInURL'] === 0) {
            $this->hideDefaultLocaleInURL = false;
        }
    }

    /**
     * Set supported Locales
     *
     * @return array
     */

    public function setSupportedLocales($supportedLocales): void
    {
        if (empty($supportedLocales) || !is_array($supportedLocales)) {
            throw new ConfigLocalizerNotDefined('Не задана конфигурация');
        }
        $this->supportedLocales = $supportedLocales;
    }

    /**
     * Get supported Locales
     *
     * @return array
     */
    public function getSupportedLocales()
    {
        return $this->supportedLocales;
    }
    /**
     * is Supported Locale
     *
     * @param string $locale
     * @return boolean
     */
    public function isSupportedLocale($locale): bool
    {
        return isset($this->getSupportedLocales()[strval($locale)]);
    }

    /**
     * Устанавливаем локаль по умолчанию.
     *
     * @return string
     */
    public function setDefaultLocale(string $locale): void
    {
        if (!$this->isSupportedLocale($locale)) {
            throw new UnsupportedLocaleException('Laravel default locale is not in the supportedLocales array');
        }
        $this->defaultLocale = $locale;
    }
    /**
     * Получаем локаль по умолчанию
     *
     * @return string
     */

    public function getDefaultLocale(): string
    {
        return $this->defaultLocale;
    }

    /**
     * Установка локали
     *
     * @param string $locale
     * @return void
     */
    public function setLocale($locale)
    {
        if ($this->isSupportedLocale($locale)) {
            app()->setLocale($locale);
        }
    }

    /**
     * Получить локаль
     *
     * @param string $lang
     * @return string
     */
    public function getLocale(): string
    {
        return app()->getLocale();
    }

    /**
     * Перевод роутера
     *
     * @param string $key
     * @param string $group
     * @return string
     */
    public function transRoute(string $key, string $group = self::NAME_LANGUAGE_FILE): string
    {
        $string = $key;
        $locale = $this->getLocale();
        if ($this->translator->has("{$group}.{$key}", $locale, false)) {
            $string = $this->translator->get("{$group}.{$key}", [], $locale, false);
            if ($this->compareRouteParameterStructure($key, $string) === false) {
                throw new TranslateRouteException(
                    'Bad structure of the translated router.'
                        . ' Key: ' . $key
                        . ', translation: ' . $string
                        . ', local: ' . $locale
                );
            }
        }
        return $string;
    }



    /**
     * Generate the URL to a named route.
     *
     * @param  string  $name
     * @param  mixed  $parameters
     * @param  string  $local
     * @param  bool  $absolute
     * @return string
     */

    public function localeRoute(string $name, $parameters = [], $local = '', $absolute = true): string
    {
        if (empty($local)) {
            $local = $this->getLocale();
        } else {
            if (!$this->isSupportedLocale($local)) {
                throw new UnsupportedLocaleException('Unsupported Locale');
            }
        }
        return route("{$local}.{$name}", $parameters, $absolute);
    }

    /**
     * Проверяем совпадает ли структура роутера с локализированным роутером
     *
     * @param string $routeParameter
     * @param string $transString
     * @return boolean
     */
    public function compareRouteParameterStructure(string $routeParameter, string $transString): bool
    {
        $routeParameter = explode('/', $routeParameter);
        $transString = explode('/', $transString);
        if (count($routeParameter) !== count($transString)) return false;

        foreach ($routeParameter as $pos => $segment) {
            if (strpos($segment, '{') !== false) {
                //Есть подстановка, Должны быть равны
                if ($transString[$pos] !== $segment) return false;
            } else {
                //нет подстановки в роутере, не должно быть и в строке
                if (strpos($transString[$pos], '{') !== false) return false;
                //Если не нулевая длина в роутере, должна быть не нулевой и в строке
                if (strlen($segment) > 0 && strlen($transString[$pos]) == 0) return false;
                if (strlen($segment) == 0 && strlen($transString[$pos]) > 0) return false;
            }
        }
        return true;
    }

    /** Скрывать ли локаль по умолчанию в URL
     *
     *
     * @return boolean
     */
    public function isHideDefaultLocaleInURL(): bool
    {
        return $this->hideDefaultLocaleInURL;
    }
}
