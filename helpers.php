<?php

if (!function_exists('lroute')) {
    function lroute(string $name, $parameters = [], $local = '', $absolute = true)
    {
        return app('localizer')->localeRoute($name, $parameters, $local, $absolute);
    }
}