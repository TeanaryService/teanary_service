<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Models\Cart;
use App\Models\ProductVariant;
use App\Models\CartItem;
use App\Services\LocaleCurrencyService;

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
        $this->cartItems = $cart ? $cart->cartItems()->with(['product.productTranslations', 'productVariant.specificationValues.specificationValueTranslations', 'productVariant.media'])->get() : collect();
        $this->cartTotal = $this->cartItems->sum(function ($item) {
            return $item->qty * ($item->productVariant->price ?? $item->product->productVariants->first()->price ?? 0);
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
        $lang = app(LocaleCurrencyService::class)->getLanguageByCode(session('lang'));
        $currencyService = app(LocaleCurrencyService::class);
        $currencyCode = session('currency_code', 'CNY');
        return view('livewire.components.cart-dropdown', [
            'cartItems' => $this->cartItems,
            'cartTotal' => $currencyService->convertWithSymbol($this->cartTotal, $currencyCode),
            'lang' => $lang,
        ]);
    }
}
