# Laravel localizator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vsamovarov/laravel-localizer.svg?style=flat-square)](https://packagist.org/packages/vsamovarov/laravel-localizer)
[![Build Status](https://img.shields.io/travis/vsamovarov/laravel-localizer/master.svg?style=flat-square)](https://travis-ci.org/vsamovarov/laravel-localizer)
[![Quality Score](https://img.shields.io/scrutinizer/g/vsamovarov/laravel-localizer.svg?style=flat-square)](https://scrutinizer-ci.com/g/vsamovarov/laravel-localizer)
[![Total Downloads](https://img.shields.io/packagist/dt/vsamovarov/laravel-localizer.svg?style=flat-square)](https://packagist.org/packages/vsamovarov/laravel-localizer)

Простая локализация Laravel. Значение локали извлекается из URL.

- Переводимые маршруты
- Возможность скрыть локаль по умолчанию в URL
- Поддерживает кеширование
- Поддержка именованных маршрутов

## Installation

Установка с помощью Composer:

```bash
composer require vsamovarov/laravel-localizer
```

... опции
```bash
php artisan vendor:publish --provider="VSamovarov\LaravelLocalizer\LocalizerServiceProvider"
```
## Usage

### Группы для маршрутов

**Laravel Localizator** создает группы для маршрутов для каждой локали, с предопределенными параметрами 'prefix' и 'as'.

Используется макрос, который делает синтаксис похожим на объявление группы роутеров.

Например:

_routes/web.php_

```php
Route::localizedGroup([],
    function () {
        Route::get('/','Controller@metod')->name('home');
        Route::get('article/about','Controller@metod')->name('about');
    }
);
```

Создаст маршруты для предопределенных языков **en, ru, uk**

| Method   | URI              | Name               |
| -------- | ---------------- | ------------------ |
| GET/HEAD | /                | localiser-en.home  |
| GET/HEAD | ru               | localiser-ru.home  |
| GET/HEAD | uk               | localiser-uk.home  |
| GET/HEAD | article/about    | localiser-en.about |
| GET/HEAD | ru/article/about | localiser-ru.about |
| GET/HEAD | uk/article/about | localiser-uk.about |


Префикс языка в url по умолчанию скрывается.

Изменить поведение можно с помощью опции `hideDefaultLocaleInURL` в файле конфигурации.

Тогда создаются такие маршруты

| Method   | URI              | Name               |
| -------- | ---------------- | ------------------ |
| GET/HEAD | en               | localiser-en.home  |
| GET/HEAD | ru               | localiser-ru.home  |
| GET/HEAD | uk               | localiser-uk.home  |
| GET/HEAD | en/article/about | localiser-en.about |
| GET/HEAD | ru/article/about | localiser-ru.about |
| GET/HEAD | uk/article/about | localiser-uk.about |
| GET/HEAD | /                |                    |

Дополнительный маршрут на главную станицу, дублирует страницу с локалью по умолчанию

### Переведенные маршруты

Вы можете перевести ваши маршруты.
Например...

http://example.com/en/article/about,
http://example.com/ru/statya/o-nas (на русском)

...будут перенаправлены на тот же контроллер/представление.

Для надо создать файл переводов для соответствущих локалей, по умолчанию - `routes.php`, в котором задать перевод для каждого **сегмента** URL.

Если совпадение не будет найдено, то сегмент останется прежним.

Например
```php
// resources/lang/ru/routes.php
return [
     "article" => "statya",
     "about" => "o-nas" ,
];
```

```php
// routes/web.php
Route::localizedGroup([],
    function () {
        Route::get('article/about','Controller@metod')->name('about');
    }
);
```

Создаст маршруты для предопределенных языков **en, ru**

| Method   | URI              | Name               |
| -------- | ---------------- | ------------------ |
| GET/HEAD | en/article/about | localiser-ru.about |
| GET/HEAD | ru/statya/o-nas  | localiser-ru.about |

**Обратите внимание - ни какого дополнительного кода для перевода маршрута не требуется.**

### Именованные маршруты

Именованные маршруты удобно получать с помощью хелпера:

```php
$url = t_route('about');
$url = t_route('article', ['id'=>'24']); //с параметрами
$url = t_route('article', [],'ru'); //с указаной локалью
```

Это сокращает конструкцию, в которой необходимо указывать сложный префикс

```php
// $url = t_route('article', ['id'=>'24']);
$url = route(app('localizer')->getNamePrefix() . app()->getLocale() . '.' .'article', ['id'=>'24']);
```

### Настройки

Настройки библиотеки находятся в файле конфигурации 'config/laravel-localizer.php'

Локали определяются в секции 'supportedLocales' с помощью массива. Например:

```php
    'supportedLocales' => [
        'uk'          => ['name' => 'Ukrainian', 'script' => 'Cyrl', 'native' => 'українська', 'regional' => 'uk_UA'],
        'en'          => ['name' => 'English', 'script' => 'Latn', 'native' => 'English', 'regional' => 'en_GB'],
        'ru'          => ['name' => 'Russian', 'script' => 'Cyrl', 'native' => 'русский', 'regional' => 'ru_RU'],
    ],
```

Язык по умолчанию будет первый в массиве.

### Middleware

Локаль приложения определяется с помощью мидлвара 'LocalizerMiddleware', по url.

Сам мидлвар устанавливается автоматически в группу 'web'а

**Ни каких дополнительных манипуляций с кодом не требуется**.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
