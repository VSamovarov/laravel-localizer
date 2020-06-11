<?php
return [

    // Uncomment the languages that your site supports - or add new ones.
    // These are sorted by the native name, which is the order you might show them in a language selector.
    // Regional languages are sorted by their base language, so "British English" sorts as "English, British"
    'supportedLocales' => [
        'uk'          => ['name' => 'Ukrainian', 'script' => 'Cyrl', 'native' => 'українська', 'regional' => 'uk_UA'],
        'en'          => ['name' => 'English', 'script' => 'Latn', 'native' => 'English', 'regional' => 'en_GB'],
        'ru'          => ['name' => 'Russian', 'script' => 'Cyrl', 'native' => 'русский', 'regional' => 'ru_RU'],
    ],


    // If `hideDefaultLocaleInURL` is true, then a url without locale
    // is identical with the same url with default locale.
    // For example, if `en` is default locale, then `/en/about` and `/about`
    // would be identical.
    'hideDefaultLocaleInURL' => false,

    // Имя фйала с переводами, который размещается там же
    // где и прочие языковые файлы
    //
    // Перевод должен быть отдельно для каждого сегмента Uri
    // Например для 'home/about/article/{id}'
    // return [
    //      'home' => 'домашняя',
    //      'about' => 'о нас',
    //      'article' => 'статья',
    // ];
    // Получим 'домашняя/о нас/статья/{id}'
    'translationFileName' => 'routes',

    // Используется в атрибуте 'as',
    // для наименования переведенных роутеров.
    // К этому префиксу будет добавляться название локали
    // Например, "localizer-ru"
    //
    // Доступ к именованному будет с помощью этого префикса
    // Например, route('localizer-ru.user)
    'localizerNamePrefix' => 'localizer-',

];