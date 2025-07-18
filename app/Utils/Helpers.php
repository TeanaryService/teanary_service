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

if (!function_exists('getFilamentUrl')) {
    /**
     * 获取带语言前缀的 Filament 页面 URL
     *
     * @param class-string $page  Filament Page 类名
     * @param string $uri         页面的子路径（默认 '/'）
     * @param array $params       其他参数（会合并 locale 参数）
     * @return string
     */
    function getFilamentUrl($page, string $uri = '/', array $params = [])
    {
        $locale = session('lang') ?? App::getLocale();

        return $page::route($uri, array_merge(['locale' => $locale], $params));
    }
}
