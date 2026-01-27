<?php

namespace App\Livewire\Manager;

use App\Enums\PromotionConditionTypeEnum;
use App\Enums\PromotionDiscountTypeEnum;
use App\Livewire\Traits\HasTranslatedNames;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\PromotionRule;
use App\Models\UserGroup;
use Livewire\Component;

class PromotionDetail extends Component
{
    use HasTranslatedNames;
    use UsesLocaleCurrency;

    public int $promotionId;
    public Promotion $promotion;

    // 促销规则
    public array $rules = [];

    // 用户组
    public array $selectedUserGroupIds = [];

    // 商品变体
    public array $selectedProductVariantIds = [];
    public string $productSearch = '';

    public function mount(int $id): void
    {
        $this->promotionId = $id;
        $this->loadPromotion();
    }

    protected function loadPromotion(): void
    {
        $this->promotion = Promotion::with([
            'promotionRules',
            'userGroups',
            'productVariants.product.productTranslations',
            'promotionTranslations',
        ])->findOrFail($this->promotionId);

        // 加载规则
        $this->rules = [];
        foreach ($this->promotion->promotionRules as $rule) {
            $this->rules[] = [
                'id' => $rule->id,
                'condition_type' => $rule->condition_type->value,
                'condition_value' => $rule->condition_value,
                'discount_type' => $rule->discount_type->value,
                'discount_value' => $rule->discount_value,
            ];
        }

        // 加载用户组
        $this->selectedUserGroupIds = $this->promotion->userGroups->pluck('id')->toArray();

        // 加载商品变体
        $this->selectedProductVariantIds = $this->promotion->productVariants->pluck('id')->toArray();
    }

    public function addRule(): void
    {
        $this->rules[] = [
            'id' => null,
            'condition_type' => PromotionConditionTypeEnum::OrderTotalMin->value,
            'condition_value' => 0,
            'discount_type' => PromotionDiscountTypeEnum::Percentage->value,
            'discount_value' => 0,
        ];
    }

    public function removeRule(int $index): void
    {
        if (isset($this->rules[$index])) {
            $rule = $this->rules[$index];
            // 如果规则已保存，从数据库删除
            if (! empty($rule['id'])) {
                PromotionRule::find($rule['id'])?->delete();
            }
            unset($this->rules[$index]);
            $this->rules = array_values($this->rules);
        }
    }

    public function saveRules(): void
    {
        // 删除所有现有规则
        $this->promotion->promotionRules()->delete();

        // 创建新规则
        foreach ($this->rules as $rule) {
            PromotionRule::create([
                'promotion_id' => $this->promotionId,
                'condition_type' => PromotionConditionTypeEnum::from($rule['condition_type']),
                'condition_value' => $rule['condition_value'],
                'discount_type' => PromotionDiscountTypeEnum::from($rule['discount_type']),
                'discount_value' => $rule['discount_value'],
            ]);
        }

        $this->dispatch('flash-message', type: 'success', message: __('app.saved_successfully'));
        $this->loadPromotion();
    }

    public function saveUserGroups(): void
    {
        $this->promotion->userGroups()->sync($this->selectedUserGroupIds);
        $this->dispatch('flash-message', type: 'success', message: __('app.saved_successfully'));
        $this->loadPromotion();
    }

    public function saveProductVariants(): void
    {
        $this->promotion->syncProductVariants($this->selectedProductVariantIds);
        $this->dispatch('flash-message', type: 'success', message: __('app.saved_successfully'));
        $this->loadPromotion();
    }

    public function getUserGroupsProperty()
    {
        $lang = $this->getCurrentLanguage();

        return UserGroup::with('userGroupTranslations')
            ->get()
            ->map(function ($group) use ($lang) {
                $trans = $group->userGroupTranslations->where('language_id', $lang->id)->first();
                $name = $trans?->name ?? $group->userGroupTranslations->first()?->name ?? "ID: {$group->id}";

                return [
                    'id' => $group->id,
                    'name' => $name,
                ];
            })
            ->sortBy('name');
    }

    public function getAvailableProductVariantsProperty()
    {
        $lang = $this->getCurrentLanguage();
        $query = ProductVariant::with([
            'product.productTranslations',
            'specificationValues.specificationValueTranslations',
        ]);

        if ($this->productSearch) {
            $query->where(function ($q) {
                $q->whereHas('product.productTranslations', function ($subQ) {
                    $subQ->where('name', 'like', '%'.$this->productSearch.'%');
                })->orWhere('sku', 'like', '%'.$this->productSearch.'%');
            });
        }

        return $query->limit(50)->get()->map(function ($variant) use ($lang) {
            $productTrans = $variant->product->productTranslations->where('language_id', $lang->id)->first();
            $productName = $productTrans?->name ?? $variant->product->productTranslations->first()?->name ?? $variant->product->slug;

            $specs = [];
            foreach ($variant->specificationValues as $sv) {
                $svTrans = $sv->specificationValueTranslations->where('language_id', $lang->id)->first();
                $specName = $svTrans?->name ?? $sv->specificationValueTranslations->first()?->name ?? '';
                if ($specName) {
                    $specs[] = $specName;
                }
            }

            return [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'product_name' => $productName,
                'specs' => implode(', ', $specs),
                'price' => $variant->price,
            ];
        });
    }

    public function render()
    {
        $lang = $this->getCurrentLanguage();
        $promotionName = $this->translatedField(
            $this->promotion->promotionTranslations,
            $lang,
            'name',
            __('manager.promotion.unnamed')
        );

        return view('livewire.manager.promotion-detail', [
            'promotionName' => $promotionName,
            'lang' => $lang,
            'conditionTypeOptions' => PromotionConditionTypeEnum::options(),
            'discountTypeOptions' => PromotionDiscountTypeEnum::options(),
            'userGroups' => $this->userGroups,
            'availableProductVariants' => $this->availableProductVariants,
        ])->layout('components.layouts.manager');
    }
}
