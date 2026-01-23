<?php

namespace App\Livewire\Manager;

use App\Livewire\Traits\HasDeleteAction;
use App\Livewire\Traits\HasSearchAndFilters;
use App\Livewire\Traits\HasTranslatedNames;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\Cart;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Carts extends Component
{
    use HasDeleteAction;
    use HasSearchAndFilters;
    use HasTranslatedNames;
    use UsesLocaleCurrency;

    public string $filterHasItems = '';

    public function updatingFilterHasItems(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterHasItems = '';
        $this->resetPage();
    }

    public function deleteCart(int $id): void
    {
        $this->deleteModel(Cart::class, $id);
    }

    #[Computed]
    public function carts()
    {
        $lang = $this->getCurrentLanguage();
        $currentCurrencyCode = $this->getCurrentCurrencyCode();
        $service = $this->getLocaleService();

        $query = Cart::query()
            ->with([
                'user',
                'cartItems.product.productTranslations',
                'cartItems.product.media',
                'cartItems.product.productVariants',
                'cartItems.productVariant.specificationValues.specificationValueTranslations',
                'cartItems.productVariant.media',
            ])
            ->withCount('cartItems');

        // 搜索：通过用户ID、用户名、商品名搜索
        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                // 搜索用户ID（如果是数字）
                if (is_numeric($search)) {
                    $q->where('user_id', $search);
                }
                // 搜索用户名或邮箱
                $q->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
                // 搜索商品名
                $q->orWhereHas('cartItems.product.productTranslations', function ($productQuery) use ($search) {
                    $productQuery->where('name', 'like', '%'.$search.'%');
                });
            });
        }

        // 筛选：是否有商品
        if ($this->filterHasItems === '1') {
            $query->has('cartItems');
        } elseif ($this->filterHasItems === '0') {
            $query->doesntHave('cartItems');
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function getProductName($product, $lang)
    {
        if (! $product) {
            return '-';
        }

        return $this->translatedField($product->productTranslations, $lang, 'name', (string) $product->id);
    }

    public function getVariantSpecifications($variant, $lang)
    {
        if (! $variant) {
            return __('manager.cart_item.no_variant');
        }
        $specNames = [];
        foreach ($variant->specificationValues as $specValue) {
            $specNames[] = $this->translatedField($specValue->specificationValueTranslations, $lang, 'name', '');
        }

        return implode(' / ', array_filter($specNames)) ?: ($variant->sku ?? $variant->id);
    }

    public function getItemPrice($item, $service, $currentCurrencyCode)
    {
        $price = null;
        if ($item->productVariant && $item->productVariant->price) {
            $price = $item->productVariant->price;
        } elseif ($item->product && $item->product->relationLoaded('productVariants')) {
            $variant = $item->product->productVariants->first();
            $price = $variant ? $variant->price : null;
        }

        if (! $price || $price == 0) {
            return '-';
        }

        return $service->convertWithSymbol($price, $currentCurrencyCode);
    }

    public function getItemSubtotal($item, $service, $currentCurrencyCode)
    {
        $price = null;
        if ($item->productVariant && $item->productVariant->price) {
            $price = $item->productVariant->price;
        } elseif ($item->product && $item->product->relationLoaded('productVariants')) {
            $variant = $item->product->productVariants->first();
            $price = $variant ? $variant->price : null;
        }

        $subtotal = ($price ?? 0) * ($item->qty ?? 0);
        if ($subtotal == 0) {
            return '-';
        }

        return $service->convertWithSymbol($subtotal, $currentCurrencyCode);
    }

    public function render()
    {
        $lang = $this->getCurrentLanguage();
        $currentCurrencyCode = $this->getCurrentCurrencyCode();
        $service = $this->getLocaleService();

        return view('livewire.manager.carts', [
            'carts' => $this->carts,
            'lang' => $lang,
            'service' => $service,
            'currentCurrencyCode' => $currentCurrencyCode,
        ])->layout('components.layouts.manager');
    }
}
