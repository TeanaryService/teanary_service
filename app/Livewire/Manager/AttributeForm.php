<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Models\Attribute;
use App\Models\AttributeTranslation;
use App\Livewire\Traits\HandlesTranslations;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Livewire\Traits\HasNavigationRedirect;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class AttributeForm extends Component
{
    use HandlesTranslations;
    use UsesLocaleCurrency;
    use HasNavigationRedirect;

    public ?int $attributeId = null;
    public bool $isFilterable = false;

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
        $this->initializeTranslationStatus();

        if ($id) {
            $this->attributeId = $id;
            $attribute = Attribute::with('attributeTranslations')->findOrFail($id);
            $this->isFilterable = $attribute->is_filterable;
            $this->translationStatus = $attribute->translation_status->value;

            // 加载翻译
            $this->initializeTranslations($attribute, 'attributeTranslations');
        } else {
            // 初始化翻译数组
            $this->initializeTranslations(null, 'attributeTranslations');
        }
    }

    public function save()
    {
        // 验证默认语言必须填写
        if (! $this->validateDefaultLanguage('name', '默认语言的属性名称不能为空')) {
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
            $this->saveTranslations($attribute, AttributeTranslation::class, 'attribute_id');

            Cache::forget('attributes.with.translations');
            $this->flashMessage('updated_successfully');
        } else {
            $attribute = Attribute::create($data);

            // 创建翻译
            $this->saveTranslations($attribute, AttributeTranslation::class, 'attribute_id');

            Cache::forget('attributes.with.translations');
            $this->flashMessage('created_successfully');
        }

        return $this->redirectWithMessage('manager.attributes', 'created_successfully');
    }

    public function render()
    {
        $languages = $this->getLanguages();

        return view('livewire.manager.attribute-form', [
            'languages' => $languages,
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}
