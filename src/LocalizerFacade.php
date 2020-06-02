<?php

namespace VSamovarov\LaravelLocalizer;

use Illuminate\Support\Facades\Facade;

/**
 * @see \VSamovarov\LaravelLocalizer\Skeleton\SkeletonClass
 */
class LocalizerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'localizer';
    }
}