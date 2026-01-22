<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Models\Attribute;
use App\Models\AttributeTranslation;
use App\Services\LocaleCurrencyService;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class AttributeForm extends Component
{
    public ?int $attributeId = null;
    public bool $isFilterable = false;
    public string $translationStatus = '';
    public array $translations = [];

    protected array $rules = [
        'isFilterable' => 'boolean',
        'translationStatus' => 'required',
        'translations.*.name' => 'required|max:255',
    ];

    protected array $messages = [
        'translationStatus.required' => '翻译状态不能为空',
        'translations.*.name.required' => '属性名称不能为空',
        'translations.*.name.max' => '属性名称不能超过255个字符',
    ];

    public function mount(?int $id = null): void
    {
        $this->translationStatus = TranslationStatusEnum::NotTranslated->value;

        if ($id) {
            $this->attributeId = $id;
            $attribute = Attribute::with('attributeTranslations')->findOrFail($id);
            $this->isFilterable = $attribute->is_filterable;
            $this->translationStatus = $attribute->translation_status->value;

            // 加载翻译
            $service = app(LocaleCurrencyService::class);
            $languages = $service->getLanguages();
            foreach ($languages as $language) {
                $translation = $attribute->attributeTranslations->where('language_id', $language->id)->first();
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
            $this->addError('translations.' . $defaultLanguage->id . '.name', '默认语言的属性名称不能为空');
            return;
        }

        $this->validate();

        $data = [
            'is_filterable' => $this->isFilterable,
            'translation_status' => TranslationStatusEnum::from($this->translationStatus),
        ];

        if ($this->attributeId) {
            $attribute = Attribute::findOrFail($this->attributeId);
            $attribute->update($data);

            // 更新翻译
            foreach ($this->translations as $languageId => $translation) {
                if (!empty($translation['name'])) {
                    AttributeTranslation::updateOrCreate(
                        [
                            'attribute_id' => $attribute->id,
                            'language_id' => $languageId,
                        ],
                        [
                            'name' => $translation['name'],
                        ]
                    );
                }
            }

            Cache::forget('attributes.with.translations');
            session()->flash('message', __('app.updated_successfully'));
        } else {
            $attribute = Attribute::create($data);

            // 创建翻译
            foreach ($this->translations as $languageId => $translation) {
                if (!empty($translation['name'])) {
                    AttributeTranslation::create([
                        'attribute_id' => $attribute->id,
                        'language_id' => $languageId,
                        'name' => $translation['name'],
                    ]);
                }
            }

            Cache::forget('attributes.with.translations');
            session()->flash('message', __('app.created_successfully'));
        }

        return redirect()->to(locaRoute('manager.attributes'), navigate: true);
    }

    public function render()
    {
        $service = app(LocaleCurrencyService::class);
        $languages = $service->getLanguages();

        return view('livewire.manager.attribute-form', [
            'languages' => $languages,
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}
