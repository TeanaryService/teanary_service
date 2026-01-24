<?php

namespace App\Livewire\Traits;

/**
 * 提供通用的“按当前语言取翻译字段”的辅助方法。
 *
 * 用于减少各组件内重复的 `firstWhere('language_id', ...)` + fallback 逻辑。
 */
trait HasTranslatedNames
{
    /**
     * 从翻译集合中按语言取字段值，取不到则回退到第一条翻译，再回退到给定默认值。
     *
     * @param  object|null  $translations  通常为 Eloquent Collection（含 firstWhere/first）
     * @param  object|null  $lang  通常为 Language 模型（含 id）
     * @param  string  $field  翻译字段名（默认 name）
     * @param  string  $default  无任何可用值时的默认返回
     */
    protected function translatedField($translations, $lang, string $field = 'name', string $default = ''): string
    {
        if (! $translations) {
            return $default;
        }

        $langId = $lang?->id ?? null;
        $translation = $langId ? $translations->firstWhere('language_id', $langId) : null;
        $value = $translation?->{$field} ?? null;
        if ($value !== null && $value !== '') {
            return (string) $value;
        }

        $first = $translations->first();
        $fallback = $first?->{$field} ?? null;
        if ($fallback !== null && $fallback !== '') {
            return (string) $fallback;
        }

        return $default;
    }
}

