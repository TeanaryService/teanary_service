<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Cart as CartModel;
use App\Models\CartItem;
use App\Services\LocaleCurrencyService;

class Cart extends Component
{
    public $cartItems;
    public $selected = [];
    public $selectAll = true;
    public $total = 0;

    public function mount()
    {
        $cart = $this->getCart();
        $this->cartItems = $cart ? $cart->cartItems()->with(['product.productTranslations', 'productVariant.specificationValues.specificationValueTranslations', 'productVariant.media'])->get() : collect();
        $this->selected = $this->cartItems->pluck('id')->toArray();
        $this->calcTotal();
    }

    public function getCart()
    {
        if (auth()->check()) {
            return CartModel::firstOrCreate(['user_id' => auth()->id()]);
        } else {
            $sessionId = session()->getId();
            return CartModel::firstOrCreate(['session_id' => $sessionId]);
        }
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
            return $item->qty * ($item->productVariant->price ?? 0);
        });
    }

    public function checkout()
    {
        // 跳转到结账页面
        return redirect()->route('checkout');
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
