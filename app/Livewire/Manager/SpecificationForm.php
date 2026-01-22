<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Models\Specification;
use App\Models\SpecificationTranslation;
use App\Services\LocaleCurrencyService;
use Livewire\Component;

class SpecificationForm extends Component
{
    public ?int $specificationId = null;
    public string $translationStatus = '';
    public array $translations = [];

    protected array $rules = [
        'translationStatus' => 'required',
        'translations.*.name' => 'required|max:255',
    ];

    protected array $messages = [
        'translationStatus.required' => '翻译状态不能为空',
        'translations.*.name.required' => '规格名称不能为空',
        'translations.*.name.max' => '规格名称不能超过255个字符',
    ];

    public function mount(?int $id = null): void
    {
        $this->translationStatus = TranslationStatusEnum::NotTranslated->value;

        $service = app(LocaleCurrencyService::class);
        $languages = $service->getLanguages();

        if ($id) {
            $this->specificationId = $id;
            $spec = Specification::with('specificationTranslations')->findOrFail($id);
            $this->translationStatus = $spec->translation_status->value;

            foreach ($languages as $language) {
                $translation = $spec->specificationTranslations->where('language_id', $language->id)->first();
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
            $this->addError('translations.' . $defaultLanguage->id . '.name', '默认语言的规格名称不能为空');
            return;
        }

        $this->validate();

        $data = [
            'translation_status' => TranslationStatusEnum::from($this->translationStatus),
        ];

        if ($this->specificationId) {
            $spec = Specification::findOrFail($this->specificationId);
            $spec->update($data);

            foreach ($this->translations as $languageId => $translation) {
                if (! empty($translation['name'])) {
                    SpecificationTranslation::updateOrCreate(
                        [
                            'specification_id' => $spec->id,
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
            $spec = Specification::create($data);

            foreach ($this->translations as $languageId => $translation) {
                if (! empty($translation['name'])) {
                    SpecificationTranslation::create([
                        'specification_id' => $spec->id,
                        'language_id' => $languageId,
                        'name' => $translation['name'],
                    ]);
                }
            }

            session()->flash('message', __('app.created_successfully'));
        }

        return redirect()->to(locaRoute('manager.specifications'), navigate: true);
    }

    public function render()
    {
        $service = app(LocaleCurrencyService::class);
        $languages = $service->getLanguages();

        return view('livewire.manager.specification-form', [
            'languages' => $languages,
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}

