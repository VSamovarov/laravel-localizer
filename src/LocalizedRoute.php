<?php

namespace VSamovarov\LaravelLocalizer;

use Illuminate\Contracts\Translation\Translator;
use VSamovarov\LaravelLocalizer\Exceptions\TranslateRouteException;
use VSamovarov\LaravelLocalizer\Exceptions\UnsupportedLocaleException;

class LocalizedRoute
{
    /**
     * Имя файла для переводов роутеров
     */
    const NAME_LANGUAGE_FILE = 'routes';

    private $localizer;
    private $translator;

    public function __construct(Localizer $localizer, Translator $translator)
    {
        $this->localizer = $localizer;
        $this->translator = $translator;
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
        $locale = $this->localizer->getLocale();
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
            $local = $this->localizer->getLocale();
        } else {
            if (!$this->localizer->isSupportedLocale($local)) {
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
}