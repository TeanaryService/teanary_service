<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Livewire\Traits\HandlesTranslations;
use App\Livewire\Traits\HasNavigationRedirect;
use App\Models\AttributeValue;
use App\Models\AttributeValueTranslation;
use Livewire\Component;

class AttributeValueForm extends Component
{
    use HandlesTranslations;
    use HasNavigationRedirect;

    public ?int $attributeValueId = null;
    public ?int $attributeId = null;

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
        $this->initializeTranslationStatus();

        if ($id) {
            $this->attributeValueId = $id;
            $attributeValue = AttributeValue::with('attributeValueTranslations')->findOrFail($id);
            $this->attributeId = $attributeValue->attribute_id;
            $this->translationStatus = $attributeValue->translation_status->value;
            $this->initializeTranslations($attributeValue, 'attributeValueTranslations', ['name']);
        } else {
            $this->initializeTranslations(null, 'attributeValueTranslations', ['name']);
        }
    }

    public function save()
    {
        if (! $this->validateDefaultLanguage('name', '默认语言的属性值名称不能为空')) {
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
            $this->saveTranslations($attributeValue, AttributeValueTranslation::class, 'attribute_value_id', ['name']);
            $this->flashMessage('updated_successfully');
        } else {
            $attributeValue = AttributeValue::create($data);
            $this->saveTranslations($attributeValue, AttributeValueTranslation::class, 'attribute_value_id', ['name']);
            $this->flashMessage('created_successfully');
        }

        return $this->redirectWithMessage('manager.attribute-values', $this->attributeValueId ? 'updated_successfully' : 'created_successfully');
    }

    public function render()
    {
        // 注意：避免与 Blade 组件系统的 $attributes 变量名冲突
        $attributeModels = \App\Models\Attribute::with('attributeTranslations')->get();

        return view('livewire.manager.attribute-value-form', [
            'languages' => $this->getLanguages(),
            'attributeModels' => $attributeModels,
            'lang' => $this->getCurrentLanguage(),
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}
