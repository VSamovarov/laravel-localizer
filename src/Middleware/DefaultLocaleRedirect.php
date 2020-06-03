<?php

namespace VSamovarov\LaravelLocalizer\Middleware;

use VSamovarov\LaravelLocalizer\Localizer;
use Illuminate\Http\Request;
use Closure;

/**
 * Делаем редирект на урл без первого сегмента (скрываем язык)
 */
class DefaultLocaleRedirect
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
        if ($request->segment(1) === $this->localizer->getDefaultLocale()) {
            return redirect($this->getUrlWithoutLocal($request), 302);
        }
        return $next($request);
    }

    /**
     * Убирает первый сегмент в Url
     *
     * @param Request $request
     * @return string
     */
    public function getUrlWithoutLocal(Request $request): string
    {
        $segments = $request->segments();
        array_shift($segments);
        array_unshift($segments, $request->getSchemeAndHttpHost());
        $request = $request->create(implode('/', $segments), 'GET', $request->all());
        return $request->fullUrl();
    }
}