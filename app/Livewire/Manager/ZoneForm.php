<?php

namespace App\Livewire\Manager;

use App\Enums\TranslationStatusEnum;
use App\Livewire\Traits\HandlesTranslations;
use App\Livewire\Traits\HasNavigationRedirect;
use App\Models\Zone;
use App\Models\ZoneTranslation;
use Livewire\Component;

class ZoneForm extends Component
{
    use HandlesTranslations;
    use HasNavigationRedirect;

    public ?int $zoneId = null;
    public ?int $countryId = null;
    public ?string $code = null;
    public bool $active = true;

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
        $this->initializeTranslationStatus();

        if ($id) {
            $this->zoneId = $id;
            $zone = Zone::with('zoneTranslations')->findOrFail($id);
            $this->countryId = $zone->country_id;
            $this->code = $zone->code;
            $this->active = $zone->active;
            $this->translationStatus = $zone->translation_status->value;

            $this->initializeTranslations($zone, 'zoneTranslations', ['name']);
        } else {
            $this->initializeTranslations(null, 'zoneTranslations', ['name']);
        }
    }

    public function save()
    {
        if (! $this->validateDefaultLanguage('name', '默认语言的地区名称不能为空')) {
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
            $this->saveTranslations($zone, ZoneTranslation::class, 'zone_id', ['name']);
            $this->flashMessage('updated_successfully');
        } else {
            $zone = Zone::create($data);
            $this->saveTranslations($zone, ZoneTranslation::class, 'zone_id', ['name']);
            $this->flashMessage('created_successfully');
        }

        return $this->redirectWithMessage('manager.zones', $this->zoneId ? 'updated_successfully' : 'created_successfully');
    }

    public function render()
    {
        $countries = \App\Models\Country::with('countryTranslations')->get();

        return view('livewire.manager.zone-form', [
            'languages' => $this->getLanguages(),
            'countries' => $countries,
            'lang' => $this->getCurrentLanguage(),
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}
