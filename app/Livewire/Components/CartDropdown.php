<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Models\Cart;
use App\Models\CartItem;
use App\Services\LocaleCurrencyService;
use App\Services\PromotionService;

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
        $cart = $this->getCart();
        $service = app(PromotionService::class);
        $user = auth()->user();
        $this->cartItems = $cart ? $cart->cartItems()->with(['product.productTranslations', 'productVariant.specificationValues.specificationValueTranslations', 'productVariant.media'])->get()->map(function ($item) use ($service, $user) {
            $promo = $item->productVariant ? $service->calculateVariantPrice($item->productVariant, $item->qty, $user) : ['final_price' => $item->productVariant->price ?? 0, 'promotion' => null];
            $item->final_price = $promo['final_price'];
            $item->promotion = $promo['promotion'];
            return $item;
        }) : collect();
        $this->cartTotal = $this->cartItems->sum(function ($item) {
            return $item->qty * ($item->final_price ?? $item->productVariant->price ?? $item->product->productVariants->first()->price ?? 0);
        });
    }

    public function getCart()
    {
        if (auth()->check()) {
            return Cart::firstOrCreate(['user_id' => auth()->id()]);
        } else {
            $sessionId = session()->getId();
            return Cart::firstOrCreate(['session_id' => $sessionId]);
        }
    }

    public function addToCart($productId, $variantId, $qty)
    {
        $cart = $this->getCart();
        $item = \App\Models\CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->where('product_variant_id', $variantId)
            ->first();

        if ($item) {
            $item->qty += $qty;
            $item->save();
        } else {
            \App\Models\CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'qty' => $qty,
            ]);
        }
        $this->mount();
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

    public function render()
    {
        $service = app(LocaleCurrencyService::class);

        $lang = $service->getLanguageByCode(session('lang'));
        return view('livewire.components.cart-dropdown', [
            'cartItems' => $this->cartItems,
            'cartTotal' => $this->cartTotal,
            'lang' => $lang,
        ]);
    }
}
