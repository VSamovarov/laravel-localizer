<?php

namespace VSamovarov\LaravelLocalizer\Controllers;

/**
 * Клас, заглушка
 * Без него не работает кеширование роутеров
 */
class Dummy
{
    public function abort()
    {
        return abort('404');
    }
}