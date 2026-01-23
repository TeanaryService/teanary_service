<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Models\AttributeValue;
use App\Models\AttributeValueTranslation;
use App\Services\LocaleCurrencyService;
use Livewire\Component;

class AttributeValueForm extends Component
{
    public ?int $attributeValueId = null;
    public ?int $attributeId = null;
    public string $translationStatus = '';
    public array $translations = [];

    protected array $rules = [
        'attributeId' => 'required|exists:attributes,id',
        'translationStatus' => 'required',
        'translations.*.name' => 'required|max:255',
    ];

    protected array $messages = [
        'attributeId.required' => '属性不能为空',
        'attributeId.exists' => '选择的属性不存在',
        'translationStatus.required' => '翻译状态不能为空',
        'translations.*.name.required' => '属性值名称不能为空',
        'translations.*.name.max' => '属性值名称不能超过255个字符',
    ];

    public function mount(?int $id = null): void
    {
        $this->translationStatus = TranslationStatusEnum::NotTranslated->value;

        if ($id) {
            $this->attributeValueId = $id;
            $attributeValue = AttributeValue::with('attributeValueTranslations')->findOrFail($id);
            $this->attributeId = $attributeValue->attribute_id;
            $this->translationStatus = $attributeValue->translation_status->value;

            // 加载翻译
            $service = app(LocaleCurrencyService::class);
            $languages = $service->getLanguages();
            foreach ($languages as $language) {
                $translation = $attributeValue->attributeValueTranslations->where('language_id', $language->id)->first();
                $this->translations[$language->id] = [
                    'name' => $translation ? $translation->name : '',
                ];
            }
        } else {
            // 初始化翻译数组
            $service = app(LocaleCurrencyService::class);
            $languages = $service->getLanguages();
            foreach ($languages as $language) {
                $this->translations[$language->id] = [
                    'name' => '',
                ];
            }
        }
    }

    public function save()
    {
        // 验证默认语言必须填写
        $service = app(LocaleCurrencyService::class);
        $defaultLanguage = $service->getLanguages()->firstWhere('default', true);
        if ($defaultLanguage && empty($this->translations[$defaultLanguage->id]['name'])) {
            $this->addError('translations.'.$defaultLanguage->id.'.name', '默认语言的属性值名称不能为空');

            return;
        }

        $this->validate();

        $data = [
            'attribute_id' => $this->attributeId,
            'translation_status' => TranslationStatusEnum::from($this->translationStatus),
        ];

        if ($this->attributeValueId) {
            $attributeValue = AttributeValue::findOrFail($this->attributeValueId);
            $attributeValue->update($data);

            // 更新翻译
            foreach ($this->translations as $languageId => $translation) {
                if (! empty($translation['name'])) {
                    AttributeValueTranslation::updateOrCreate(
                        [
                            'attribute_value_id' => $attributeValue->id,
                            'language_id' => $languageId,
                        ],
                        [
                            'name' => $translation['name'],
                        ]
                    );
                }
            }

            session()->flash('message', __('app.updated_successfully'));
        } else {
            $attributeValue = AttributeValue::create($data);

            // 创建翻译
            foreach ($this->translations as $languageId => $translation) {
                if (! empty($translation['name'])) {
                    AttributeValueTranslation::create([
                        'attribute_value_id' => $attributeValue->id,
                        'language_id' => $languageId,
                        'name' => $translation['name'],
                    ]);
                }
            }

            session()->flash('message', __('app.created_successfully'));
        }

        return redirect()->to(locaRoute('manager.attribute-values'), navigate: true);
    }

    public function render()
    {
        $service = app(LocaleCurrencyService::class);
        $languages = $service->getLanguages();
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);
        $attributes = \App\Models\Attribute::with('attributeTranslations')->get();

        return view('livewire.manager.attribute-value-form', [
            'languages' => $languages,
            'attributes' => $attributes,
            'lang' => $lang,
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}
