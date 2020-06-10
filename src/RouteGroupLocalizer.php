<?php

namespace VSamovarov\LaravelLocalizer;

use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollectionInterface;
use Illuminate\Routing\Router;
use Illuminate\Translation\Translator;
use Illuminate\Contracts\Translation\Translator as TranslatorContract;

class RouteGroupLocalizer
{
    private $localizer;
    private $translator;
    private $route;
    private $request;
    const LOCALIZER_NAME_PREFIX = 'localizer-';
    const NAME_LANGUAGE_FILE = 'routes';

    public function __construct(Localizer $localizer, Translator $translator, Router $route, Request $request)
    {
        $this->localizer = $localizer;
        $this->translator = $translator;
        $this->route = $route;
        $this->request = $request;
    }

    /**
     * Cоздает группу роутеров
     * для каждого языка с соответствующими префиксами и именами
     * Должен быть в самом верху и оборачивать все локализируемые роутеры.
     *
     * @param \Closure|string|array $attributes
     * @param \Closure|string $routes
     * @return void
     */
    public function routeGroupLocalizer($attributes, $routes)
    {
        foreach ($this->localizer->getSupportedLocales() as $lang) {
            $this->route->group([
                'prefix' => $lang['prefix'],
                'as' =>  self::LOCALIZER_NAME_PREFIX . $lang['slug'] . "."
            ], function () use ($attributes, $routes) {
                $this->route->group($attributes, $routes);
            });
        }

        $routes = $this->route->getRoutes();

        if (!app('localizer')->isHideDefaultLocaleInURL()) {
            $this->addMainRoute($routes);
        }

        $this->translateRoutes($routes);
    }

    /**
     * Создаем дополнительный роутер, для главной страницы,
     * без указания языка - site.name/
     * Надо для SEO и правильной обработки редиректа
     * В миделваре примем решения, оставить дубль страницы
     * или делать редирект на страницу с указанием локали по умолчанию  - site.name/uk
     *
     * @param RouteCollectionInterface $routes
     * @return void
     */
    public function addMainRoute(RouteCollectionInterface $routes): void
    {
        try {
            /**
             * Ищем в коллекции роутер с URL '/'
             * Если не находит, конструкция выбрасывает исключение
             * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
             * тогда пытаемся сделать новый роутер
             */
            $routes->match($this->request->create('/', 'GET'));
        } catch (\Exception $e) {
            try {
                $action = $routes->match($this->request->create('/' . $this->localizer->getDefaultLocale(), 'GET'))
                    ->getAction();

                $this->route->namespace('\\')->group(function () use ($action) {
                    $action['prefix'] = '';
                    $action['as'] = '';
                    $this->route->get('', $action);
                });
            } catch (\Exception $e) {
                //
            }
        }
    }

    /**
     * Локализирует УРЛы роутеров
     *
     * @param RouteCollectionInterface $routes
     * @return void
     */
    public function translateRoutes(RouteCollectionInterface $routes): void
    {
        foreach ($routes as $route) {
            $name = $route->getName();
            if (strpos($name, self::LOCALIZER_NAME_PREFIX) !== false) {
                // определяем локаль из имени роутера
                $locale = substr(
                    substr($name, 0, strpos($name, '.')),
                    strlen(self::LOCALIZER_NAME_PREFIX)
                );
                if ($locale) {
                    $route->setUri($this->translateUri($route->uri(), $locale, self::NAME_LANGUAGE_FILE, $this->translator));
                }
            }
        }
    }


    public function translateUri(string $uri, string $locale, string $group, TranslatorContract $translator): string
    {
        $parts = array_map(function ($part) use ($locale, $group, $translator) {
            $newPart = $translator->get("{$group}.{$part}", [], $locale, false);
            return $newPart == "{$group}.{$part}" ? $part : $newPart;
        }, explode('/', $uri));

        return implode('/', $parts);
    }
}