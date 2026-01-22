<?php

namespace App\Livewire\Manager;

use App\Enums\PromotionTypeEnum;
use App\Enums\TranslationStatusEnum;
use App\Models\Promotion;
use App\Models\PromotionTranslation;
use App\Services\LocaleCurrencyService;
use Livewire\Component;

class PromotionForm extends Component
{
    public ?int $promotionId = null;
    public string $type = '';
    public string $translationStatus = '';
    public ?string $startsAt = null;
    public ?string $endsAt = null;
    public bool $active = true;
    public array $translations = [];

    protected array $rules = [
        'type' => 'required',
        'translationStatus' => 'required',
        'startsAt' => 'nullable|date',
        'endsAt' => 'nullable|date|after_or_equal:startsAt',
        'active' => 'boolean',
        'translations.*.name' => 'required|max:255',
        'translations.*.description' => 'nullable',
    ];

    protected array $messages = [
        'type.required' => '促销类型不能为空',
        'translationStatus.required' => '翻译状态不能为空',
        'translations.*.name.required' => '促销名称不能为空',
        'translations.*.name.max' => '促销名称不能超过255个字符',
        'endsAt.after_or_equal' => '结束时间必须晚于或等于开始时间',
    ];

    public function mount(?int $id = null): void
    {
        $this->translationStatus = TranslationStatusEnum::NotTranslated->value;
        $this->active = true;

        $service = app(LocaleCurrencyService::class);
        $languages = $service->getLanguages();

        if ($id) {
            $this->promotionId = $id;
            $promotion = Promotion::with('promotionTranslations')->findOrFail($id);
            $this->type = $promotion->type->value;
            $this->translationStatus = $promotion->translation_status->value;
            $this->startsAt = $promotion->starts_at?->format('Y-m-d\TH:i');
            $this->endsAt = $promotion->ends_at?->format('Y-m-d\TH:i');
            $this->active = $promotion->active;

            foreach ($languages as $language) {
                $translation = $promotion->promotionTranslations->where('language_id', $language->id)->first();
                $this->translations[$language->id] = [
                    'name' => $translation ? $translation->name : '',
                    'description' => $translation ? $translation->description : '',
                ];
            }
        } else {
            foreach ($languages as $language) {
                $this->translations[$language->id] = [
                    'name' => '',
                    'description' => '',
                ];
            }
        }
    }

    public function save()
    {
        $service = app(LocaleCurrencyService::class);
        $defaultLanguage = $service->getLanguages()->firstWhere('default', true);
        if ($defaultLanguage && empty($this->translations[$defaultLanguage->id]['name'])) {
            $this->addError('translations.' . $defaultLanguage->id . '.name', '默认语言的促销名称不能为空');
            return;
        }

        $this->validate();

        $data = [
            'type' => PromotionTypeEnum::from($this->type),
            'translation_status' => TranslationStatusEnum::from($this->translationStatus),
            'starts_at' => $this->startsAt ? new \Carbon\Carbon($this->startsAt) : null,
            'ends_at' => $this->endsAt ? new \Carbon\Carbon($this->endsAt) : null,
            'active' => $this->active,
        ];

        if ($this->promotionId) {
            $promotion = Promotion::findOrFail($this->promotionId);
            $promotion->update($data);

            foreach ($this->translations as $languageId => $translation) {
                if (! empty($translation['name'])) {
                    PromotionTranslation::updateOrCreate(
                        [
                            'promotion_id' => $promotion->id,
                            'language_id' => $languageId,
                        ],
                        [
                            'name' => $translation['name'],
                            'description' => $translation['description'] ?? null,
                        ]
                    );
                }
            }

            session()->flash('message', __('app.updated_successfully'));
        } else {
            $promotion = Promotion::create($data);

            foreach ($this->translations as $languageId => $translation) {
                if (! empty($translation['name'])) {
                    PromotionTranslation::create([
                        'promotion_id' => $promotion->id,
                        'language_id' => $languageId,
                        'name' => $translation['name'],
                        'description' => $translation['description'] ?? null,
                    ]);
                }
            }

            session()->flash('message', __('app.created_successfully'));
        }

        return redirect()->to(locaRoute('manager.promotions'), navigate: true);
    }

    public function render()
    {
        $service = app(LocaleCurrencyService::class);
        $languages = $service->getLanguages();

        return view('livewire.manager.promotion-form', [
            'languages' => $languages,
            'typeOptions' => PromotionTypeEnum::options(),
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}

