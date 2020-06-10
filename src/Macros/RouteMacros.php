<?php

namespace VSamovarov\LaravelLocalizer\Macros;

use Illuminate\Support\Facades\Route;
use Illuminate\Routing\RouteFileRegistrar;

class RouteMacros
{
    /**
     * Макрос для Route, который создает группу роутеров
     * для каждого языка с соответствующими префиксами и именами
     * Должен быть в самом верху и оборачивать все роутеры.
     *
     * Функции локализации маршрутов, используют текущее значение локали
     * потому меняем ее, для каждой группы, в соответствии с из языком,
     * а потом восстанавливаем
     */
    public function localizedGroup()
    {
        /**
         * @param \Closure|string|array $attributes
         * @param \Closure|string $routes
         * @return void
         */
        return function ($attributes, $routes) {
            app('routeGroupLocalizer')->routeGroupLocalizer($attributes, $routes);
        };
    }
}