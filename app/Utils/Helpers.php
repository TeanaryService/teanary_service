<?php

use App\Services\LocaleCurrencyService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

if (!function_exists('isImage')) {
    function isImage($file)
    {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
    }
}

if (!function_exists('generateRandomImage')) {
    function generateRandomImage()
    {
        $disk = Storage::disk('local');
        $folder = 'demo';

        $files = $disk->files($folder);

        $images = array_filter($files, function ($file) {
            return isImage($file);
        });

        if (empty($images)) {
            return null;
        }

        return $disk->path($images[array_rand($images)]);
    }
}

if (!function_exists('locaRoute')) {
    /**
     * Generate a route URL with current locale prefix.
     *
     * @param string $name
     * @param array $parameters
     * @param bool $absolute
     * @return string
     */
    function locaRoute(string $name, array $parameters = [], bool $absolute = true): string
    {
        // 获取当前语言
        $locale = session('lang') ?? app()->getLocale();

        // 如果已有 locale 参数（优先）就使用它，否则加上当前 locale
        if (!isset($parameters['locale'])) {
            $parameters = ['locale' => $locale] + $parameters;
        }

        // 调用 Laravel 原生 route() 函数
        return route($name, $parameters, $absolute);
    }
}

if (!function_exists('switch_locale_url')) {
    function switch_locale_url(string $newLang): string
    {
        $uri = request()->path(); // e.g. 'en/products/item-1'
        $segments = explode('/', $uri);

        $service = new LocaleCurrencyService();

        $supportedLocales = $service->getLanguages()->pluck('code')->toArray();
        if (in_array($segments[0], $supportedLocales)) {
            $segments[0] = $newLang;
        } else {
            array_unshift($segments, $newLang);
        }

        return url(implode('/', $segments));
    }
}