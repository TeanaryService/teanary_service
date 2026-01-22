<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Models\Country;
use App\Models\CountryTranslation;
use App\Services\LocaleCurrencyService;
use Livewire\Component;

class CountryForm extends Component
{
    public ?int $countryId = null;
    public ?string $isoCode2 = null;
    public ?string $isoCode3 = null;
    public bool $postcodeRequired = false;
    public bool $active = true;
    public string $translationStatus = '';
    public array $translations = [];

    protected array $rules = [
        'isoCode2' => 'nullable|max:2',
        'isoCode3' => 'nullable|max:3',
        'postcodeRequired' => 'boolean',
        'active' => 'boolean',
        'translationStatus' => 'required',
        'translations.*.name' => 'required|max:255',
    ];

    protected array $messages = [
        'isoCode2.max' => 'ISO代码2不能超过2个字符',
        'isoCode3.max' => 'ISO代码3不能超过3个字符',
        'translationStatus.required' => '翻译状态不能为空',
        'translations.*.name.required' => '国家名称不能为空',
        'translations.*.name.max' => '国家名称不能超过255个字符',
    ];

    public function mount(?int $id = null): void
    {
        $this->translationStatus = TranslationStatusEnum::NotTranslated->value;

        if ($id) {
            $this->countryId = $id;
            $country = Country::with('countryTranslations')->findOrFail($id);
            $this->isoCode2 = $country->iso_code_2;
            $this->isoCode3 = $country->iso_code_3;
            $this->postcodeRequired = $country->postcode_required;
            $this->active = $country->active;
            $this->translationStatus = $country->translation_status->value;

            // 加载翻译
            $service = app(LocaleCurrencyService::class);
            $languages = $service->getLanguages();
            foreach ($languages as $language) {
                $translation = $country->countryTranslations->where('language_id', $language->id)->first();
                $this->translations[$language->id] = [
                    'name' => $translation ? $translation->name : '',
                ];
            }
        } else {
            // 初始化翻译数组
            $service = app(LocaleCurrencyService::class);
            $languages = $service->getLanguages();
            foreach ($languages as $language) {
                $this->translations[$language->id] = ['name' => ''];
            }
        }
    }

    public function save()
    {
        // 验证默认语言必须填写
        $service = app(LocaleCurrencyService::class);
        $defaultLanguage = $service->getDefaultLanguage();
        if ($defaultLanguage && empty($this->translations[$defaultLanguage->id]['name'])) {
            $this->addError('translations.' . $defaultLanguage->id . '.name', '默认语言的国家名称不能为空');
            return;
        }

        $this->validate();

        $data = [
            'iso_code_2' => $this->isoCode2 ? strtoupper($this->isoCode2) : null,
            'iso_code_3' => $this->isoCode3 ? strtoupper($this->isoCode3) : null,
            'postcode_required' => $this->postcodeRequired,
            'active' => $this->active,
            'translation_status' => TranslationStatusEnum::from($this->translationStatus),
        ];

        if ($this->countryId) {
            $country = Country::findOrFail($this->countryId);
            $country->update($data);

            // 更新翻译
            foreach ($this->translations as $languageId => $translation) {
                if (!empty($translation['name'])) {
                    CountryTranslation::updateOrCreate(
                        [
                            'country_id' => $country->id,
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
            $country = Country::create($data);

            // 创建翻译
            foreach ($this->translations as $languageId => $translation) {
                if (!empty($translation['name'])) {
                    CountryTranslation::create([
                        'country_id' => $country->id,
                        'language_id' => $languageId,
                        'name' => $translation['name'],
                    ]);
                }
            }

            session()->flash('message', __('app.created_successfully'));
        }

        return redirect()->to(locaRoute('manager.countries'), navigate: true);
    }

    public function render()
    {
        $service = app(LocaleCurrencyService::class);
        $languages = $service->getLanguages();

        return view('livewire.manager.country-form', [
            'languages' => $languages,
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}
