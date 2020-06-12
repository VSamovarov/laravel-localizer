<?php

namespace VSamovarov\LaravelLocalizer\Middleware;

use VSamovarov\LaravelLocalizer\Localizer;
use Closure;
use Illuminate\Http\Request;

/**
 * Устанавливаем локаль для Веб
 */
class LocalizerMiddleware
{
    private $localizer;
    public function __construct(Localizer $localizer)
    {
        $this->localizer = $localizer;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $prefix = (string) $request->segment(1);
        $this->setLocale($prefix);

        return $next($request);
    }

    /**
     * Устанавливаем локаль
     *
     * @param string $prefix
     * @return void
     */
    private function setLocale(string $prefix): void
    {
        if ($this->localizer->isSupportedLocale($prefix)) {
            $this->localizer->setLocale($prefix);
        } else {
            $this->localizer->setLocale($this->localizer->getDefaultLocale());
        }
    }
}
