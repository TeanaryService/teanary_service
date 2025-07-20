<?php

namespace App\Services;

use Closure;

class RequestQueryCacheService
{
    protected static array $cache = [];

    public static function remember(string $key, \Closure $callback): mixed
    {
        if (!array_key_exists($key, self::$cache)) {
            self::$cache[$key] = $callback();
        }

        return self::$cache[$key];
    }
}