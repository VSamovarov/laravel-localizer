<?php

namespace VSamovarov\LaravelLocalizer;

use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollectionInterface;
use Illuminate\Routing\Router;
use Illuminate\Contracts\Translation\Translator as TranslatorContract;
use VSamovarov\LaravelLocalizer\Exceptions\NestedLocalizerRouteGroup;

class RouteGroupLocalizer
{
    private $localizer;
    private $translator;
    private $route;
    private $request;


    public function __construct(Localizer $localizer, TranslatorContract $translator, Router $route, Request $request)
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
                'as' =>  $this->localizer->getNamePrefix() . "{$lang['slug']}."
            ], function () use ($attributes, $routes) {
                $this->route->group($attributes, $routes);
            });
        }

        $routes = $this->route->getRoutes();

        /**
         * Локализующие группы, запрещено вкладывать друг в друга
         */
        if ($this->checkGroupNested($routes)) {
            throw new NestedLocalizerRouteGroup();
        }

        if (!app('localizer')->isHideDefaultLocaleInURL()) {
            $this->addMainRoute($routes);
        }

        $this->translateRoutes($routes);
    }

    /**
     * Проверяем, есть ли вложения локализующих групп роутеров.
     * Просто анализируем имя роутера.
     * Если в нем несколько раз встречается префикс локализации,
     * значит группа вложенная
     *
     * @param RouteCollectionInterface $routes
     * @return boolean
     */
    private function checkGroupNested($routes): bool
    {
        $locales = $this->localizer->getSlagsSupportedLocales();
        $matchPattern =  '{' . $this->localizer->getNamePrefix() . '(' . implode('|', $locales) . ').*[.]' . $this->localizer->getNamePrefix() . '(' . implode('|', $locales) . ')[.]' . '}';
        foreach (array_keys($routes->getRoutesByName()) as $name) {
            if (preg_match($matchPattern, $name)) return true;
        }
        return false;
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
     * Локализирует роутеры
     *
     * Ищет роутеры с префиксом локалайзера и устанавливает новые (переведенные) урлы
     *
     * @param RouteCollectionInterface $routes
     * @return void
     */
    public function translateRoutes(RouteCollectionInterface $routes): void
    {
        $prefix = $this->localizer->getNamePrefix();
        foreach ($routes as $route) {
            $name = $route->getName();

            if (strpos($name, $prefix) !== false) {
                // определяем локаль из имени роутера
                $locale = substr(
                    substr($name, 0, strpos($name, '.')),
                    strlen($this->localizer->getNamePrefix())
                );
                if ($locale) {
                    $route->setUri(
                        $this->translateUri($route->uri(), $locale, $this->localizer->getTranslationFileName(), $this->translator)
                    );
                }
            }
        }
    }

    /**
     * Локализирует УРЛы
     *
     * Переводится каждый сегмент отдельно.
     * Если перевода сегмента нет, то остается прежний
     *
     * @param string $uri
     * @param string $locale
     * @param string $group
     * @param TranslatorContract $translator
     * @return string
     */
    public function translateUri(string $uri, string $locale, string $group, TranslatorContract $translator): string
    {
        $parts = array_map(function ($part) use ($locale, $group, $translator) {
            $newPart = $translator->get("{$group}.{$part}", [], $locale, false);
            return $newPart == "{$group}.{$part}" ? $part : $newPart;
        }, explode('/', $uri));
        return implode('/', $parts);
    }
}