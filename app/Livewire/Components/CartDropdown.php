<?php

namespace App\Livewire\Components;

use App\Models\CartItem;
use App\Services\CartService;
use App\Services\LocaleCurrencyService;
use App\Services\PromotionService;
use Livewire\Component;

class CartDropdown extends Component
{
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
        session()->flash('success', __('app.add_cart_success'));
        $this->mount();
    }

    public function updateQty($itemId, $qty)
    {
        $cartItem = CartItem::find($itemId);
        if ($cartItem && $qty > 0) {
            $cartItem->qty = $qty;
            $cartItem->save();
            session()->flash('success', __('app.edit_cart_success'));
        } elseif ($cartItem && $qty <= 0) {
            $cartItem->delete();
            session()->flash('success', __('app.delete_cart_success'));
        }

        $this->mount();
    }

    public function removeItem($itemId)
    {
        $cartItem = CartItem::find($itemId);
        if ($cartItem) {
            $cartItem->delete();
            session()->flash('success', __('app.delete_cart_success'));
        }
        $this->mount();
    }

    /**
     * 获取购物车项显示数据
     */
    protected function getCartItemDisplayData($item, $lang): array
    {
        $currencyService = app(LocaleCurrencyService::class);
        $currencyCode = session('currency');
        
        $product = $item->product;
        $variant = $item->productVariant;
        $translation = $product->productTranslations->where('language_id', $lang?->id)->first();
        $name = $translation && $translation->name
            ? $translation->name
            : $product->productTranslations->first()->name ?? $product->slug;
        $image = $variant
            ? ($variant->getFirstMediaUrl('image', 'thumb') ?: $product->getFirstMediaUrl('images', 'thumb') ?: asset('logo.svg'))
            : ($product->getFirstMediaUrl('images', 'thumb') ?: asset('logo.svg'));
        $specs = $variant
            ? $variant->specificationValues
                ->map(function ($sv) use ($lang) {
                    $trans = $sv->specificationValueTranslations->where('language_id', $lang?->id)->first();
                    return $trans && $trans->name ? $trans->name : $sv->id;
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
        $service = app(LocaleCurrencyService::class);
        $lang = $service->getLanguageByCode(session('lang'));
        $currencyService = app(LocaleCurrencyService::class);
        $currencyCode = session('currency');

        return view('livewire.components.cart-dropdown', [
            'cartItems' => $this->cartItems,
            'cartTotal' => $this->cartTotal,
            'lang' => $lang,
            'currencyService' => $currencyService,
            'currencyCode' => $currencyCode,
        ]);
    }
}
