<?php

namespace VSamovarov\LaravelLocalizer\Middleware;

use VSamovarov\LaravelLocalizer\Localizer;
use Closure;

/**
 * Устанавливаем язык для API
 */
class LocalizationApi
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
        if ($request->has('lang')) {
            $lang = $request->input('lang');
            if (!$this->localizer->isSupportedLocale($lang)) {
                return response()->json(['error' => 'Not Found!'], 404);
            }
        } else {
            $lang = $this->localizer->getDefaultLocale();
        }

        $request->merge(['lang' => $lang]);
        $this->localizer->setLocale($lang);

        return $next($request);
    }
}