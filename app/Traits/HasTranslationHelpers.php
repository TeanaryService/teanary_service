<?php

namespace App\Traits;

use App\Services\LocaleCurrencyService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

trait HasTranslationHelpers
{
    /**
     * 获取当前语言对象
     */
    protected static function getCurrentLanguage(): ?Model
    {
        static $cache = null;
        
        if ($cache === null) {
            $service = app(LocaleCurrencyService::class);
            $locale = app()->getLocale();
            $cache = $service->getLanguageByCode($locale);
        }
        
        return $cache;
    }

    /**
     * 从翻译集合中获取指定语言的翻译名称
     *
     * @param Collection $translations 翻译集合（如 categoryTranslations, productTranslations）
     * @param int|null $languageId 语言ID，如果为null则使用当前语言
     * @param string $field 要获取的字段名，默认为 'name'
     * @param mixed $fallback 如果找不到翻译时的回退值
     * @return mixed
     */
    protected static function getTranslationName(
        Collection $translations,
        ?int $languageId = null,
        string $field = 'name',
        $fallback = null
    ) {
        if ($translations->isEmpty()) {
            return $fallback;
        }

        $langId = $languageId ?? static::getCurrentLanguage()?->id;

        if ($langId) {
            $translation = $translations->where('language_id', $langId)->first();
            if ($translation && $translation->{$field}) {
                return $translation->{$field};
            }
        }

        // 回退到第一个翻译
        $first = $translations->first();
        return $first && $first->{$field} ? $first->{$field} : $fallback;
    }

    /**
     * 构建可搜索和可排序的翻译列
     *
     * @param string $columnName 列名
     * @param string $translationRelation 翻译关系名（如 'productTranslations', 'categoryTranslations'）
     * @param string $translationTable 翻译表名（如 'product_translations', 'category_translations'）
     * @param string $foreignKey 外键字段名（如 'product_id', 'category_id'）
     * @param string $field 翻译字段名，默认为 'name'
     * @param callable|null $getStateUsing 自定义获取状态的闭包
     * @return \Filament\Tables\Columns\TextColumn
     */
    protected static function makeTranslatableColumn(
        string $columnName,
        string $translationRelation,
        string $translationTable,
        string $foreignKey,
        string $field = 'name',
        ?callable $getStateUsing = null
    ): \Filament\Tables\Columns\TextColumn {
        $lang = static::getCurrentLanguage();
        $langId = $lang?->id ?? 1;

        $column = \Filament\Tables\Columns\TextColumn::make($columnName);

        // 设置获取状态的逻辑
        if ($getStateUsing) {
            $column->getStateUsing($getStateUsing);
        } else {
            $column->getStateUsing(function ($record) use ($translationRelation, $lang, $field) {
                $translations = $record->{$translationRelation} ?? collect();
                return static::getTranslationName($translations, $lang?->id, $field, '-');
            });
        }

        // 设置搜索逻辑
        $column->searchable(query: function ($query, string $search) use ($translationRelation, $field) {
            return $query->whereHas($translationRelation, function ($q) use ($search, $field) {
                $q->where($field, 'like', "%{$search}%");
            });
        });

        // 设置排序逻辑
        $column->sortable(query: function ($query, string $direction) use ($translationTable, $foreignKey, $field, $langId) {
            $mainTable = $query->getModel()->getTable();
            return $query->leftJoin($translationTable, function ($join) use ($mainTable, $translationTable, $foreignKey, $langId) {
                $join->on("{$mainTable}.id", '=', "{$translationTable}.{$foreignKey}")
                    ->where("{$translationTable}.language_id", '=', $langId);
            })
            ->orderBy("{$translationTable}.{$field}", $direction)
            ->select("{$mainTable}.*")
            ->groupBy("{$mainTable}.id");
        });

        return $column;
    }
}
