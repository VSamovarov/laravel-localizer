<?php


if (!function_exists('t_route')) {
    /**
     * Возвращает переведенный именованный маршрут
     *
     * @param string $name
     * @param array $parameters
     * @param string $local
     * @param boolean $absolute
     * @return string
     */
    function t_route(string $name, $parameters = [], $local = '', $absolute = true): string
    {
        if (empty($local)) $local = app('localizer')->getLocale();
        $name = app('localizer')->getNamePrefix() . $local . '.' . $name;

        return route($name, $parameters, $absolute);
    }
}