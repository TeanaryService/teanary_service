<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Models\CartItem;
use App\Services\LocaleCurrencyService;
use App\Services\PromotionService;
use App\Services\CartService;

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
            $promo = $item->productVariant ? $service->calculateVariantPrice($item->productVariant, $item->qty, $user) : ['final_price' => $item->productVariant->price ?? 0, 'promotion' => null];
            $item->final_price = $promo['final_price'];
            $item->promotion = $promo['promotion'];
            return $item;
        }) : collect();
        $this->cartTotal = $this->cartItems->sum(function ($item) {
            return $item->qty * ($item->final_price ?? $item->productVariant->price ?? $item->product->productVariants->first()->price ?? 0);
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