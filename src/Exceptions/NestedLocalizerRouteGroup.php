<?php

namespace VSamovarov\LaravelLocalizer\Exceptions;

use \Exception;

/**
 * Локализующие группы, запрещено вкладывать друг в друга
 */
class NestedLocalizerRouteGroup  extends Exception
{
    public function __construct($message = 'Should not be nested group of localization routers')
    {
        $this->message = $message;
    }
}