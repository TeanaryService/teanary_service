<?php

namespace App\Livewire;

use App\Livewire\Traits\HasTranslatedNames;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\CartItem;
use App\Services\CartService;
use Livewire\Component;

class Cart extends Component
{
    use HasTranslatedNames;
    use UsesLocaleCurrency;
    public $cartItems;

    public $selected = [];

    public $selectAll = true;

    public $total = 0;

    public function mount()
    {
        $cart = app(CartService::class)->getCart();
        $promoService = app(\App\Services\PromotionService::class);
        $user = auth()->user();
        $this->cartItems = $cart
            ? $cart->cartItems()->with([
                'product.productTranslations',
                'productVariant.specificationValues.specificationValueTranslations',
                'productVariant.media',
            ])->get()->map(function ($item) use ($promoService, $user) {
                if ($item->productVariant) {
                    $promo = $promoService->calculateVariantPrice($item->productVariant, $item->qty, $user);
                    $item->final_price = (float) ($promo['final_price'] ?? $item->productVariant->price ?? 0);
                    $item->promotion = $promo['promotion'] ?? null;
                } else {
                    $item->final_price = 0.0;
                    $item->promotion = null;
                }

                return $item;
            })
            : collect();

        // if ($this->cartItems->isEmpty()) {
        //     return redirect()->route('home', ['locale' => app()->getLocale()]);
        // }

        $this->selected = $this->cartItems->pluck('id')->toArray();
        $this->calcTotal();
    }

    public function updateQty($itemId, $qty)
    {
        $cartItem = CartItem::find($itemId);
        if ($cartItem && $qty > 0) {
            $cartItem->qty = $qty;
            $cartItem->save();
        } elseif ($cartItem && $qty <= 0) {
            $cartItem->delete();
        }
        $this->mount();
    }

    public function removeItem($itemId)
    {
        $cartItem = CartItem::find($itemId);
        if ($cartItem) {
            $cartItem->delete();
        }
        $this->mount();
    }

    public function toggleSelectAll()
    {
        if (count($this->selected) < count($this->cartItems)) {
            $this->selected = $this->cartItems->pluck('id')->toArray();
            $this->selectAll = true;
        } else {
            $this->selected = [];
            $this->selectAll = false;
        }
        $this->calcTotal();
    }

    public function toggleInverse()
    {
        $allIds = $this->cartItems->pluck('id')->toArray();
        $this->selected = array_diff($allIds, $this->selected);
        $this->calcTotal();
    }

    public function updatedSelected()
    {
        $this->calcTotal();
    }

    public function calcTotal()
    {
        $this->total = $this->cartItems->whereIn('id', $this->selected)->sum(function ($item) {
            return $item->qty * ($item->final_price ?? $item->productVariant->price ?? 0);
        });
    }

    public function checkout()
    {
        $locale = app()->getLocale();

        $selectedItems = $this->cartItems->whereIn('id', $this->selected)
            ->map(function ($item) {
                return [
                    'cart_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'qty' => $item->qty,
                ];
            })->toArray();

        // 只保留属于当前仓库的商品
        $warehouseId = session('warehouse_id');
        if ($warehouseId) {
            $allowedProductIds = \App\Models\Product::whereHas('warehouses', fn ($q) => $q->where('warehouses.id', $warehouseId))
                ->pluck('id')
                ->toArray();
            $selectedItems = array_values(array_filter($selectedItems, fn ($item) => in_array($item['product_id'], $allowedProductIds)));
        }

        if (empty($selectedItems)) {
            $this->dispatch('flash-message', type: 'warning', message: __('app.warehouse_checkout_empty'));

            return;
        }

        session()->put('checkout_items', $selectedItems);

        return $this->redirect(route('checkout', ['locale' => $locale]), navigate: true);
    }

    /**
     * 获取购物车项显示数据.
     */
    protected function getCartItemDisplayData($item, $lang): array
    {
        $currencyService = $this->getLocaleService();
        $currencyCode = $this->getCurrentCurrencyCode();

        $product = $item->product;
        $variant = $item->productVariant;
        $name = $this->translatedField($product->productTranslations, $lang, 'name', $product->slug);
        $image = $variant
            ? (first_media_url($variant, 'image', 'thumb', asset('logo.svg'))
                ?: first_media_url($product, 'images', 'thumb', asset('logo.svg'))
                ?: asset('logo.svg'))
            : (first_media_url($product, 'images', 'thumb', asset('logo.svg')) ?: asset('logo.svg'));
        $specs = $variant ? $variant->specificationValues->map(function ($sv) use ($lang) {
            return $this->translatedField($sv->specificationValueTranslations, $lang, 'name', (string) $sv->id);
        })->implode(' / ') : '';
        $price = $variant && $variant->price ? $currencyService->convertWithSymbol($variant->price, $currencyCode) : '';
        $finalPrice = $item->final_price ?? ($variant && $variant->price ? $variant->price : 0);
        $promotion = $item->promotion ?? null;

        return [
            'name' => $name,
            'image' => $image,
            'specs' => $specs,
            'price' => $price,
            'finalPrice' => $finalPrice,
            'promotion' => $promotion,
        ];
    }

    /**
     * 获取促销折扣文本.
     */
    protected function getPromotionDiscountText($promotion, $rule): array
    {
        // 处理 discount_type：可能是枚举对象、数组或字符串
        $discountType = null;
        if (is_array($rule)) {
            $discountType = $rule['discount_type'] ?? null;
            // 如果是枚举对象，获取其值
            if (is_object($discountType) && method_exists($discountType, 'value')) {
                $discountType = $discountType->value;
            } elseif (is_object($discountType)) {
                $discountType = (string) $discountType;
            }
        } elseif (is_object($rule)) {
            $discountType = $rule->discount_type ?? null;
            if (is_object($discountType) && method_exists($discountType, 'value')) {
                $discountType = $discountType->value;
            } elseif (is_object($discountType)) {
                $discountType = (string) $discountType;
            }
        }

        // 处理 discount_value
        $discountValue = is_array($rule) ? ($rule['discount_value'] ?? null) : (is_object($rule) ? ($rule->discount_value ?? null) : null);
        if (is_object($discountValue)) {
            $discountValue = (string) $discountValue;
        }

        return [
            'discountType' => $discountType,
            'discountValue' => $discountValue,
        ];
    }

    /**
     * 规范化促销描述.
     */
    protected function normalizePromotionDescription($description): string
    {
        if (is_array($description)) {
            return json_encode($description, JSON_UNESCAPED_UNICODE);
        } elseif (! is_string($description)) {
            return (string) $description;
        }

        return $description;
    }

    public function render()
    {
        return view('livewire.cart', [
            'cartItems' => $this->cartItems,
            'selected' => $this->selected,
            'total' => $this->total,
            'lang' => $this->getCurrentLanguage(),
            'currencyService' => $this->getLocaleService(),
            'currencyCode' => $this->getCurrentCurrencyCode(),
        ]);
    }
}
