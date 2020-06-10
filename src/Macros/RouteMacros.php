<?php

namespace VSamovarov\LaravelLocalizer\Macros;

use Illuminate\Support\Facades\Route;


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
            $locale = app('localizer')->getLocale(); //Сохраняем текущую локаль
            foreach (app('localizer')->getSupportedLocales() as $lang) {
                //Меняем локаль, для функций локализации
                //которые используют данные о текущей локали
                app('localizer')->setLocale($lang['slug']);
                Route::group([
                    'prefix' => $lang['prefix'],
                    'as' => "{$lang['slug']}."
                ], function () use ($attributes, $routes) {
                    Route::group($attributes, $routes);
                });
            }
            /**
             * Создаем дополнительный роутер, для главной страницы,
             * без указания языка - site.name/
             * Надо для SEO и правильной обработки редиректа
             * В миделваре примем решения, оставить дубль страницы
             * или делать редирект на страницу с указанием локали по умолчанию  - site.name/uk
             */
            if (!app('localizer')->isHideDefaultLocaleInURL()) {
                try {
                    /**
                     * Ищем в коллекции роутер с URL '/'
                     * Если не находит, конструкция выбрасывает исключение
                     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
                     * тогда пытаемся сделать новый роутер
                     */
                    Route::getRoutes()->match(app('request')->create('/', 'GET'));
                } catch (\Exception $e) {
                    try {
                        //Меняем локаль
                        app('localizer')->setLocale(app('localizer')->getDefaultLocale());
                        $action = Route::getRoutes()
                            ->match(app('request')->create('/' . app('localizer')->getDefaultLocale(), 'GET'))
                            ->getAction();

                        Route::namespace('\\')->group(function () use ($action) {
                            $action['prefix'] = '';
                            $action['as'] = '';
                            Route::get('', $action);
                        });
                    } catch (\Exception $e) {
                        //
                    }
                }
            }

            app('localizer')->setLocale($locale); //восстанавливаем локаль
        };
    }
}