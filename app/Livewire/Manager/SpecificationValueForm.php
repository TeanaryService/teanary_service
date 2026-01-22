<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Models\Specification;
use App\Models\SpecificationValue;
use App\Models\SpecificationValueTranslation;
use App\Services\LocaleCurrencyService;
use Livewire\Component;

class SpecificationValueForm extends Component
{
    public ?int $specificationValueId = null;
    public ?int $specificationId = null;
    public string $translationStatus = '';
    public array $translations = [];

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
        $this->translationStatus = TranslationStatusEnum::NotTranslated->value;

        $service = app(LocaleCurrencyService::class);
        $languages = $service->getLanguages();

        if ($id) {
            $this->specificationValueId = $id;
            $value = SpecificationValue::with('specificationValueTranslations')->findOrFail($id);
            $this->specificationId = $value->specification_id;
            $this->translationStatus = $value->translation_status->value;

            foreach ($languages as $language) {
                $translation = $value->specificationValueTranslations->where('language_id', $language->id)->first();
                $this->translations[$language->id] = [
                    'name' => $translation ? $translation->name : '',
                ];
            }
        } else {
            foreach ($languages as $language) {
                $this->translations[$language->id] = [
                    'name' => '',
                ];
            }
        }
    }

    public function save()
    {
        $service = app(LocaleCurrencyService::class);
        $defaultLanguage = $service->getLanguages()->firstWhere('default', true);
        if ($defaultLanguage && empty($this->translations[$defaultLanguage->id]['name'])) {
            $this->addError('translations.' . $defaultLanguage->id . '.name', '默认语言的规格值名称不能为空');
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

            foreach ($this->translations as $languageId => $translation) {
                if (! empty($translation['name'])) {
                    SpecificationValueTranslation::updateOrCreate(
                        [
                            'specification_value_id' => $value->id,
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
            $value = SpecificationValue::create($data);

            foreach ($this->translations as $languageId => $translation) {
                if (! empty($translation['name'])) {
                    SpecificationValueTranslation::create([
                        'specification_value_id' => $value->id,
                        'language_id' => $languageId,
                        'name' => $translation['name'],
                    ]);
                }
            }

            session()->flash('message', __('app.created_successfully'));
        }

        return redirect()->to(locaRoute('manager.specification-values'), navigate: true);
    }

    public function render()
    {
        $service = app(LocaleCurrencyService::class);
        $languages = $service->getLanguages();
        $specifications = Specification::with('specificationTranslations')->get();

        return view('livewire.manager.specification-value-form', [
            'languages' => $languages,
            'specifications' => $specifications,
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}

