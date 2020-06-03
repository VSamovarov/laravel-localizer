<?php

namespace VSamovarov\LaravelLocalizer\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \VSamovarov\LaravelLocalizer\Skeleton\SkeletonClass
 */
class LocalizerRouter extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'localizerRouter';
    }
}