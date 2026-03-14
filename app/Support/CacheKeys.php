<?php

namespace App\Support;

/**
 * 缓存键常量类
 * 统一管理所有缓存键，避免硬编码
 */
class CacheKeys
{
    // 分类相关
    public const CATEGORIES_WITH_TRANSLATIONS = 'categories.with.translations';

    // 国家相关
    public const COUNTRIES_WITH_TRANSLATIONS = 'countries.with.translations';

    // 地区相关
    public const ZONES_WITH_TRANSLATIONS = 'zones.with.translations';

    // 语言相关
    public const LANGUAGES_ALL = 'languages.all';

    // 货币相关
    public const CURRENCIES_ALL = 'currencies.all';

    // 仓库（分仓）相关
    public const WAREHOUSES_ALL = 'warehouses.all';
}
