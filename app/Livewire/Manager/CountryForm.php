<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Livewire\Traits\HandlesTranslations;
use App\Livewire\Traits\HasNavigationRedirect;
use App\Models\Country;
use App\Models\CountryTranslation;
use Livewire\Component;

class CountryForm extends Component
{
    use HandlesTranslations;
    use HasNavigationRedirect;

    public ?int $countryId = null;
    public ?string $isoCode2 = null;
    public ?string $isoCode3 = null;
    public bool $postcodeRequired = false;
    public bool $active = true;

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
        $this->initializeTranslationStatus();

        if ($id) {
            $this->countryId = $id;
            $country = Country::with('countryTranslations')->findOrFail($id);
            $this->isoCode2 = $country->iso_code_2;
            $this->isoCode3 = $country->iso_code_3;
            $this->postcodeRequired = $country->postcode_required;
            $this->active = $country->active;
            $this->translationStatus = $country->translation_status->value;

            $this->initializeTranslations($country, 'countryTranslations', ['name']);
        } else {
            $this->initializeTranslations(null, 'countryTranslations', ['name']);
        }
    }

    public function save()
    {
        if (! $this->validateDefaultLanguage('name', '默认语言的国家名称不能为空')) {
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
            $this->saveTranslations($country, CountryTranslation::class, 'country_id', ['name']);
            $this->flashMessage('updated_successfully');
        } else {
            $country = Country::create($data);
            $this->saveTranslations($country, CountryTranslation::class, 'country_id', ['name']);
            $this->flashMessage('created_successfully');
        }

        return $this->redirectWithMessage('manager.countries', $this->countryId ? 'updated_successfully' : 'created_successfully');
    }

    public function render()
    {
        return view('livewire.manager.country-form', [
            'languages' => $this->getLanguages(),
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}
