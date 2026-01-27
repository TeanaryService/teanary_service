<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Livewire\Traits\HandlesTranslations;
use App\Livewire\Traits\HasNavigationRedirect;
use App\Livewire\Traits\HasTranslatedNames;
use App\Models\Specification;
use App\Models\SpecificationValue;
use App\Models\SpecificationValueTranslation;
use Livewire\Component;

class SpecificationValueForm extends Component
{
    use HandlesTranslations;
    use HasNavigationRedirect;
    use HasTranslatedNames;

    public ?int $specificationValueId = null;
    public ?int $specificationId = null;

    protected array $rules = [
        'specificationId' => 'required|integer',
        'translationStatus' => 'required',
        'translations.*.name' => 'required|max:255',
    ];

    protected array $messages = [
        'specificationId.required' => '规格不能为空',
        'translationStatus.required' => '翻译状态不能为空',
        'translations.*.name.required' => '规格值名称不能为空',
        'translations.*.name.max' => '规格值名称不能超过255个字符',
    ];

    public function mount(?int $id = null): void
    {
        $this->initializeTranslationStatus();

        if ($id) {
            $this->specificationValueId = $id;
            $value = SpecificationValue::with('specificationValueTranslations')->findOrFail($id);
            $this->specificationId = $value->specification_id;
            $this->translationStatus = $value->translation_status->value;
            $this->initializeTranslations($value, 'specificationValueTranslations', ['name']);
        } else {
            $this->initializeTranslations(null, 'specificationValueTranslations', ['name']);
        }
    }

    public function save()
    {
        if (! $this->validateDefaultLanguage('name', '默认语言的规格值名称不能为空')) {
            return;
        }

        $this->validate();

        $data = [
            'specification_id' => $this->specificationId,
            'translation_status' => TranslationStatusEnum::from($this->translationStatus),
        ];

        if ($this->specificationValueId) {
            $value = SpecificationValue::findOrFail($this->specificationValueId);
            $value->update($data);
            $this->saveTranslations($value, SpecificationValueTranslation::class, 'specification_value_id', ['name']);
            $this->flashMessage('updated_successfully');
        } else {
            $value = SpecificationValue::create($data);
            $this->saveTranslations($value, SpecificationValueTranslation::class, 'specification_value_id', ['name']);
            $this->flashMessage('created_successfully');
        }

        return $this->redirectWithMessage('manager.specification-values', $this->specificationValueId ? 'updated_successfully' : 'created_successfully');
    }

    public function render()
    {
        $specifications = Specification::with('specificationTranslations')->get();

        return view('livewire.manager.specification-value-form', [
            'languages' => $this->getLanguages(),
            'specifications' => $specifications,
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }

    public function getSpecificationLabel(Specification $spec): string
    {
        $lang = $this->getCurrentLanguage();

        return $this->translatedField(
            $spec->specificationTranslations,
            $lang,
            'name',
            (string) $spec->id
        );
    }
}
