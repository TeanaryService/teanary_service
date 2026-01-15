<?php

namespace App\Traits;

trait HasResourceNavigation
{
    /**
     * 获取资源类名（不含命名空间）
     */
    protected static function getResourceClassName(): string
    {
        $className = class_basename(static::class);
        // 移除 Resource 后缀（如果存在）
        return str_replace('Resource', '', $className);
    }

    /**
     * 获取翻译键前缀
     */
    protected static function getTranslationKeyPrefix(): string
    {
        return static::getResourceClassName() . 'Resource';
    }

    public static function getLabel(): string
    {
        return __('filament.' . static::getTranslationKeyPrefix() . '.label');
    }

    public static function getPluralLabel(): string
    {
        return __('filament.' . static::getTranslationKeyPrefix() . '.pluralLabel');
    }

    public static function getNavigationGroup(): string
    {
        return __('filament.' . static::getTranslationKeyPrefix() . '.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.' . static::getTranslationKeyPrefix() . '.label');
    }

    public static function getNavigationIcon(): string
    {
        return __('filament.' . static::getTranslationKeyPrefix() . '.icon');
    }
}
