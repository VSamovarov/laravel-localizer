# Laravel localizator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vsamovarov/laravel-localizer.svg?style=flat-square)](https://packagist.org/packages/vsamovarov/laravel-localizer)
[![Build Status](https://img.shields.io/travis/vsamovarov/laravel-localizer/master.svg?style=flat-square)](https://travis-ci.org/vsamovarov/laravel-localizer)
[![Quality Score](https://img.shields.io/scrutinizer/g/vsamovarov/laravel-localizer.svg?style=flat-square)](https://scrutinizer-ci.com/g/vsamovarov/laravel-localizer)
[![Total Downloads](https://img.shields.io/packagist/dt/vsamovarov/laravel-localizer.svg?style=flat-square)](https://packagist.org/packages/vsamovarov/laravel-localizer)

Простая локализация, с использованием встроенной функциональности Laravel. Значение локали извлекается из URL.

-   Поддерживает кеширование
-   Поддержка именованных маршрутов
-   Переводимые маршруты
-   Возможность скрыть локаль по умолчанию в URL

## Installation

Установка с помощью Composer:

```bash
composer require vsamovarov/laravel-localizer
```

## Usage

#### Группы для маршрутов

Laravel Localizator создает группы для маршрутов, с предопределенными параметрами 'prefix' и 'as'.

Например:

_routes/web.php_

```php
app('localizerRouter')->group(
    function () {
        Route::get('article/about','Controller@metod')->name('about');
    }
);
```

Создаст маршруты для предопределенных языков **en, ru, uk**

| Method   | URI              | Name     |
| -------- | ---------------- | -------- |
| GET/HEAD | article/about    | en.about |
| GET/HEAD | ru/article/about | ru.about |
| GET/HEAD | uk/article/about | uk.about |

Так же можно использовать хелпер localizerRouter();

```php
localizerRouter(
    function () {
        Route::get('/about', 'Controller@metod')->name('about');
    }
);
```

Префикс языка по умолчанию в url скрывается.

Отображение можно разрешить с помощью опции `hideDefaultLocaleInURL` в файле конфигурации.

#### Переведенные маршруты

Вы можете перевести ваши маршруты. Например, http://example.com/en/article/about, http://example.com/ru/statya/o-nas (на русском) будут перенаправлены на тот же контроллер/представление

```php
// resources/lang/en/rout.php
return [
     "article/about" => "article/about" ,
];
```

```php
// resources/lang/ru/rout.php
return [
     "article/about" => "statya/o-nas" ,
];
```

Делается это с помощью метода **transRoute()** класса **Localizer**;

```php
app('localizerRouter')->group(function () {
    Route::any(app('localizer')->transRoute('article/about'), 'Controller@metod')->name('about');
});
```

Можно, также, использовать фасад **Localizer**

```php
app('localizerRouter')->group(function () {
    Route::any(Localizer::transRoute('article/about'), 'Controller@metod')->name('about');
});
```

Если маршрут не имеет перевода, то остается тот, что указан.

Структура маршрута и его перевода должны совпадать

```
'article/about' => 'statya/o-nas', //ok
'article/{id}' => 'statya/{id}', //ok
'article/about' => 'statya', //Error
'article/{id}' => 'statya/{article}', //Error
'article/{id}' => 'statya/o-nas/{id}', //Error
```

#### Именованные маршруты

Именованные маршруты используются с указанием локали, например:

```php
$url = route(app->getLocale() . '.' .'about');
```

Для удобства можно использовать хелпер `lroute()`

```php
$url = lroute('about');
$url = lroute('article', ['id'=>'24']); //с параметрами
$url = lroute('article', [],'ru'); //с указаной локалью
```

#### Локализация API

Локализация API строится на параметре `lang` с помощью мидлвар LocalizationApi

```php
Route::middleware('VSamovarov\LaravelLocalizer\Middleware\LocalizationApi::class')->get('/article', 'Controller@metod');
```

или

```php
Route::middleware('LocalizationApi')->get('/article', 'Controller@metod');
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
