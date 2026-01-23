<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Models\Zone;
use App\Models\ZoneTranslation;
use App\Services\LocaleCurrencyService;
use Livewire\Component;

class ZoneForm extends Component
{
    public ?int $zoneId = null;
    public ?int $countryId = null;
    public ?string $code = null;
    public bool $active = true;
    public string $translationStatus = '';
    public array $translations = [];

    protected array $rules = [
        'countryId' => 'required|exists:countries,id',
        'code' => 'nullable|max:255',
        'active' => 'boolean',
        'translationStatus' => 'required',
        'translations.*.name' => 'required|max:255',
    ];

    protected array $messages = [
        'countryId.required' => '国家不能为空',
        'countryId.exists' => '选择的国家不存在',
        'code.max' => '地区代码不能超过255个字符',
        'translationStatus.required' => '翻译状态不能为空',
        'translations.*.name.required' => '地区名称不能为空',
        'translations.*.name.max' => '地区名称不能超过255个字符',
    ];

    public function mount(?int $id = null): void
    {
        $this->translationStatus = TranslationStatusEnum::NotTranslated->value;

        if ($id) {
            $this->zoneId = $id;
            $zone = Zone::with('zoneTranslations')->findOrFail($id);
            $this->countryId = $zone->country_id;
            $this->code = $zone->code;
            $this->active = $zone->active;
            $this->translationStatus = $zone->translation_status->value;

            // 加载翻译
            $service = app(LocaleCurrencyService::class);
            $languages = $service->getLanguages();
            foreach ($languages as $language) {
                $translation = $zone->zoneTranslations->where('language_id', $language->id)->first();
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
            $this->addError('translations.'.$defaultLanguage->id.'.name', '默认语言的地区名称不能为空');

            return;
        }

        $this->validate();

        $data = [
            'country_id' => $this->countryId,
            'code' => $this->code,
            'active' => $this->active,
            'translation_status' => TranslationStatusEnum::from($this->translationStatus),
        ];

        if ($this->zoneId) {
            $zone = Zone::findOrFail($this->zoneId);
            $zone->update($data);

            // 更新翻译
            foreach ($this->translations as $languageId => $translation) {
                if (! empty($translation['name'])) {
                    ZoneTranslation::updateOrCreate(
                        [
                            'zone_id' => $zone->id,
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
            $zone = Zone::create($data);

            // 创建翻译
            foreach ($this->translations as $languageId => $translation) {
                if (! empty($translation['name'])) {
                    ZoneTranslation::create([
                        'zone_id' => $zone->id,
                        'language_id' => $languageId,
                        'name' => $translation['name'],
                    ]);
                }
            }

            session()->flash('message', __('app.created_successfully'));
        }

        return redirect()->to(locaRoute('manager.zones'), navigate: true);
    }

    public function render()
    {
        $service = app(LocaleCurrencyService::class);
        $languages = $service->getLanguages();
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);
        $countries = \App\Models\Country::with('countryTranslations')->get();

        return view('livewire.manager.zone-form', [
            'languages' => $languages,
            'countries' => $countries,
            'lang' => $lang,
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}
