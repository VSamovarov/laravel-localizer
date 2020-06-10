<?php

namespace VSamovarov\LaravelLocalizer;

use VSamovarov\LaravelLocalizer\Exceptions\ConfigLocalizerNotDefined;

class Localizer
{
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

    public function __construct($config)
    {
        if (empty($config) || !is_array($config)) {
            throw new ConfigLocalizerNotDefined('Не задана конфигурация');
        }

        $this->defaultLocale = current(array_keys($config['supportedLocales']));
        $this->hideDefaultLocaleInURL = !empty($config['hideDefaultLocaleInURL']);

        $this->setSupportedLocales($config['supportedLocales']);
        $this->setLocale($this->defaultLocale);
    }

    /**
     * Set supported Locales
     *
     * @return array
     */
    public function setSupportedLocales($supportedLocales): void
    {
        /**
         * Определяем префикс, который будет в URL
         * Он может зависеть от параметра $hideDefaultLocaleInURL и настроек
         */
        foreach ($supportedLocales as $key => $value) {
            $this->supportedLocales[$key] = array_merge(
                $value,
                [
                    'slug' => $key,
                    'prefix' => $this->getDefaultLocale() === $key && $this->isHideDefaultLocaleInURL() ? '' : $key
                ]
            );
        }
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
     * Получаем локаль по умолчанию
     *
     * @return string
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    /**
     * Установка локали
     *
     * @param string $locale
     * @return void
     */
    public function setLocale($locale): void
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
