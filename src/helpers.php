<?php


if (!function_exists('troute')) {
    /**
     * Возвращает переведенный именованный маршрут
     *
     * @param string $name
     * @param array $parameters
     * @param string $local
     * @param boolean $absolute
     * @return string
     */
    function troute(string $name, $parameters = [], $local = '', $absolute = true): string
    {
        return $name;
    }
}

if (!function_exists('translateUri')) {
    /**
     * Возвращает переведенный Url с помощью Translate
     *
     * @param string $uri
     * @return string
     */
    function translateUri(string $uri): string
    {
        return $uri;
    }
}