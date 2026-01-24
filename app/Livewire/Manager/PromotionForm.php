<?php

namespace App\Livewire\Manager;

use App\Enums\PromotionTypeEnum;
use App\Enums\TranslationStatusEnum;
use App\Livewire\Traits\HandlesTranslations;
use App\Livewire\Traits\HasNavigationRedirect;
use App\Models\Promotion;
use App\Models\PromotionTranslation;
use Livewire\Component;

class PromotionForm extends Component
{
    use HandlesTranslations;
    use HasNavigationRedirect;

    public ?int $promotionId = null;
    public string $type = '';
    public ?string $startsAt = null;
    public ?string $endsAt = null;
    public bool $active = true;

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
        $this->initializeTranslationStatus();
        $this->active = true;

        if ($id) {
            $this->promotionId = $id;
            $promotion = Promotion::with('promotionTranslations')->findOrFail($id);
            $this->type = $promotion->type->value;
            $this->translationStatus = $promotion->translation_status->value;
            $this->startsAt = $promotion->starts_at?->format('Y-m-d\TH:i');
            $this->endsAt = $promotion->ends_at?->format('Y-m-d\TH:i');
            $this->active = $promotion->active;

            $this->initializeTranslations($promotion, 'promotionTranslations', ['name', 'description']);
        } else {
            $this->initializeTranslations(null, 'promotionTranslations', ['name', 'description']);
        }
    }

    public function save()
    {
        if (! $this->validateDefaultLanguage('name', '默认语言的促销名称不能为空')) {
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
            $this->saveTranslations($promotion, PromotionTranslation::class, 'promotion_id', ['name', 'description']);
            $this->flashMessage('updated_successfully');
        } else {
            $promotion = Promotion::create($data);
            $this->saveTranslations($promotion, PromotionTranslation::class, 'promotion_id', ['name', 'description']);
            $this->flashMessage('created_successfully');
        }

        return $this->redirectWithMessage('manager.promotions', $this->promotionId ? 'updated_successfully' : 'created_successfully');
    }

    public function render()
    {
        return view('livewire.manager.promotion-form', [
            'languages' => $this->getLanguages(),
            'typeOptions' => PromotionTypeEnum::options(),
            'translationStatusOptions' => TranslationStatusEnum::options(),
        ])->layout('components.layouts.manager');
    }
}
