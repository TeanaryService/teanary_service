<?php

namespace App\Livewire\Components;

use App\Livewire\Traits\HasTranslatedNames;
use App\Livewire\Traits\UsesLocaleCurrency;
use App\Models\CartItem;
use App\Services\CartService;
use App\Services\PromotionService;
use Livewire\Component;

class CartDropdown extends Component
{
    use HasTranslatedNames;
    use UsesLocaleCurrency;
    public $cartItems;

    public $cartTotal = 0;

    protected $listeners = [
        'cart:add' => 'addToCart',
        'cart:refresh' => 'mount',
    ];

    public function mount()
    {
        $cart = app(CartService::class)->getCart();
        $service = app(PromotionService::class);
        $user = auth()->user();
        $this->cartItems = $cart ? $cart->cartItems()->with(['product.productTranslations', 'productVariant.specificationValues.specificationValueTranslations', 'productVariant.media'])->get()->map(function ($item) use ($service, $user) {
            if ($item->productVariant) {
                $promo = $service->calculateVariantPrice($item->productVariant, $item->qty, $user);
                $item->final_price = (float) ($promo['final_price'] ?? $item->productVariant->price ?? 0);
                $item->promotion = $promo['promotion'] ?? null;
            } else {
                $item->final_price = 0.0;
                $item->promotion = null;
            }

            return $item;
        }) : collect();
        $this->cartTotal = $this->cartItems->sum(function ($item) {
            $price = $item->final_price ?? 0;
            if ($price <= 0 && $item->productVariant) {
                $price = $item->productVariant->price ?? 0;
            }
            if ($price <= 0 && $item->product && $item->product->productVariants->isNotEmpty()) {
                $price = $item->product->productVariants->first()->price ?? 0;
            }

            return $item->qty * $price;
        });
    }

    public function addToCart($productId, $variantId, $qty)
    {
        $cart = app(CartService::class)->getOrCreateCart();
        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->where('product_variant_id', $variantId)
            ->first();

        if ($item) {
            $item->qty += $qty;
            $item->save();
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'qty' => $qty,
            ]);
        }
        $this->dispatch('flash-message', type: 'success', message: __('app.add_cart_success'));
        $this->mount();
    }

    public function updateQty($itemId, $qty)
    {
        $cartItem = CartItem::find($itemId);
        if ($cartItem && $qty > 0) {
            $cartItem->qty = $qty;
            $cartItem->save();
            $this->dispatch('flash-message', type: 'success', message: __('app.edit_cart_success'));
        } elseif ($cartItem && $qty <= 0) {
            $cartItem->delete();
            $this->dispatch('flash-message', type: 'success', message: __('app.delete_cart_success'));
        }

        $this->mount();
    }

    public function removeItem($itemId)
    {
        $cartItem = CartItem::find($itemId);
        if ($cartItem) {
            $cartItem->delete();
            $this->dispatch('flash-message', type: 'success', message: __('app.delete_cart_success'));
        }
        $this->mount();
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
        $specs = $variant
            ? $variant->specificationValues
                ->map(function ($sv) use ($lang) {
                    return $this->translatedField($sv->specificationValueTranslations, $lang, 'name', (string) $sv->id);
                })
                ->implode(' / ')
            : '';
        $price = $variant && $variant->price
            ? $currencyService->convertWithSymbol($variant->price, $currencyCode)
            : '';
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

    public function render()
    {
        return view('livewire.components.cart-dropdown', [
            'cartItems' => $this->cartItems,
            'cartTotal' => $this->cartTotal,
            'lang' => $this->getCurrentLanguage(),
            'currencyService' => $this->getLocaleService(),
            'currencyCode' => $this->getCurrentCurrencyCode(),
        ]);
    }
}
