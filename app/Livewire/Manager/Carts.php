<?php

namespace App\Livewire\Manager;

use App\Models\Cart;
use App\Services\LocaleCurrencyService;
use Livewire\Component;
use Livewire\WithPagination;

class Carts extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $filterUserId = null;
    public string $filterHasItems = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterUserId(): void
    {
        $this->resetPage();
    }

    public function updatingFilterHasItems(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterUserId = null;
        $this->filterHasItems = '';
        $this->resetPage();
    }

    public function deleteCart(int $id): void
    {
        $cart = Cart::findOrFail($id);
        $cart->delete();
        session()->flash('message', __('app.deleted_successfully'));
    }

    public function getCartsProperty()
    {
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);
        $currentCurrencyCode = session('currency') ?? $service->getDefaultCurrencyCode();

        $query = Cart::query()
            ->with([
                'user',
                'cartItems.product.productTranslations',
                'cartItems.product.productVariants',
                'cartItems.productVariant.specificationValues.specificationValueTranslations',
            ])
            ->withCount('cartItems');

        // 搜索：通过用户名称搜索
        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        // 筛选：用户
        if ($this->filterUserId) {
            $query->where('user_id', $this->filterUserId);
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
        if (!$product) {
            return '-';
        }
        $translation = $product->productTranslations->where('language_id', $lang?->id)->first();
        if ($translation && $translation->name) {
            return $translation->name;
        }
        $first = $product->productTranslations->first();
        return $first ? $first->name : $product->id;
    }

    public function getVariantSpecifications($variant, $lang)
    {
        if (!$variant) {
            return __('manager.cart_item.no_variant');
        }
        $specNames = [];
        foreach ($variant->specificationValues as $specValue) {
            $translation = $specValue->specificationValueTranslations->where('language_id', $lang?->id)->first();
            $specNames[] = $translation && $translation->name
                ? $translation->name
                : ($specValue->specificationValueTranslations->first()->name ?? '');
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
        
        if (!$price || $price == 0) {
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
        $service = app(LocaleCurrencyService::class);
        $locale = app()->getLocale();
        $lang = $service->getLanguageByCode($locale);
        $currentCurrencyCode = session('currency') ?? $service->getDefaultCurrencyCode();
        $users = \App\Models\User::orderBy('name')->get();

        return view('livewire.manager.carts', [
            'carts' => $this->carts,
            'lang' => $lang,
            'users' => $users,
            'service' => $service,
            'currentCurrencyCode' => $currentCurrencyCode,
        ])->layout('components.layouts.manager');
    }
}
