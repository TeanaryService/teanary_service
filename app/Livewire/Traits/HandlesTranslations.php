<?php

namespace App\Livewire\Traits;

use App\Enums\TranslationStatusEnum;
use App\Services\LocaleCurrencyService;

/**
 * 提供翻译处理功能的 Trait.
 *
 * 用于需要处理多语言翻译的表单组件
 */
trait HandlesTranslations
{
    use UsesLocaleCurrency;

    /**
     * 翻译数据数组.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $translations = [];

    /**
     * 翻译状态.
     */
    public string $translationStatus = '';

    /**
     * 初始化翻译数组.
     *
     * @param  object|null  $model  模型实例
     * @param  string  $translationRelation  翻译关系名称（如 'categoryTranslations'）
     * @param  array  $fields  需要加载的字段（默认 ['name']）
     */
    protected function initializeTranslations(?object $model, string $translationRelation, array $fields = ['name']): void
    {
        $languages = $this->getLanguages();

        if ($model && $model->{$translationRelation}) {
            foreach ($languages as $language) {
                $translation = $model->{$translationRelation}->where('language_id', $language->id)->first();
                $translationData = [];
                foreach ($fields as $field) {
                    $translationData[$field] = $translation ? ($translation->{$field} ?? '') : '';
                }
                $this->translations[$language->id] = $translationData;
            }
        } else {
            foreach ($languages as $language) {
                $translationData = [];
                foreach ($fields as $field) {
                    $translationData[$field] = '';
                }
                $this->translations[$language->id] = $translationData;
            }
        }
    }

    /**
     * 验证默认语言是否填写.
     *
     * @param  string  $field  字段名（默认 'name'）
     * @param  string  $errorMessage  错误消息
     * @return bool
     */
    protected function validateDefaultLanguage(string $field = 'name', ?string $errorMessage = null): bool
    {
        $defaultLanguage = $this->getDefaultLanguage();

        if ($defaultLanguage && empty($this->translations[$defaultLanguage->id][$field] ?? '')) {
            $message = $errorMessage ?? "默认语言的{$field}不能为空";
            $this->addError("translations.{$defaultLanguage->id}.{$field}", $message);

            return false;
        }

        return true;
    }

    /**
     * 保存翻译数据.
     *
     * @param  object  $model  模型实例
     * @param  string  $translationModel  翻译模型类名（如 CategoryTranslation::class）
     * @param  string  $foreignKey  外键名称（如 'category_id'）
     * @param  array  $fields  需要保存的字段（默认 ['name']）
     */
    protected function saveTranslations(object $model, string $translationModel, string $foreignKey, array $fields = ['name']): void
    {
        foreach ($this->translations as $languageId => $translation) {
            if (! empty($translation['name'] ?? '')) {
                $data = [$foreignKey => $model->id, 'language_id' => $languageId];
                foreach ($fields as $field) {
                    if (isset($translation[$field])) {
                        $data[$field] = $translation[$field];
                    }
                }

                $translationModel::updateOrCreate(
                    [$foreignKey => $model->id, 'language_id' => $languageId],
                    $data
                );
            }
        }
    }

    /**
     * 初始化翻译状态.
     */
    protected function initializeTranslationStatus(): void
    {
        if (empty($this->translationStatus)) {
            $this->translationStatus = TranslationStatusEnum::NotTranslated->value;
        }
    }
}
