<?php

namespace App\Livewire;

use App\Models\CartItem;
use App\Services\CartService;
use App\Services\LocaleCurrencyService;
use Livewire\Component;

class Cart extends Component
{
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
                $promo = $item->productVariant
                    ? $promoService->calculateVariantPrice($item->productVariant, $item->qty, $user)
                    : ['final_price' => $item->productVariant->price ?? 0, 'promotion' => null];
                $item->final_price = $promo['final_price'];
                $item->promotion = $promo['promotion'];

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

        // 只传递必要的商品信息
        $selectedItems = $this->cartItems->whereIn('id', $this->selected)
            ->map(function ($item) {
                return [
                    'cart_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'qty' => $item->qty,
                ];
            })->toArray();

        session()->put('checkout_items', $selectedItems);

        return redirect()->route('checkout', ['locale' => $locale]);
    }

    public function render()
    {
        $lang = app(LocaleCurrencyService::class)->getLanguageByCode(session('lang'));

        return view('livewire.cart', [
            'cartItems' => $this->cartItems,
            'selected' => $this->selected,
            'total' => $this->total,
            'lang' => $lang,
        ]);
    }
}
