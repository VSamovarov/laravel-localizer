<?php


if (!function_exists('lroute')) {
    /**
     * Возвращает переведенный именованный маршрут
     *
     * @param string $name
     * @param array $parameters
     * @param string $local
     * @param boolean $absolute
     * @return string
     */
    function lroute(string $name, $parameters = [], $local = '', $absolute = true)
    {
        return app('localizer')->localeRoute($name, $parameters, $local, $absolute);
    }
}

if (!function_exists('localizerRouter')) {

    function localizerRouter(\Closure $routes)
    {
        app('localizerRouter')->group(
            function () {
                $routes;
            }
        );
    }
}